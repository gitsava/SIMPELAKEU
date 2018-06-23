<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Support\Facades\Input;
use Storage;
use Illuminate\Http\Request;
use Response;
use Illuminate\Http\Testing\MimeType;
use App\Unit;
use App\Transaksi;
use App\TransaksiUnit;
use App\HistoriSaldoUnitPerbulan;
use App\Http\Controllers\TransaksiBankController;
use App\Http\Resources\UnitResource;

class TransaksiUnitController extends Controller
{
    //
    public function reverseNominalType($nominalType){
        if($nominalType == 'd'){
            return 'k';
        }
        else return 'd';
    }

    public function getAllUnitList(Request $request){
        try{
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'Get All Unit List Routes');
            if($request->has('key')){
                $key = $request->key;
                $Unit = Unit::where('nama','LIKE','%'.$key.'%')->orWhere('nama_panjang','LIKE','%'.$key.'%')->paginate(10)->appends(Input::except('page'));
            }else{
                $Unit = Unit::paginate(10);
            }
            Storage::disk('local')->append($path, json_encode($Unit));
            if(!$Unit->isEmpty()) return UnitResource::collection($Unit)->additional(['empty'=>false]); 
            else return ['empty'=>true];
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    public function storeTransUnit($idTransaksi,$idUnit,$nominalType,$nominal,$tanggal){
        try{
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'Store Transaksi Unit '.$idUnit);
            $time  = strtotime($tanggal);
            $bulan = date('n',$time);
            $tahun  = date('Y',$time);
            $transaksiUnit = new TransaksiUnit; 
            $Unit = Unit::findOrFail($idUnit);
            $historiUnit = HistoriSaldoUnitPerbulan::where('id_unit',$idUnit)->where('tahun',(int)$tahun)->where('bulan',(int)$bulan)->first();
            $nextHistoriUnit = HistoriSaldoUnitPerbulan::where('id_unit',$idUnit)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','>',(int)$bulan);
                                            })->orWhere('tahun','>',(int)$tahun);
                                        })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
            if($historiUnit == null){
                $historiUnit = new HistoriSaldoUnitPerbulan;
                $prevHistoriUnit = HistoriSaldoUnitPerbulan::where('id_unit',$idUnit)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                            })->orWhere('tahun','<',(int)$tahun);
                                        })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                if($prevHistoriUnit == null){
                    if($nextHistoriUnit->isEmpty()){
                        $historiUnit->saldo = $Unit->saldo;
                        Storage::disk('local')->append($path, 'next empty');
                    }
                    else{
                        $historiUnit->saldo = $nextHistoriUnit[0]->saldo;
                        Storage::disk('local')->append($path, 'historiUnit next tahun : '.$nextHistoriUnit[0]->tahun);
                        Storage::disk('local')->append($path, 'historiUnit next bulan : '.$nextHistoriUnit[0]->bulan);
                        $transaksi = Transaksi::with(['transaksiUnit'=>function($query) use ($idUnit){
                                        $query->where('id_unit',$idUnit);
                                     }])->whereYear('tanggal',$nextHistoriUnit[0]->tahun)
                                     ->whereMonth('tanggal',$nextHistoriUnit[0]->bulan)->get();
                        Storage::disk('local')->append($path, 'transaksi : '.json_encode($transaksi));
                        foreach($transaksi as $trans){
                            foreach($trans->transaksiUnit as $transUnit){
                                if($transUnit->tipe_nominal == 'k'){
                                    $historiUnit->saldo += $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiUnit Saldo k : '.$historiUnit->saldo);
                                }
                                else{
                                    $historiUnit->saldo -= $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiUnit Saldo d : '.$historiUnit->saldo);
                                }
                            }
                        }
                        Storage::disk('local')->append($path, 'historiUnit Saldo akhir : '.$historiUnit->saldo);
                    }
                }
                else{
                    $historiUnit->saldo = $prevHistoriUnit->saldo;
                }
                $historiUnit->id_unit = $idUnit;
                $historiUnit->bulan = (int)$bulan;
                $historiUnit->tahun = (int)$tahun;
            }
            $transaksiUnit->id_transaksi = $idTransaksi;
            $transaksiUnit->id_unit = $idUnit;
            $transaksiUnit->tipe_nominal = $nominalType;
            $transaksiUnit->status = 1;
            if($transaksiUnit->save()){
                if($nominalType == 'd'){
                    $Unit->saldo += $nominal;
                    $historiUnit->saldo += $nominal;
                    if(isset($nextHistoriUnit)){
                        foreach($nextHistoriUnit as $NHP){
                            $NHP->saldo += $nominal;
                            $NHP->save();
                        }
                    }
                }
                elseif($nominalType == 'k'){
                    $Unit->saldo -= $nominal;
                    $historiUnit->saldo -= $nominal;
                    if(isset($nextHistoriUnit)){
                        foreach($nextHistoriUnit as $NHP){
                            $NHP->saldo -= $nominal;
                            $NHP->save();
                        }
                    }
                }
                if($Unit->save()){
                    if($historiUnit->save()){
                        return true;
                    }
                    else{
                        return false;
                    }
                }
                else{
                    return false;
                }
            }
            else{
                return false;
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    /* Mengurangi saldo Unit, mengurangi saldo Unit perbulan, dan menghapus transaksiUnit */
    public function deleteTransUnit(TransaksiUnit $transUnit, $nominal,$tanggal){
        Log::info('delete trans Unit route');
        $time  = strtotime($tanggal);
        $bulan = date('n',$time);
        $tahun  = date('Y',$time);
        $Unit = Unit::find($transUnit->id_unit);
        $historiSaldoUnit = HistoriSaldoUnitPerbulan::where('id_unit',$transUnit->id_unit)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','>=',(int)$bulan);
                                            })->orWhere('tahun','>',(int)$tahun);
                                        })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
        foreach($historiSaldoUnit as $histProRow){
            if($transUnit->tipe_nominal == 'k'){
                $histProRow->saldo += $nominal;
            }else{
                $histProRow->saldo -= $nominal;
            }
            $histProRow->save();
        }
        if($transUnit->tipe_nominal == 'k'){
            $Unit->saldo += $nominal;
        }else{
            $Unit->saldo -= $nominal;
        }
        if($Unit->save()){
            $deleted = TransaksiUnit::destroy($transUnit->id);
        }
    }

    /* Memasukan pengajuan dana ke transakasi status == 1 */
    public function storePengajuanDana(Request $request){
        try{
            $transaksi = Transaksi::find($request->idTransaksi);
            $time  = strtotime($transaksi->tanggal);
            $bulan = date('n',$time);
            $tahun  = date('Y',$time);
            $idCash = 1;
            $transaksiUnit = TransaksiUnit::where('id_transaksi',$request->idTransaksi)->first();
            $Unit = Unit::findOrFail($transaksiUnit->id_unit);
            /* ambil semua histori saldo Unit perbulan pada tanggal $transaksi dan setelahnya */
            $historiUnit = HistoriSaldoUnitPerbulan::where('id_unit',$transaksiUnit->id_unit)
                             ->where(function($first) use ($tahun,$bulan){
                                $first->where(function($second) use ($tahun,$bulan){
                                    $second->where('tahun',(int)$tahun)->where('bulan','>=',(int)$bulan);
                                })->orWhere('tahun','>',(int)$tahun);
                             })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
            /* ganti status transaksi 1 */
            $transaksi->status = 1;
            $transaksi->keterangan = $request->keterangan;
            if($transaksi->save()){
                /* ganti tipe_nominal sesuai input dan status 1*/
                $transaksiUnit->tipe_nominal = $request->nominalType;
                $transaksiUnit->status = 1;
                $transaksiUnit->save();
                /* jumlahin saldo di bulan $transaksi pada Unit */
                if($transaksiUnit->tipe_nominal == 'k'){
                    $Unit->saldo += $transaksi->nominal;
                }else{
                    $Unit->saldo -= $transaksi->nominal;
                }
                $Unit->save();
                if($historiUnit->isEmpty()){
                    /* jumlahin saldo di bulan $transaksi pada histori Unit */
                    foreach($historiUnit as $hisRow){
                        $hisRow->id_unit = $Unit->id;
                        if($transaksiUnit->tipe_nominal == 'k'){
                            $hisRow->saldo += $transaksi->nominal;
                        }else{
                            $hisRow->saldo -= $transaksi->nominal;
                        }
                        $hisRow->save();
                    }
                }else{
                    /* buat record baru kalo ga ada histori */
                    $prevHistoriUnit = HistoriSaldoUnitPerbulan::where('id_unit',$transaksiUnit->id_unit)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                            })->orWhere('tahun','<',(int)$tahun);
                                        })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                    $historiUnit = new HistoriSaldoUnitPerbulan;
                    $historiUnit->id_unit = $Unit->id;
                    $historiUnit->tahun = $tahun;
                    $historiUnit->bulan = $bulan;
                    if($prevHistoriUnit == null){
                        /* tidak ada histori sama sekali */
                        $historiUnit->saldo = $Unit->saldo;
                    }else{
                        /* tidak ada histori sekarang dan setelahnya tapi ada histori sebelumnya */
                        $historiUnit->saldo = $prevHistoriUnit->saldo;
                        if($transaksiUnit->tipe_nominal == 'k'){
                            $historiUnit->saldo += $transaksi->nominal;
                        }else{
                            $historiUnit->saldo -= $transaksi->nominal;
                        }
                    }
                    $historiUnit->save();
                }   
                $transBankControl = new TransaksiBankController;
                if(isset($request->idSimpanan)){
                    /* kalo ngambil dari simpanan */
                    $transBankStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$request->idSimpanan['value'],
                                        $request->nominalType,
                                        $transaksi->nominal,$transaksi->tanggal);
                    $transBankStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$idCash,
                                        $this->reverseNominalType($request->nominalType),$transaksi->nominal,
                                        $transaksi->tanggal);
                }
                $transCashStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$idCash,$request->nominalType,
                                        $transaksi->nominal,$transaksi->tanggal);
                return ['status'=>true];
            }else{
                return ['status'=>false];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    // return semua pengajuan penggunaan dana Unit
    public function getAllPengajuanDanaUnit(Request $request){
        try{
            $data = [];
            $dataindex = 0;
            if($request->has('idUnit')){
                $transaksi = Transaksi::whereHas('transaksiUnit',function($query) use ($request){
                                            $query->where('id_unit',$request->idUnit);
                                        })->with(['transaksiUnit'=>function($query) use ($request){
                                            $query->with(['Unit'])->where('id_unit',$request->idUnit);
                                        }])->with(['pegawai'])->where('status',3)->orderBy('tanggal','desc')->get();
            }else{
                $transaksi = Transaksi::whereHas('transaksiUnit')->with(['transaksiUnit'=>function($query) use ($request){
                                            $query->with(['Unit']);
                                        }])->with(['pegawai'])->where('status',3)->orderBy('tanggal','desc')->get();
            }
            if(!$transaksi->isEmpty()){
                foreach($transaksi as $transRow){
                    $data[$dataindex] = [
                        'id'=> $transRow->id,
                        'tanggal'=> $transRow->tanggal,
                        'pegawai'=> $transRow->pegawai->nama,
                        'keterangan'=> $transRow->transaksiUnit[0]->keterangan.' '.$transRow->transaksiUnit[0]->jumlah
                                       .' '.$transRow->transaksiUnit[0]->unit.' x '.$transRow->transaksiUnit[0]->perkiraan_biaya,
                        'nominal'=> $transRow->nominal,
                        'kategori'=> $transRow->transaksiUnit[0]->Unit->nama,
                        'edit_able'=> true,
                        'delete_able'=> true,
                    ];
                    $dataindex++;
                }
                return ['data'=>$data,'empty'=>false];
            }else{
                return ['empty'=>true];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function getAllTransaksiUnit(Request $request){
        try{
            $data = [];
            $dataindex = 0;
            $bulan = '';
            $saldo = 0;
            $transaksi = Transaksi::whereHas('transaksiUnit',function($query) use ($request){
                                        $query->where('id_unit',$request->idUnit);
                                    })->with(['transaksiUnit'=>function($query) use ($request){
                                        $query->with(['Unit'])->where('id_unit',$request->idUnit);
                                    }])->with(['pegawai'])->whereYear('tanggal',$request->tahun)
                                    ->where('status',1)->orderBy('tanggal','desc')->get();
            $historiSimpanan = HistoriSaldoUnitPerbulan::where('id_unit',$request->idUnit)
                                ->where('tahun',(int)$request->tahun)
                                ->get();
            if(!$transaksi->isEmpty()){
                foreach($transaksi as $transUnitRow){
                    $time  = strtotime($transUnitRow->tanggal);
                    if($bulan != date('n',$time)){
                        $firstRowinBulan = true;
                        $bulan = date('n',$time);
                        //ambil histori saldo pada $bulan dari tanggal transaksi
                        $historiUnitBulan = $historiSimpanan->where('bulan',(int)$bulan)->first();
                        // disimpan di saldo
                        $saldo = $historiUnitBulan->saldo;
                    }
                    if(!$firstRowinBulan){
                        //kalo kredit saldo ditambah, debit saldo dikurang
                        if($transaksi[$dataindex-1]->transaksiUnit[0]->tipe_nominal == 'k'){
                            $saldo += $transaksi[$dataindex-1]->nominal;
                        }else{
                            $saldo -= $transaksi[$dataindex-1]->nominal;
                        }
                    }
                    $firstRowinBulan = false;
                    $data[$dataindex] = [
                        'id_transaksi'=>$transUnitRow->id,
                        'jenis_transaksi'=> 3,
                        'id' => $transUnitRow->transaksiUnit[0]->id,
                        'tanggal'=> $transUnitRow->tanggal,
                        'pegawai'=> $transUnitRow->pegawai->nama,
                        'keterangan'=> $transUnitRow->keterangan,
                        'nominal_type'=> $transUnitRow->transaksiUnit[0]->tipe_nominal,
                        'nominal'=> $transUnitRow->nominal,
                        'saldo'=> $saldo,
                        'kategori'=> $transUnitRow->transaksiUnit[0]->Unit->nama,
                        'edit_able'=> true,
                        'delete_able'=> true
                    ];
                    $dataindex++;
                }
                if($transaksi[$dataindex-1]->transaksiUnit[0]->tipe_nominal == 'k'){
                    $saldo += $transaksi[$dataindex-1]->nominal;
                }else{
                    $saldo -= $transaksi[$dataindex-1]->nominal;
                }
                $data[$dataindex] = [
                    'id'=>'',
                    'pegawai'=>'',
                    'tanggal'=>'',
                    'keterangan'=>'Saldo Awal ',
                    'nominal_type'=>'',
                    'nominal'=>'',
                    'saldo'=>$saldo,
                    'kategori'=> '',
                    'id_transaksi'=>0,
                    'jenis_transaksi'=> 0,
                    'edit_able'=> false,
                    'delete_able'=> false
                ];
            }
            $data = array_reverse($data);
            if(!$transaksi->isEmpty()) return ['data'=>$data,'empty'=>false]; 
            else return ['empty'=>true];
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    public function generateExcelUnit(Request $request){
        try{
            $data = $this->getAllTransaksiUnit($request);
            if($data['empty'] == false){
                $transaksiController = new TransaksiController;
                $array = $transaksiController->cvtArray($data['data']);
                $Unit = Unit::find($request->idUnit);
                $sheetname = $request->tahun;
                $filename = 'Laporan Keuangan Unit '.$Unit->nama.' '.$request->tahun;
                $title = 'Laporan Keuangan '.$Unit->nama;
                $transaksiController->createExcel($array['array'],$filename,$sheetname,$title,$array['width']);
                $transaksiController->xlsToXlsx(storage_path('exports/'.$filename.'.xls'),$filename);
                return ['status'=>true];
            }else{
                return ['error'=>'Tidak ada data yang tersedia'];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function downloadExcelUnit(Request $request){
        $Unit = Unit::find($request->idUnit);
        $sheetname = $request->tahun;
        $filename = 'Laporan Keuangan Unit '.$Unit->nama.' '.$request->tahun;
        $fileContents = Storage::disk('export')->get($filename.'.xlsx');
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', MimeType::get('xlsx'));
        return $response;
    }
}
