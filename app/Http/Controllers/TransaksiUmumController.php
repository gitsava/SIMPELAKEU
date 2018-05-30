<?php

namespace App\Http\Controllers;

use Storage;
use Illuminate\Http\Request;
use Response;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Support\Facades\Input;
use App\KategoriUmum;
use App\TransaksiUmum;
use App\Transaksi;
use App\HistoriSaldoKategoriPerbulan;
use App\Http\Resources\KategoriUmum as KategoriResource;


class TransaksiUmumController extends Controller
{
    /*
    	| Database kategori_transaksi_umum Attribute Information
    	|------------------------------------------------------
		| id 			    : idKategori 
		| nama_kategori		: namaKategori
		| saldo	        	: saldo
		| status			: status (active = 1 deleted = 2)
		| -----------------------------------------------------
		| Model             : KategoriUmum
        | Query search      : key
    */
    //ambil semua kategori umum
    public function getAllKategoriList(Request $request){
        try{
            if($request->has('key')){
                $key = $request->input('key');
                $kategori = KategoriUmum::where('nama_kategori','LIKE','%'.$key.'%')->where(['status'=>1])->paginate(10)->appends(Input::except('page'));
                
            }else{
                $kategori = KategoriUmum::where(['status'=>1])->paginate(10);
            }
            return KategoriResource::collection($kategori);        
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    //tambah atau edit kategori umum
    public function storeKategori(Request $request){
        \DB::connection()->enableQueryLog();
        try{
            if($request->isMethod('patch')){
                $kategori = KategoriUmum::findOrFail($request->idKategori);
            }
            else{
                $kategori = new KategoriUmum;
                $kategori->saldo = $request->saldo;
                $kategori->status = 1;
            }
            $kategori->nama_kategori = $request->namaKategori;
            if($kategori->save()){
                return new KategoriResource($kategori);
            }
            else {
                //$queries = \DB::getQueryLog();
                return ['info'=>'gagal mengganti atau menambahkan kategori'];
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    //Delete kategori umum (ganti status jadi 2)
    public function deleteKategori(Request $request){
        try{
            $kategori = KategoriUmum::findOrFail($request->idKategori);
            $kategori->status = 2;
            if($kategori->save()){
                return ['info' => 'Kategori terhapus!'];
            }
            else {
                return ['info'=>'Gagal Menghapus'];
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    public function getAllTransaksiUmum(Request $request){
        try{
            $data = [];
            $dataindex = 0;
            $bulan = '';
            $saldo = 0;
            $transaksi = Transaksi::whereHas('transaksiUmum',function($query) use ($request){
                                        $query->where('id_kategori',$request->idKategori);
                                    })->with(['transaksiUmum'=>function($query) use ($request){
                                        $query->with(['kategori'])->where('id_kategori',$request->idKategori);
                                    }])->with(['pegawai'])->whereYear('tanggal',$request->tahun)
                                    ->where('status',1)->orderBy('tanggal','desc')->get();
                   
            $historiSimpanan = HistoriSaldoKategoriPerbulan::where('id_kategori',$request->idKategori)
                                ->where('tahun',(int)$request->tahun)
                                ->get();
            if(!$transaksi->isEmpty()){
                foreach($transaksi as $transUmumRow){
                    $time  = strtotime($transUmumRow->tanggal);
                    if($bulan != date('n',$time)){
                        $firstRowinBulan = true;
                        $bulan = date('n',$time);
                        //ambil histori saldo pada $bulan dari tanggal transaksi
                        $historiKategoriBulan = $historiSimpanan->where('bulan',(int)$bulan)->first();
                        // disimpan di saldo
                        $saldo = $historiKategoriBulan->saldo;
                    }
                    if(!$firstRowinBulan){
                        //kalo kredit saldo ditambah, debit saldo dikurang
                        if($transaksi[$dataindex-1]->transaksiUmum[0]->tipe_nominal == 'k'){
                            $saldo += $transaksi[$dataindex-1]->nominal;
                        }else{
                            $saldo -= $transaksi[$dataindex-1]->nominal;
                        }
                    }
                    $firstRowinBulan = false;
                    $data[$dataindex] = [
                        'id_transaksi'=>$transUmumRow->id,
                        'jenis_transaksi'=> 1,
                        'id' => $transUmumRow->transaksiUmum[0]->id,
                        'tanggal'=> $transUmumRow->tanggal,
                        'pegawai'=> $transUmumRow->pegawai->nama,
                        'keterangan'=> $transUmumRow->keterangan,
                        'nominal_type'=> $transUmumRow->transaksiUmum[0]->tipe_nominal,
                        'nominal'=> $transUmumRow->nominal,
                        'saldo'=> $saldo,
                        'kategori'=> $transUmumRow->transaksiUmum[0]->kategori->nama_kategori,
                        'edit_able'=> true,
                        'delete_able'=> true
                    ];
                    $dataindex++;
                }
                if($transaksi[$dataindex-1]->transaksiUmum[0]->tipe_nominal == 'k'){
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
            Storage::disk('local')->append('logger.txt', json_encode($data));       
            if(!$transaksi->isEmpty()) return ['data'=>$data,'empty'=>false]; 
            else return ['empty'=>true];
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    public function downloadExcelUmum(Request $request){
        try{
            $data = $this->getAllTransaksiUmum($request);
            if($data['empty'] == false){
                $transaksiController = new TransaksiController;
                $array = $transaksiController->cvtArray($data['data']);
                Storage::disk('local')->append('logger.txt', json_encode($array['array']));
                $kategori = KategoriUmum::find($request->idKategori);
                $sheetname = $kategori->nama_kategori;
                $filename = 'Laporan Keuangan Proyek '.$sheetname.' '.$request->tahun;
                $title = 'Laporan Keuangan '.$sheetname;
                $transaksiController->createExcel($array['array'],$filename,$sheetname,$title,$array['width']);
                $transaksiController->xlsToXlsx(storage_path('exports/'.$filename.'.xls'),$filename);
                $fileContents = Storage::disk('export')->get($filename.'.xlsx');
                $response = Response::make($fileContents, 200);
                $response->header('Content-Type', MimeType::get('xlsx'));
                return $response;
            }else{
                return ['error'=>'Tidak ada data yang tersedia'];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    /* Mengurangi saldo umum, mengurangi saldo umum perbulan, dan menghapus transaksiUmum */
    public function deleteTransUmum(TransaksiUmum $transUmum, $nominal, $tanggal){
        $kategori = KategoriUmum::find($transUmum->id_kategori);
        $time  = strtotime($tanggal);
        $bulan = date('n',$time);
        $tahun  = date('Y',$time);
        $historiSaldoKategori = HistoriSaldoKategoriPerbulan::where('id_kategori',$transUmum->id_kategori)
                                    ->where(function($first) use ($tahun,$bulan){
                                        $first->where(function($second) use ($tahun,$bulan){
                                            $second->where('tahun',(int)$tahun)->where('bulan','>=',(int)$bulan);
                                        })->orWhere('tahun','>',(int)$tahun);
                                    })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
        foreach($historiSaldoKategori as $histKatRow){
            if($transUmum->tipe_nominal == 'k'){
                $histKatRow->saldo += $nominal;
            }else{
                $histKatRow->saldo -= $nominal;
            }
            $histKatRow->save();
        }
        if($transUmum->tipe_nominal == 'k'){
            $kategori->saldo += $nominal;
        }else{
            $kategori->saldo -= $nominal;
        }
        if($kategori->save()){
            $deleted = TransaksiUmum::destroy($transUmum->id);
        }
    }

    public function storeTransUmum($idTransaksi,$idKategori,$nominalType,$nominal,$tanggal){
        try{
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'Store Transaksi Umum '.$idKategori);
            $time  = strtotime($tanggal);
            $bulan = date('n',$time);
            $tahun  = date('Y',$time);
            $transaksiUmum = new TransaksiUmum;
            $kategori = KategoriUmum::findOrFail($idKategori);
            $historiKategori = HistoriSaldoKategoriPerbulan::where('id_kategori',$idKategori)
                               ->where('tahun',(int)$tahun)->where('bulan',(int)$bulan)->first();
            $nextHistoriKategori = HistoriSaldoKategoriPerbulan::where('id_kategori',$idKategori)
                                    ->where(function($first) use ($tahun,$bulan){
                                        $first->where(function($second) use ($tahun,$bulan){
                                            $second->where('tahun',(int)$tahun)->where('bulan','>',(int)$bulan);
                                        })->orWhere('tahun','>',(int)$tahun);
                                    })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
            if($historiKategori == null){
                $historiKategori = new HistoriSaldoKategoriPerbulan;
                $prevHistoriKategori = HistoriSaldoKategoriPerbulan::where('id_kategori',$idKategori)
                                        ->where(function($first) use ($tahun,$bulan){
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                            })->orWhere('tahun','<',(int)$tahun);
                                        })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                if($prevHistoriKategori == null){
                    if($nextHistoriKategori->isEmpty()){
                        $historiKategori->saldo = $kategori->saldo;
                        Storage::disk('local')->append($path, 'next empty');
                    }
                    else{
                        $historiKategori->saldo = $nextHistoriKategori[0]->saldo;
                        Storage::disk('local')->append($path, 'historiKategori next tahun : '.$nextHistoriKategori[0]->tahun);
                        Storage::disk('local')->append($path, 'historiKategori next bulan : '.$nextHistoriKategori[0]->bulan);
                        $transaksi = Transaksi::with(['transaksiUmum'=>function($query) use ($idKategori){
                                        $query->where('id_kategori',$idKategori);
                                     }])->whereYear('tanggal',$nextHistoriKategori[0]->tahun)
                                     ->whereMonth('tanggal',$nextHistoriKategori[0]->bulan)->get();
                        Storage::disk('local')->append($path, 'transaksi : '.json_encode($transaksi));
                        foreach($transaksi as $trans){
                            foreach($trans->transaksiUmum as $transUmum){
                                if($transUmum->tipe_nominal == 'k'){
                                    $historiKategori->saldo += $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiKategori Saldo k : '.$historiKategori->saldo);
                                }
                                else{
                                    $historiKategori->saldo -= $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiKategori Saldo d : '.$historiKategori->saldo);
                                }
                            }
                        }
                        Storage::disk('local')->append($path, 'historiKategori Saldo akhir : '.$historiKategori->saldo);
                    }
                }
                else{
                    $historiKategori->saldo = $prevHistoriKategori->saldo;
                }
                $historiKategori->id_kategori = $idKategori;
                $historiKategori->bulan = (int)$bulan;
                $historiKategori->tahun = (int)$tahun;
            }
            $transaksiUmum->id_transaksi = $idTransaksi;
            $transaksiUmum->id_kategori = $idKategori;
            $transaksiUmum->tipe_nominal = $nominalType;
            $transaksiUmum->status = 1;
            if($transaksiUmum->save()){
                if($nominalType == 'd'){
                    $kategori->saldo += $nominal;
                    $historiKategori->saldo += $nominal;
                    if(isset($nextHistoriKategori)){
                        foreach($nextHistoriKategori as $NHK){
                            $NHK->saldo += $nominal;
                            $NHK->save();
                        }
                    }
                }
                elseif($nominalType == 'k'){
                    $kategori->saldo -= $nominal;
                    $historiKategori->saldo -= $nominal;
                    if(isset($nextHistoriKategori)){
                        foreach($nextHistoriKategori as $NHK){
                            $NHK->saldo -= $nominal;
                            $NHK->save();
                        }
                    }
                }
                if($kategori->save()){
                    if($historiKategori->save()){
                        $info = ['status'=>true,'info'=>'all saved']; 
                        return $info;
                    }
                    else{
                        $info = ['status'=>false,'info'=>'histori kategori failed']; 
                        return $info;
                    }
                }
                else{
                    $info = ['status'=>true,'info'=>'kategori failed']; 
                    return $info;
                }
            }
            else{
                $info = ['status'=>false,'info'=>'transaksi umum failed']; 
                return $info;
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }    
}
