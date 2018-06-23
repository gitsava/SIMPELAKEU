<?php

namespace App\Http\Controllers;

use Log;
use Storage;
use Response;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Simpanan;
use App\TransaksiBank;
use App\Transaksi;
use App\Http\Controllers\TransaksiController;
use App\Http\Resources\Simpanan as SimpananResource;
use App\Http\Resources\TransaksiBank as TransaksiBankResource;
use App\HistoriSaldoSimpananPerbulan;

class TransaksiBankController extends Controller
{
    /*
    	| Database akun_bank Attribute Information
    	|------------------------------------------------------
		| id 			    : idSimpanan 
		| nama_bank		    : namaSimpanan
		| saldo	        	: saldo
		| status			: status (active = 1 deleted = 2)
		| -----------------------------------------------------
		| Model             : Simpanan
        | Query search      : key
    */

    //Ambil semua simpanan (return resource Simpanan) paginated
    public function getAllSimpananList(Request $request){
        try{
            if($request->has('key')){
                $key = $request->input('key');
                $simpanan = Simpanan::where('nama_bank','LIKE','%'.$key.'%')->where(['status'=>1])->paginate(10)->appends(Input::except('page'));
                
            }else{
                $simpanan = Simpanan::where(['status'=>1])->paginate(10);
            }
            if(!$simpanan->isEmpty()) return SimpananResource::collection($simpanan)->additional(['empty'=>false]); 
            else return ['empty'=>true];
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    //Ambil semua simpanan (return resource Simpanan) not paginated
    public function getAllSimpananListNonCash(Request $request){
        try{
            $idCash = 1;
            $simpanan = Simpanan::where(['status'=>1])->where('id','<>',$idCash)->get();
            if(!$simpanan->isEmpty()) return SimpananResource::collection($simpanan)->additional(['empty'=>false]); 
            else return ['empty'=>true];
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    //tambah atau edit simpanan
    public function storeSimpanan(Request $request){
        \DB::connection()->enableQueryLog();
        try{
            if($request->isMethod('patch')){
                $simpanan = Simpanan::findOrFail($request->idSimpanan);
            }
            else{
                $simpanan = new Simpanan;
                $simpanan->saldo = $request->saldo;
                $simpanan->status = 1;
            }
            $simpanan->nama_bank = $request->namaSimpanan;
            if($simpanan->save()){
                return new SimpananResource($simpanan);
            }
            else {
                //$queries = \DB::getQueryLog();
                return ['info'=>'gagal mengganti atau menambahkan simpanan'];
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    //Delete simpanan (ganti status jadi 2)
    public function deleteSimpanan(Request $request){
        try{
            $simpanan = Simpanan::findOrFail($request->idSimpanan);
            $simpanan->status = 2;
            if($simpanan->save()){
                return ['info' => 'Simpanan terhapus!'];
            }
            else {
                return ['info'=>'Gagal Menghapus'];
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

        /*
    	| Database transaksi_bank Information
    	|------------------------------------------------------
		| id 			    : idTransBank 
		| id_transaksi		: idTransaksi
		| id_bank        	: idBank
		| status			: status (active = 1 deleted = 2)
		| -----------------------------------------------------
        | transaksi         : transaksi()
        | simpanan          : simpanan()
        | -----------------------------------------------------
		| Model             : Simpanan
        | Query search      : key
    */

    /*
    	| Database transaksi Information
    	|------------------------------------------------------
		| id 			    : idTransaksi 
		| id_pegawai		: idPegawai
		| keterangan        : keterangan
        | nominal_type      : nominalType
        | nominal           : nominal
        | tanggal           : tanggal
        | saldo             : saldo
		| status			: status (active = 1 deleted = 2)
		| -----------------------------------------------------
        | transaksi         : transaksi()
        | simpanan          : simpanan()
        | -----------------------------------------------------
		| Model             : Simpanan
        | Query search      : key
    */

    //return semua transaksi bank
    public function getAllTransaksiBank(Request $request){
        try{
            $data = [];
            $dataindex = 0;
            $bulan = '';
            $saldo = 0;
            $jenisTransaksi = 2;
            $deleteAble = true;
            $transaksi = Transaksi::whereHas('transaksiBank',function($query) use ($request){
                                        $query->where('id_bank',$request->idBank);
                                    })->with(['transaksiBank'=>function($query) use ($request){
                                        $query->with(['simpanan'])->where('id_bank',$request->idBank);
                                    }])->with(['pegawai'])->whereYear('tanggal',$request->tahun)
                                    ->where('status',1)->orderBy('tanggal','desc')->get();
            $historiSimpanan = HistoriSaldoSimpananPerbulan::where('id_bank',$request->idBank)
                                ->where('tahun',(int)$request->tahun)
                                ->get();
            if(!$transaksi->isEmpty()){
                foreach($transaksi as $transBankRow){
                    $time  = strtotime($transBankRow->tanggal);
                    if($bulan != date('n',$time)){
                        $firstRowinBulan = true;
                        $bulan = date('n',$time);
                        //ambil histori saldo pada $bulan dari tanggal transaksi
                        $historiSimpananBulan = $historiSimpanan->where('bulan',(int)$bulan)->first();
                        // disimpan di saldo
                        $saldo = $historiSimpananBulan->saldo;
                    }
                    if(!$firstRowinBulan){
                        //kalo kredit saldo ditambah, debit saldo dikurang
                        if($transaksi[$dataindex-1]->transaksiBank[0]->tipe_nominal == 'k'){
                            $saldo += $transaksi[$dataindex-1]->nominal;
                        }else{
                            $saldo -= $transaksi[$dataindex-1]->nominal;
                        }
                    }
                    if($transBankRow->transaksiUmum->count() != 0){
                        $jenisTransaksi = 1;
                        $deleteAble = false;
                    }elseif($transBankRow->transaksiProyek->count() != 0){
                        $jenisTransaksi = 3;
                        $deleteAble = false;
                    }else{
                        $jenisTransaksi = 2;
                        $deleteAble = true;
                    }
                    $firstRowinBulan = false;
                    $data[$dataindex] = [
                        'id_transaksi'=>$transBankRow->id,
                        'jenis_transaksi'=> $jenisTransaksi,
                        'id' => $transBankRow->transaksiBank[0]->id,
                        'tanggal'=> $transBankRow->tanggal,
                        'pegawai'=> $transBankRow->pegawai->nama,
                        'keterangan'=> $transBankRow->keterangan,
                        'nominal_type'=> $transBankRow->transaksiBank[0]->tipe_nominal,
                        'nominal'=> $transBankRow->nominal,
                        'saldo'=> $saldo,
                        'kategori'=> $transBankRow->transaksiBank[0]->simpanan->nama_bank,
                        'edit_able'=> true,
                        'delete_able'=> $deleteAble
                    ];
                    $dataindex++;
                }
                if($transaksi[$dataindex-1]->transaksiBank[0]->tipe_nominal == 'k'){
                    $saldo += $transaksi[$dataindex-1]->nominal;
                }else{
                    $saldo -= $transaksi[$dataindex-1]->nominal;
                }
                $data[$dataindex] = [
                    'id_transaksi'=>0,
                    'jenis_transaksi'=> 0,
                    'id'=>'',
                    'pegawai'=>'',
                    'tanggal'=>'',
                    'keterangan'=>'Saldo Awal ',
                    'nominal_type'=>'',
                    'nominal'=>'',
                    'saldo'=>$saldo,
                    'kategori'=> '',
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

    public function generateExcelBank(Request $request){
        try{
            $path = 'logger.txt';
            $data = $this->getAllTransaksiBank($request);
            if($data['empty'] == false){
                $transaksiController = new TransaksiController;
                $array = $transaksiController->cvtArray($data['data']);
                Storage::disk('local')->append($path, json_encode($array));
                $simpanan = Simpanan::find($request->idBank);
                $sheetname = $simpanan->nama_bank;
                $filename = 'Laporan Keuangan Proyek '.$sheetname.' '.$request->tahun;
                $title = 'Rekening '.$sheetname;
                $transaksiController->createExcel($array['array'],$filename,$sheetname,$title,$array['width']);
                $transaksiController->xlsToXlsx(storage_path('exports/'.$filename.'.xls'),$filename);
                return ['status'=> true];
            }else{
                return ['error'=>'Tidak ada data yang tersedia'];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function downloadExcelBank(Request $request){
        $simpanan = Simpanan::find($request->idBank);
        $sheetname = $simpanan->nama_bank;
        $filename = 'Laporan Keuangan Proyek '.$sheetname.' '.$request->tahun;
        $fileContents = Storage::disk('export')->get($filename.'.xlsx');
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', MimeType::get('xlsx'));
        return $response;
    }

    public function editTransBank(TransaksiBank $transBank, $nominalType, $nominal, $tanggal, $idSimpanan){
        
    }

    /* Mengurangi saldo simpanan, mengurangi saldo simpanan perbulan, dan menghapus transaksiBank */
    public function deleteTransBank(TransaksiBank $transBank, $nominal, $tanggal){
        Log::info('delete trans bank route '.$transBank->id_bank);
        $time  = strtotime($tanggal);
        $bulan = date('n',$time);
        $tahun  = date('Y',$time);
        $simpanan = Simpanan::find($transBank->id_bank);
        $historiSaldoSimpanan = HistoriSaldoSimpananPerbulan::where('id_bank',$transBank->id_bank)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','>=',(int)$bulan);
                                            })->orWhere('tahun','>',(int)$tahun);
                                        })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
        foreach($historiSaldoSimpanan as $histSimRow){
            if($transBank->tipe_nominal == 'k'){
                $histSimRow->saldo += $nominal;
            }else{
                $histSimRow->saldo -= $nominal;
            }
            $histSimRow->save();
        }
        if($transBank->tipe_nominal == 'k'){
            $simpanan->saldo += $nominal;
        }else{
            $simpanan->saldo -= $nominal;
        }
        if($simpanan->save()){
            $deleted = TransaksiBank::destroy($transBank->id);
        }
    }

    //store ke transaksi_bank -> edit saldo akun_bank -> edit atau store histori_saldo_bank_perbulan
    public function storeTransBank($idTransaksi,$idBank,$nominalType,$nominal,$tanggal){
        try{
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'Store Transaksi Bank '.$idBank);
            $time  = strtotime($tanggal);
            $bulan = date('n',$time);
            $tahun  = date('Y',$time);
            $transaksiBank = new TransaksiBank; 
            $simpanan = Simpanan::findOrFail($idBank);
            $historiSimpanan = HistoriSaldoSimpananPerbulan::where('id_bank',$idBank)->where('tahun',(int)$tahun)->where('bulan',(int)$bulan)->first();
            $nextHistoriSimpanan = HistoriSaldoSimpananPerbulan::where('id_bank',$idBank)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','>',(int)$bulan);
                                            })->orWhere('tahun','>',(int)$tahun);
                                        })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
            if($historiSimpanan == null){
                $historiSimpanan = new HistoriSaldoSimpananPerbulan;
                $prevHistoriSimpanan = HistoriSaldoSimpananPerbulan::where('id_bank',$idBank)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                            })->orWhere('tahun','<',(int)$tahun);
                                        })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                if($prevHistoriSimpanan == null){
                    if($nextHistoriSimpanan->isEmpty()){
                        $historiSimpanan->saldo = $simpanan->saldo;
                        Storage::disk('local')->append($path, 'next empty');
                    }
                    else{
                        $historiSimpanan->saldo = $nextHistoriSimpanan[0]->saldo;
                        Storage::disk('local')->append($path, 'historiSimpanan next tahun : '.$nextHistoriSimpanan[0]->tahun);
                        Storage::disk('local')->append($path, 'historiSimpanan next bulan : '.$nextHistoriSimpanan[0]->bulan);
                        $transaksi = Transaksi::with(['transaksiBank'=>function($query) use ($idBank){
                                        $query->where('id_bank',$idBank);
                                     }])->whereYear('tanggal',$nextHistoriSimpanan[0]->tahun)
                                     ->whereMonth('tanggal',$nextHistoriSimpanan[0]->bulan)->get();
                        Storage::disk('local')->append($path, 'transaksi : '.json_encode($transaksi));
                        foreach($transaksi as $trans){
                            foreach($trans->transaksiBank as $transBank){
                                if($transBank->tipe_nominal == 'k'){
                                    $historiSimpanan->saldo += $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiSimpanan Saldo k : '.$historiSimpanan->saldo);
                                }
                                else{
                                    $historiSimpanan->saldo -= $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiSimpanan Saldo d : '.$historiSimpanan->saldo);
                                }
                            }
                        }
                        Storage::disk('local')->append($path, 'historiSimpanan Saldo akhir : '.$historiSimpanan->saldo);
                    }
                }
                else{
                    $historiSimpanan->saldo = $prevHistoriSimpanan->saldo;
                }
                $historiSimpanan->id_bank = $idBank;
                $historiSimpanan->bulan = (int)$bulan;
                $historiSimpanan->tahun = (int)$tahun;
            }
            $transaksiBank->id_transaksi = $idTransaksi;
            $transaksiBank->id_bank = $idBank;
            $transaksiBank->tipe_nominal = $nominalType;
            $transaksiBank->status = 1;
            if($transaksiBank->save()){
                if($nominalType == 'd'){
                    $simpanan->saldo += $nominal;
                    $historiSimpanan->saldo += $nominal;
                    if(isset($nextHistoriSimpanan)){
                        foreach($nextHistoriSimpanan as $NHS){
                            $NHS->saldo += $nominal;
                            $NHS->save();
                        }
                    }
                }
                elseif($nominalType == 'k'){
                    $simpanan->saldo -= $nominal;
                    $historiSimpanan->saldo -= $nominal;
                    if(isset($nextHistoriSimpanan)){
                        foreach($nextHistoriSimpanan as $NHS){
                            $NHS->saldo -= $nominal;
                            $NHS->save();
                        }
                    }
                }
                if($simpanan->save()){
                    if($historiSimpanan->save()){
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


    //delete transaksi bank
    public function deleteTransaksiBank(Request $request){
        try{
            $transaksiBank = Simpanan::findOrFail($request->idSimpanan);
            $transaksiBank->status = 2;
            if($transaksiBank->save()){
                return ['info' => 'Simpanan terhapus!'];
            }
            else {
                return ['info'=>'Gagal Menghapus'];
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }
}
