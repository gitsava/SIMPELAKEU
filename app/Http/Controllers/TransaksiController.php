<?php

namespace App\Http\Controllers;

use Log;
use Storage;
use Excel;
use Response;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\MimeType;
use App\Transaksi;
use App\Proyek;
use App\KategoriUmum;
use App\Simpanan;
use App\Http\Controllers\TransaksiBankController;
use App\Http\Controllers\TransaksiUmumController;
use App\Http\Controllers\TransaksiProyekController;
use App\Http\Controllers\TransaksiUnitController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\HistoriSaldoSimpananPerbulan as HistoriSimpanan;
use App\HistoriSaldoKategoriPerbulan as HistoriKategori;
use App\HistoriSaldoProyekPerbulan as HistoriProyek;
use App\HistoriSaldoUnitPerbulan as HistoriUnit;
use App\Http\Resources\Transaksi as TransaksiResource;

class TransaksiController extends Controller
{
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
        | Relations
        | pegawai           : pegawai()
        | transaksi umum    : transaksiUmum()
        | transaksi bank    : transaksiBank()
        | -----------------------------------------------------
		| Model             : Simpanan
        | Query search      : key

        kredit non-bank invovled bank : k(cash) -> k(Bank) -> d(cash) -> k(non-bank)
        debit non-bank invovled bank : d(cash) -> d(Bank) -> k(cash) -> d(non-bank)
        kredit non-bank : k(cash) -> k(non-bank)
        debit non-bank : d(cash) -> d(non-bank)
        debit bank : d(cash) -> k(bank)
        kredit bank : k(cash) -> d(bank)
    */
    public function reverseNominalType($nominalType){
        if($nominalType == 'd'){
            return 'k';
        }
        else return 'd';
    }

    public function isKredit($nominalType){
        if($nominalType == 'k'){
            return true;
        }
        else return false;
    }

    public function fetchEditedData(Request $request){
        return $this->editData($request);
    }

    public function editData(Request $request){
        try{
            $data = [
                'jenis_transaksi'=>$request->jenisTransaksi,
                'id_transaksi'=>$request->idTransaksi,
                'id_kat'=> null,
                'nama_kat'=>null,
                'id_bank'=> null,
                'nama_bank'=> null,
                'id_pegawai'=>null,
                'nama_pegawai'=> null,
                'tanggal'=>null,
                'tipe_nominal'=>null,
                'nominal'=>null,
                'keterangan'=>null
            ];
            $transaksi = Transaksi::where('id',$request->idTransaksi)->with(['pegawai'])
                        ->with(['transaksiUmum'=>
                            function($query){
                                $query->with(['kategori']);
                            }
                        ])
                        ->with(['transaksiProyek'=>
                            function($query){
                                $query->with(['proyek']);
                            }
                        ])->with(['transaksiUnit'=>
                            function($query){
                                $query->with(['Unit']);
                            }
                        ])->with(['transaksiBank'=>
                            function($query){
                                $query->with(['simpanan'])->where('id_bank','<>',1);
                            }
                        ])->first();
            if(isset($transaksi)){
                $data['id_pegawai']= $transaksi->id_pegawai;
                $data['nama_pegawai']= $transaksi->pegawai->nama;
                $data['tanggal']= $transaksi->tanggal;
                $data['nominal']= $transaksi->nominal;
                $data['keterangan']= $transaksi->keterangan;
                if(!$transaksi->transaksiUmum->isEmpty()){
                    $data['id_kat'] = $transaksi->transaksiUmum[0]->id_kategori;
                    $data['tipe_nominal']= $transaksi->transaksiUmum[0]->tipe_nominal;
                    $data['nama_kat']= $transaksi->transaksiUmum[0]->kategori->nama_kategori;
                }
                if(!$transaksi->transaksiProyek->isEmpty()){
                    $data['id_kat'] = $transaksi->transaksiProyek[0]->id_kegiatan;
                    $data['tipe_nominal']= $transaksi->transaksiProyek[0]->tipe_nominal;
                    $data['nama_kat']= $transaksi->transaksiProyek[0]->proyek->nama_kegiatan;
                }
                if(!$transaksi->transaksiUnit->isEmpty()){
                    $data['id_kat'] = $transaksi->transaksiUnit[0]->id_unit;
                    $data['tipe_nominal']= $transaksi->transaksiUnit[0]->tipe_nominal;
                    $data['nama_kat']= $transaksi->transaksiUnit[0]->Unit->nama;
                }
                if(!$transaksi->transaksiBank->isEmpty()){
                    if($request->jenisTransaksi != 2){
                        $data['id_bank'] = $transaksi->transaksiBank[0]->id_bank;
                        $data['tipe_nominal']= $transaksi->transaksiBank[0]->tipe_nominal;
                        $data['nama_bank']= $transaksi->transaksiBank[0]->simpanan->nama_bank;
                    }else{
                        $data['id_kat'] = $transaksi->transaksiBank[0]->id_bank;
                        $data['tipe_nominal']= $transaksi->transaksiBank[0]->tipe_nominal;
                        $data['nama_kat']= $transaksi->transaksiBank[0]->simpanan->nama_bank;
                    }
                }
                return ['data'=>$data];
            }else{
                return ['data'=>'empty'];
            }
            
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function submiteditTransaksi(Request $request){
        try{
            $idCash = 1;
            $isBankNew = false;
            $oldNominalType = '';
            $tanggal = date('Y-m-d',strtotime($request->tanggal));
            $transaksi = Transaksi::findOrFail($request->idTransaki)->with(['pegawai'])
                        ->with(['transaksiUmum'=> 
                            function($query){
                                $query->with(['kategori']);
                            }
                        ])->with(['transaksiProyek'=>
                            function($query){
                                $query->with(['proyek']);
                            }
                        ])->with(['transaksiUnit'=>
                            function($query){
                                $query->with(['Unit']);
                            }
                        ])->with(['transaksiBank'=>
                            function($query){
                                $query->with(['simpanan']);
                            }
                        ])->first();
            $transaksi->id_pegawai = $request->idPegawai['value'];
            $transaksi->keterangan = $request->keterangan;
            $newBulan = date('n',strtotime($request->tanggal));
            $newTahun = date('Y',strtotime($request->tanggal));
            $oldBulan = date('n',strtotime($transaksi->tanggal));
            $oldTahun = date('Y',strtotime($transaksi->tanggal));
            $bankControl = new TransaksiBankController;
            $isChanged = $oldTahun != $newTahun || $oldBulan != $newBulan || $transaksi->nominal != $request->nominal 
                        || $transaksi->tipe_nominal != $request->nominalType || $request->isKategoriChanged;
            if($isChanged){
                if(!$transaksi->transaksiProyek->isEmpty()){
                    $oldNominalType = $transaksi->transaksiProyek[0]->tipe_nominal;
                    $proyekControl = new TransaksiProyekController;
                    $proyekControl->deleteTransProyek($transaksi->transaksiProyek[0],$transaksi->nominal);
                }
                elseif(!$transaksi->transaksiUmum->isEmpty()){
                    $umumControl = new TransaksiUmumController;
                    $umumControl->deleteTransUmum($transaksi->transaksiUmum[0],$transaksi->nominal);
                }
                elseif(!$transaksi->transaksiUnit->isEmpty()){
                    $unitControl = new TransaksiUnitController;
                    $unitControl->deleteTransUnit($transaksi->transaksiUnit[0],$transaksi->nominal);
                }
                if(isset($request->idProyek)){
                    $proyekControl = new TransaksiProyekController;
                    // menyimpan kategori type sesuai input
                    $transProyekStatus = $proyekControl->storeTransProyek(
                                        $transaksi->id,$request->idProyek['value'],
                                        $request->nominalType,$request->nominal,
                                        $tanggal);
                }elseif(isset($request->idKategori)){
                    $umumControl = new TransaksiUmumController;
                    // menyimpan kategori type sesuai input
                    $transUmumStatus = $transUmumControl->storeTransUmum(
                                    $transaksi->id,$request->idKategori['value'],
                                    $request->nominalType,$request->nominal,
                                    $tanggal);
                }elseif(isset($request->idUnit)){
                    $unitControl = new TransaksiUnitController;
                    // menyimpan kategori type sesuai input
                    $transUnitStatus = $transUnitControl->storeTransUnit(
                                    $transaksi->id,$request->idUnit['value'],
                                    $request->nominalType,$request->nominal,
                                    $tanggal);
                }elseif(isset($request->idSimpanan)){
                    $isBankNew = true;
                    $transBankNonCash = $transaksi->transaksiBank->where('id_bank','<>',$idCash)->first();
                    $oldNominalType = $transBankNonCash->tipe_nominal;
                    $bankControl->deleteTransBank($transBankNonCash,$transaksi->nominal);
                    $transBankStatus = $transBankControl->storeTransBank(
                                    $transaksi->id,$request->idSimpanan['value'],
                                    $request->nominalType,
                                    $transaksi->nominal,$tanggal);
                }
                foreach($transaksi->transaksiBank as $transBankRow){
                    if($transBankRow->id_bank != $idCash){
                        if(!$isBankNew){
                            $oldNominalType = $transBankRow->tipe_nominal;
                            $transBankStatus = $transBankControl->storeTransBank(
                                    $transaksi->id,$request->idSimpanan['value'],
                                    $request->nominalType,
                                    $transaksi->nominal,$tanggal);
                        }
                    }else{
                        if($oldNominalType == $transBankRow->tipe_nominal){
                            $transBankStatus = $transBankControl->storeTransBank(
                                            $transaksi->id,$idCash,$request->nominalType
                                            ,$transaksi->nominal,$tanggal);
                        }else{
                            $transBankStatus = $transBankControl->storeTransBank(
                                            $transaksi->id,$idCash,
                                            $this->reverseNominalType($request->nominalType),$transaksi->nominal,
                                            $tanggal);
                        }
                    }
                    $bankControl->deleteTransBank($transBankRow,$transaksi->nominal);
                }
                $transaksi->tanggal = $tanggal;
                return $transaksi->save();
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function forceEditTransaksi(Request $request){
        try{
            Log::info('masuk route');
            $transaksi = Transaksi::find($request->idTransaksi);
            Log::info($transaksi);
            $newBulan = date('n',strtotime($request->tanggal));
            $newTahun = date('Y',strtotime($request->tanggal));
            $oldBulan = date('n',strtotime($transaksi->tanggal));
            $oldTahun = date('Y',strtotime($transaksi->tanggal));
            $isChanged = $oldTahun != $newTahun || $oldBulan != $newBulan || $transaksi->nominal != $request->nominal 
                        || $request->isNominalTypeChanged || $request->isKategoriChanged;
            if($isChanged){
                $this->deleteTransaksi($request);
                return ['status'=>$this->storeTransaksi($request)];
            }else{
                $transaksi->id_pegawai = $request->idPegawai['value'];
                $transaksi->keterangan = $request->keterangan;
                return ['status'=>$transaksi->save()];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    //tambah atau edit transaksi
    public function storeTransaksi(Request $request){
        try{
            $transaksi = new Transaksi;
            // store transaksi 
            $transaksi->id_pegawai = $request->idPegawai['value'];
            $transaksi->keterangan = $request->keterangan;
            $transaksi->tanggal = date('Y-m-d',strtotime($request->tanggal));
            $transaksi->nominal = $request->nominal;
            $transaksi->status = 1;
            $idCash = 1;
            if($transaksi->save()){
                $transBankControl = new TransaksiBankController;
                // jika ada yang di simpan ke bank
                if(isset($request->idSimpanan)){
                    // menyimpan transaksi bank non cash dengan type sesuai input
                    $transBankStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$request->idSimpanan['value'],
                                        $request->nominalType,
                                        $transaksi->nominal,$transaksi->tanggal);
                    // menyimpan transaksi bank cash dengan type reverse input
                    $transBankStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$idCash,
                                        $this->reverseNominalType($request->nominalType),$transaksi->nominal,
                                        $transaksi->tanggal);
                
                }
                // jika ada yang di simpan di kategori
                if(isset($request->idKategori)){
                    $transUmumControl = new TransaksiUmumController;
                    // menyimpan di cash type sesuai input
                    $transCashStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$idCash,$request->nominalType,
                                        $transaksi->nominal,$transaksi->tanggal);
                    // menyimpan kategori type sesuai input
                    $transUmumStatus = $transUmumControl->storeTransUmum(
                                            $transaksi->id,$request->idKategori['value'],
                                            $request->nominalType,$transaksi->nominal,
                                            $transaksi->tanggal);
                }
                // jika ada yang di simpan di proyek
                if(isset($request->idProyek)){
                    $transProyekControl = new TransaksiProyekController;
                    // menyimpan di cash type sesuai input
                    $transCashStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$idCash,$request->nominalType,
                                        $transaksi->nominal,$transaksi->tanggal);
                    // menyimpan kategori type sesuai input
                    $transProyekStatus = $transProyekControl->storeTransProyek(
                                            $transaksi->id,$request->idProyek['value'],
                                            $request->nominalType,$transaksi->nominal,
                                            $transaksi->tanggal);
                }
                // jika ada yang di simpan di proyek
                if(isset($request->idUnit)){
                    $transUnitControl = new TransaksiUnitController;
                    // menyimpan di cash type sesuai input
                    $transCashStatus = $transBankControl->storeTransBank(
                                        $transaksi->id,$idCash,$request->nominalType,
                                        $transaksi->nominal,$transaksi->tanggal);
                    // menyimpan kategori type sesuai input
                    $transUnitStatus = $transUnitControl->storeTransUnit(
                                            $transaksi->id,$request->idUnit['value'],
                                            $request->nominalType,$transaksi->nominal,
                                            $transaksi->tanggal);
                }
                return ['status'=>true];
            }
            else{
                return ['status'=>false];
            }
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    public function deleteTransaksi(Request $request){
        try{
            Log::info('delete route');
            $transaksi = Transaksi::find($request->idTransaksi);
            $transBankControl = new TransaksiBankController;
            if($transaksi->transaksiUmum->first() != null){
                $transUmumControl = new TransaksiUmumController;
                $transUmumControl->deleteTransUmum($transaksi->transaksiUmum->first(),$transaksi->nominal,$transaksi->tanggal);
            }
            if($transaksi->transaksiProyek->first() != null){
                $transProyekControl = new TransaksiProyekController;
                $transProyekControl->deleteTransProyek($transaksi->transaksiProyek->first(),$transaksi->nominal,$transaksi->tanggal);
            }
            if($transaksi->transaksiUnit->first() != null){
                $transUnitControl = new TransaksiUnitController;
                $transUnitControl->deleteTransUnit($transaksi->transaksiUnit->first(),$transaksi->nominal,$transaksi->tanggal);
            }
            foreach($transaksi->transaksiBank as $transBankRow){
                $transBankControl->deleteTransBank($transBankRow,$transaksi->nominal,$transaksi->tanggal);
            }
            $deleted = Transaksi::destroy($request->idTransaksi);
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function fetchRekapBulanan($bulan,$tahun){
        /* ambil semua id kegiatan yang ada di tahun request */
        $proyekList = HistoriProyek::select('id_kegiatan')->where('tahun',$tahun)->distinct()->get();
        $idProyekAll = $proyekList->map(function ($item, $key) { return $item->id_kegiatan;})->toArray();
        /* ambil semua id unit yang ada di tahun request */
        $unitList = HistoriUnit::select('id_unit')->where('tahun',$tahun)->distinct()->get();
        $idUnitAll = $unitList->map(function ($item, $key) { return $item->id_unit;})->toArray();
        /* ambil semua id bank yang ada di tahun request */
        $simpananList = HistoriSimpanan::select('id_bank')->where('tahun',$tahun)->distinct()->get();
        $idSimpananAll = $simpananList->map(function ($item, $key) { return $item->id_bank;})->toArray();
        /* ambil semua id kategori yang ada di tahun request */
        $kategoriList = HistoriKategori::select('id_kategori')->where('tahun',$tahun)->distinct()->get();
        $idKategoriAll = $kategoriList->map(function ($item, $key) { return $item->id_kategori;})->toArray();
        /* ambil semua histori yang ada di tahun dan bulan request */
        $historiSimpananBulan = HistoriSimpanan::with(['simpanan'])->where('bulan',$bulan)
                                ->where('tahun',$tahun)->get();
        /* ambil semua id simpanan yang ada di tahun dan bulan request return to array */
        $idSimpananBulan = $historiSimpananBulan->map(function ($item, $key) { return $item->id_bank;})->toArray();
        /* substract simpanan all dengan bulan */
        $idSimpananDiff = array_diff($idSimpananAll,$idSimpananBulan);
        $historiProyekBulan = HistoriProyek::with(['proyek'])->where('bulan',$bulan)
                              ->where('tahun',$tahun)->get();
        /* ambil semua id kegiatan yang ada di tahun dan bulan request return to array */
        $idProyekBulan = $historiProyekBulan->map(function ($item, $key) { return $item->id_kegiatan;})->toArray();
        /* substract proyek all dengan bulan */
        $idProyekDiff = array_diff($idProyekAll,$idProyekBulan);
        $historiUnitBulan = HistoriUnit::with(['Unit'])->where('bulan',$bulan)
                              ->where('tahun',$tahun)->get();
        /* ambil semua id kegiatan yang ada di tahun dan bulan request return to array */
        $idUnitBulan = $historiUnitBulan->map(function ($item, $key) { return $item->id_unit;})->toArray();
        /* substract proyek all dengan bulan */
        $idUnitDiff = array_diff($idUnitAll,$idUnitBulan);
        $historiKategoriBulan = HistoriKategori::with(['kategori'])->where('bulan',$bulan)
                              ->where('tahun',$tahun)->get();
        /* ambil semua id kategori yang ada di tahun dan bulan request return to array */
        $idKategoriBulan = $historiKategoriBulan->map(function ($item, $key) { return $item->id_kategori;})->toArray();
        /* substract kategori all dengan bulan */
        $idKategoriDiff = array_diff($idKategoriAll,$idKategoriBulan);
        $data = [];
        $dataSimpanan = [];
        $dataIndex = 0;
        $dataSimpananIndex = 0;
        foreach($historiKategoriBulan as $hist){
            $data[$dataIndex] = [
                'kegiatan'=> $hist->kategori->nama_kategori,
                'saldo'=> $hist->saldo
            ];
            $dataIndex++;
        }
        foreach($idKategoriDiff as $id){
            $historiPrev = HistoriKategori::with(['kategori'])->where('id_kategori',$id)
                           ->where(function($first) use ($tahun,$bulan)
                            {
                                $first->where(function($second) use ($tahun,$bulan){
                                    $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                })->orWhere('tahun','<',(int)$tahun);
                            })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

            if(!is_null($historiPrev)){
                $data[$dataIndex] = [
                    'kegiatan'=> $historiPrev->kategori->nama_kategori,
                    'saldo'=> $historiPrev->saldo
                ];  
            }else{
                $kategori = KategoriUmum::find($id);
                $data[$dataIndex] = [
                    'kegiatan'=> $kategori->nama_kategori,
                    'saldo'=> '-'
                ];  
            }
            $dataIndex++;
        }
        foreach($historiProyekBulan as $hist){
            $data[$dataIndex] = [
                'kegiatan'=> $hist->proyek->nama_kegiatan,
                'saldo'=> $hist->saldo
            ];
            $dataIndex++;
        }
        foreach($idProyekDiff as $id){
            $historiPrev = HistoriProyek::with(['proyek'])->where('id_kegiatan',$id)
                           ->where(function($first) use ($tahun,$bulan)
                            {
                                $first->where(function($second) use ($tahun,$bulan){
                                    $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                })->orWhere('tahun','<',(int)$tahun);
                            })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
            
            if(!is_null($historiPrev)){
                $data[$dataIndex] = [
                    'kegiatan'=> $historiPrev->proyek->nama_kegiatan,
                    'saldo'=> $historiPrev->saldo
                ];  
            }else{
                $proyek = Proyek::find($id);
                $data[$dataIndex] = [
                    'kegiatan'=> $proyek->nama_kegiatan,
                    'saldo'=> '-'
                ];  
            }
            $dataIndex++;
        }
        //
        foreach($historiUnitBulan as $hist){
            $data[$dataIndex] = [
                'kegiatan'=> $hist->Unit->nama,
                'saldo'=> $hist->saldo
            ];
            $dataIndex++;
        }
        foreach($idUnitDiff as $id){
            $historiPrev = HistoriUnit::with(['Unit'])->where('id_unit',$id)
                           ->where(function($first) use ($tahun,$bulan)
                            {
                                $first->where(function($second) use ($tahun,$bulan){
                                    $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                })->orWhere('tahun','<',(int)$tahun);
                            })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
            
            if(!is_null($historiPrev)){
                $data[$dataIndex] = [
                    'kegiatan'=> $historiPrev->Unit->nama,
                    'saldo'=> $historiPrev->saldo
                ];  
            }else{
                $unit = Unit::find($id);
                $data[$dataIndex] = [
                    'kegiatan'=> $unit->nama,
                    'saldo'=> '-'
                ];  
            }
            $dataIndex++;
        }
        //
        foreach($historiSimpananBulan as $hist){
            $dataSimpanan[$dataSimpananIndex] = [
                'kegiatan'=> $hist->simpanan->nama_bank,
                'saldo'=> $hist->saldo
            ];
            $dataSimpananIndex++;
        }
        foreach($idSimpananDiff as $id){
            $historiPrev = HistoriSimpanan::with(['simpanan'])->where('id_bank',$id)
                           ->where(function($first) use ($tahun,$bulan)
                            {
                                $first->where(function($second) use ($tahun,$bulan){
                                    $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                })->orWhere('tahun','<',(int)$tahun);
                            })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
            if(!is_null($historiPrev)){
                $dataSimpanan[$dataSimpananIndex] = [
                    'kegiatan'=> $historiPrev->simpanan->nama_bank,
                    'saldo'=> $historiPrev->saldo
                ];  
            }else{
                $simpanan = Simpanan::find($id);
                $dataSimpanan[$dataSimpananIndex] = [
                    'kegiatan'=> $simpanan->nama_bank,
                    'saldo'=> '-'
                ];  
            }
            $dataSimpananIndex++;
        }
        if(count($data)!=0){
            if(count($dataSimpanan)!=0){
                return ['data'=>$data,'dataSimpanan'=>$dataSimpanan,'emptyData'=>false,'emptySimpanan'=>false];
            }else{
                return ['data'=>$data,'dataSimpanan'=>$dataSimpanan,'emptyData'=>false,'emptySimpanan'=>true];
            }
        }else{
            if(count($dataSimpanan)!=0){
                return ['data'=>$data,'dataSimpanan'=>$dataSimpanan,'emptyData'=>true,'emptySimpanan'=>false];
            }else{
                return ['data'=>$data,'dataSimpanan'=>$dataSimpanan,'emptyData'=>true,'emptySimpanan'=>true];
            }
        }
        
    }

    public function fetchRekap($tahun){
        $rekapData = [];
        $rekapDataSimpanan = [];
        $months = [];
        $kegiatans = [];
        for($i=0;$i<12;$i++){
            $month = date('F',strtotime('1'.'-'.($i+1).'-'.$tahun));
            $months[$i] = $month;
            $data = $this->fetchRekapBulanan(($i+1),$tahun);
            foreach($data['data'] as $item){
                if(!array_key_exists($item['kegiatan'],$rekapData)){
                    $rekapData = array_merge($rekapData,array($item['kegiatan'] => array($item['saldo'])));
                }else{
                    $rekapData[$item['kegiatan']] = array_merge($rekapData[$item['kegiatan']],array($item['saldo']));
                }
            }
            foreach($data['dataSimpanan'] as $item){
                if(!array_key_exists($item['kegiatan'],$rekapDataSimpanan)){
                    $rekapDataSimpanan = array_merge($rekapDataSimpanan,array($item['kegiatan'] => array($item['saldo'])));
                }else{
                    array_push($rekapDataSimpanan[$item['kegiatan']],$item['saldo']);
                }
            }
        }
        return ['data'=>$rekapData,'dataSimpanan'=>$rekapDataSimpanan ,'months'=>$months];
    }

    public function cvtRekapToArray($rekap){
        $arrayData = [];
        $arraySimpanan = [];
        $arrayDataSize = sizeof($rekap['data']) + 1;
        $arraySimpananSize = sizeof($rekap['dataSimpanan']);
        $dataKeys = array_keys($rekap['data']);
        $simpananKeys = array_keys($rekap['dataSimpanan']);
        for($i = 0; $i <= $arrayDataSize; $i++){
            if($i == 0){
                $arrayData[$i] = array_merge(array('No','POS ANGGARAN/KEGIATAN'),$rekap['months']);
            }
            elseif($i == $arrayDataSize){
                $arrayData[$i] = [];
                for($j = 0; $j < 14; $j++){
                    if($j <= 1){
                        $arrayData[$i] = array_merge($arrayData[$i],array(''));
                    }else{
                        $arrayData[$i] = array_merge($arrayData[$i],array(
                                            '=SUM('.chr(65+($j)).'3:'.chr(65+($j)).(1 + $arrayDataSize).')'
                                         ));
                    }
                }
            }
            else{
                $arrayData[$i] = array_merge(array($dataKeys[$i-1]),$rekap['data'][$dataKeys[$i-1]]);
                $arrayData[$i] = array_merge(array($i),$arrayData[$i]);
            }
        }
        for($i = 0; $i <= $arraySimpananSize; $i++){
            if($i == $arraySimpananSize){
                $arraySimpanan[$i] = [];
                for($j = 0; $j < 14; $j++){
                    if($j <= 1){
                        $arraySimpanan[$i] = array_merge($arraySimpanan[$i],array(''));
                    }else{
                        $arraySimpanan[$i] = array_merge($arraySimpanan[$i],array(
                                            '=SUM('.chr(65+($j)).(5 + $arrayDataSize).
                                            ':'.chr(65+($j)).((4 + $arrayDataSize) + $arraySimpananSize).')'
                                         ));
                    }
                }
            }else{
                $arraySimpanan[$i] = array_merge(array($simpananKeys[$i]),$rekap['dataSimpanan'][$simpananKeys[$i]]);
                $arraySimpanan[$i] = array_merge(array(''),$arraySimpanan[$i]);
            }
        }
        return ['data'=>$arrayData,'dataSimpanan'=>$arraySimpanan];
    }

    public function rekapExcel($array,$filename,$sheetname,$title){
        $emptyArray = array(['','','','','','','','','','','','','','']);
        $fullArray = $array['data'];
        $fullArray = array_merge($fullArray,$emptyArray);
        $fullArray = array_merge($fullArray,$emptyArray);
        $fullArray = array_merge($fullArray,$array['dataSimpanan']);
        Log::info($fullArray);
        $endRow = sizeof($array['data'])+1;
        $startSimpananRow = $endRow + 2;
        $endSimpananRow = $startSimpananRow + sizeof($array['dataSimpanan']);
        $exportExcel = Excel::create($filename, function($excel) use ($fullArray,$sheetname,$title,$endRow,$startSimpananRow,$endSimpananRow){
                            $excel->sheet($sheetname,function($sheet) use ($fullArray,$title,$endRow,$startSimpananRow,$endSimpananRow){
                                $sheet->setFontFamily('Calibri');
                                $sheet->setFontSize(11);
                                /* judul */
                                $sheet->cell('A1', function($cell) use ($title) {
                                    $cell->setValue($title);
                                    $cell->setFontWeight('bold');
                                });
                                $sheet->mergeCells('A1:N1');
                                $sheet->cells('A1:N1',function($cells){
                                    $cells->setAlignment('center');
                                });
                                /* data cells */
                                /* header */
                                $sheet->cells('A2:N2',function($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                });
                                /* saldo */
                                $sheet->cells('C3:N'.$endRow,function($cells){
                                    $cells->setAlignment('right');
                                });
                                /* border data */
                                for($i = 2; $i <= $endRow; $i++){
                                    $sheet->cells('A'.$i.':N'.$i,function($cells){
                                        $cells->setBorder('thin','thin','thin','thin');
                                    });
                                }
                                for($i = 0; $i < 14; $i++){
                                    $sheet->cells(chr(65+$i).'2:'.chr(65+$i).$endRow,function($cells){
                                        $cells->setBorder('thin','thin','thin','thin');
                                    });
                                }
                                /* array data to cells */
                                $sheet->fromArray($fullArray,'','A2',false,false);
                                /* total cells */
                                $sheet->cell('A'.$endRow, function($cell) use ($title) {
                                    $cell->setValue('Total');
                                });
                                $sheet->mergeCells('A'.$endRow.':B'.$endRow);
                                $sheet->cells('A'.$endRow.':N'.$endRow,
                                    function($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                });
                                /* coloumns width */
                                $sheet->setWidth(array(
                                    'A'=> 2.71, 'B'=> 43.71,
                                    'C'=> 17.14, 'D'=> 17.14,
                                    'E'=> 17.14, 'F'=> 17.14,
                                    'G'=> 17.14, 'H'=> 17.14,
                                    'I'=> 17.14, 'J'=> 17.14,
                                    'K'=> 17.14, 'L'=> 17.14,
                                    'M'=> 17.14, 'N'=> 17.14,
                                ));
                                /* format cell */
                                for($i = 0; $i < 12; $i++){
                                    for($j = 3; $j<=($endRow+3); $j++){
                                        $sheet->getStyle(chr(67+$i).($j))->getNumberFormat()
                                          ->setFormatCode("_(#,##0.00_);_(\(#,##0.00\);_(\"-\"??_);_(@_)");
                                    }
                                }
                                /* kegiatan text wrapping */
                                for($i = 3; $i <= ($endSimpananRow-1); $i++){
                                    $sheet->getStyle('B'.$i)->getAlignment()->setWrapText(true);
                                }
                                /* Vertical Alignment */
                                $sheet->cells('A1:N'.$endSimpananRow,function($cells){
                                    $cells->setValignment('center');
                                });
                                /* end of data cells */
                                /* data Simpanan cells */
                                /* total cells */
                                $sheet->cell('B'.$endSimpananRow, function($cell) use ($title) {
                                    $cell->setValue('Total');
                                });
                                $sheet->cells('B'.$endSimpananRow.':N'.$endSimpananRow,
                                    function($cells){
                                        $cells->setBorder('medium', 'none', 'double', 'none');
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                });
                                /* format cell */
                                for($i = 0; $i < 12; $i++){
                                    for($j = $startSimpananRow; $j<=$endSimpananRow; $j++){
                                        $sheet->getStyle(chr(67+$i).($j))->getNumberFormat()
                                          ->setFormatCode("_(#,##0.00_);_(\(#,##0.00\);_(\"-\"??_);_(@_)");
                                    }
                                }
                                /* saldo */
                                $sheet->cells('C'.$startSimpananRow.':N'.$endSimpananRow,function($cells){
                                    $cells->setAlignment('right');
                                });
                                /* end of data Simpanan cells */
                            });    
                       })->store('xls');
    }

    public function generateRekap($tahun){
        $rekap = $this->fetchRekap($tahun);
        $data = $this->cvtRekapToArray($rekap);
        $filename = 'REKAP LAPORAN KEUANGAN JANUARI-DESEMBER '.$tahun;
        $sheetname = 'SALDO JAN-DES '.$tahun;
        $title = 'REKAPITULASI LAPORAN KEUANGAN';
        $this->rekapExcel($data,$filename,$sheetname,$title);
        $this->xlsToXlsx(storage_path('exports/'.$filename.'.xls'),$filename);
        return ['status'=>true];
    }

    public function downloadRekap($tahun){
        $filename = 'REKAP LAPORAN KEUANGAN JANUARI-DESEMBER '.$tahun;
        $fileContents = Storage::disk('export')->get($filename.'.xlsx');
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', MimeType::get('xlsx'));
        return $response;
    }

    //create file Excel
    public function createExcel($array,$filename,$sheetname,$title,$width){
        Log::info(sizeof($array));
        $exportExcel = Excel::create($filename, function($excel) use ($array,$sheetname,$title,$width){
                            $excel->sheet($sheetname,function($sheet) use ($array,$title,$width){
                                $sheet->setFontFamily('Calibri');
                                $sheet->setFontSize(11);
                                $sheet->cell('A1', function($cell) use ($title) {
                                    $cell->setValue($title);
                                    $cell->setFontWeight('bold');
                                });
                                $sheet->cells('C3:E'.(sizeof($array)+3),function($cells){
                                    $cells->setAlignment('right');
                                });
                                $sheet->cells('A3:E3',function($cells){
                                    $cells->setBorder('medium', 'none', 'double', 'none');
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                });
                                $sheet->mergeCells('A1:E1');
                                $sheet->cells('A1:E1',function($cells){
                                    $cells->setAlignment('center');
                                });
                                $sheet->fromArray($array,'','A3',false,false);
                                $sheet->setWidth(array(
                                    'A'=> 11.43, 
                                    'B'=> 64.14,
                                    'C'=> 17.14,
                                    'D'=> 17.14,
                                    'E'=> 17.14,
                                    'F'=> 15.43
                                ));
                                $sheet->cells('A1:F'.(sizeof($array)+3),function($cells){
                                    $cells->setValignment('center');
                                });
                                for($i=0; $i<sizeof($width); $i++){
                                    $sheet->setHeight(($i+3), ($width[$i]*15.75));
                                    $sheet->getStyle('B'.($i+3))->getAlignment()->setWrapText(true);
                                    $sheet->getStyle("A".($i+3))->getNumberFormat()->setFormatCode('d-mmm-yy');
                                    $sheet->getStyle("C".($i+3))->getNumberFormat()->setFormatCode("_(#,##0.00_);_(\(#,##0.00\);_(\"-\"??_);_(@_)");
                                    $sheet->getStyle("D".($i+3))->getNumberFormat()->setFormatCode("_(#,##0.00_);_(\(#,##0.00\);_(\"-\"??_);_(@_)");
                                    $sheet->getStyle("E".($i+3))->getNumberFormat()->setFormatCode("_(#,##0.00_);_(\(#,##0.00\);_(\"-\"??_);_(@_)");
                                }
                                $sheet->cells('A4:A'.(sizeof($array)+3),function($cells){
                                    $cells->setAlignment('right');
                                });
                            });
                       })->store('xls');
    }

    //convert xls to xlsx
    public function xlsToXlsx($path,$filename){
        $xls = IOFactory::load($path);
        $xlsx = IOFactory::createWriter($xls, "Xlsx");
        $newpath = storage_path('exports/'.$filename.'.xlsx');
        $xlsx->save($newpath);
    }

    //convert array with key to array
    public function cvtArray($array){
        $returnedArray = [];
        $keteranganWidth = [];
        for($i=0; $i<=sizeof($array); $i++){
            if($i == 0){
                $returnedArray[$i] = [
                    'Tanggal','Keterangan', 'Debit',
                     'Kredit', 'Saldo', ''
                    ];
                    $keteranganWidth[$i] = 1;
            }
            elseif($i == 1){
                $returnedArray[$i] = [
                    '', $array[$i-1]['keterangan'],
                    '','', $array[$i-1]['saldo'], ''
                ];
                $keteranganWidth[$i] = 1;
            }
            else{
                $keterangan = $array[$i-1]['pegawai'].'; '.$array[$i-1]['keterangan'];
                $returnedArray[$i] = [
                    date('j-M-y',strtotime($array[$i-1]['tanggal'])), $keterangan,
                    floatval((float)($array[$i-1]['nominal_type'] == 'd'? $array[$i-1]['nominal'] : 0)),
                    floatval((float)($array[$i-1]['nominal_type'] == 'k'? $array[$i-1]['nominal'] : 0)),
                    '=E'.($i+2).'+C'.($i+3).'-D'.($i+3), $array[$i-1]['kategori']
                ];
                $keteranganWidth[$i] = ceil(strlen($keterangan)/67);
            }
        }
        return ['array'=>$returnedArray,'width'=>$keteranganWidth];
    }

    //ambil semua transaksi tanpa cash
    public function getAllTransaksi(Request $request){
        try{
            $data = [];
            $idCash = 1;
            $kategori = [];
            $saldo = 0;
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'getAllTransaksi Route');
            // semua data transaksi selama setahun filter : $request->tahun
            $transaksi = Transaksi::with(['pegawai'])
                                    ->with(['transaksiUmum'=> 
                                        function($query){
                                            $query->with(['kategori']);
                                        }
                                    ])->with(['transaksiProyek'=>
                                        function($query){
                                            $query->with(['proyek']);
                                        }
                                    ])->with(['transaksiUnit'=>
                                        function($query){
                                            $query->with(['Unit']);
                                        }
                                    ])->with(['transaksiBank'=>
                                        function($query){
                                            $query->with(['simpanan']);
                                        }
                                    ])
                                    ->whereYear('tanggal',$request->tahun)
                                    ->where('status',1)->orderBy('tanggal','desc')->get();
            Log::info($transaksi);
            // semua histori saldo cash selama setahun perbulan filter : $request->tahun
            $historiSimpanan = HistoriSimpanan::where('id_bank',$idCash)
                                                ->where('tahun',(int)$request->tahun)
                                                ->get();
            //jika ada transaksi
            if(!$transaksi->isEmpty()){
                //if(!$transaksi->transaksiBank->isEmpty()){
                //returned index
                $dataIndex = 0;
                //daftar kategori yang ada di transaksi bank/umum/proyek/pengadaan
                $kategoriIndex = 0;
                $bulan = '';
                foreach($transaksi as $transRow){
                    $time  = strtotime($transRow->tanggal);
                    Storage::disk('local')->append($path, $bulan);
                    if($bulan != date('n',$time)){
                        $firstRowinBulan = true;
                        //split bulandari tanggal transaksi
                        $bulan = date('n',$time);
                        //ambil histori saldo pada $bulan dari tanggal transaksi
                        $historiSimpananBulan = $historiSimpanan->where('bulan',(int)$bulan)->first();
                        // disimpan di saldo
                        $saldo = $historiSimpananBulan->saldo;
                    }

                    //cari transaksi bank lain selain cash
                    $bankFiltered = $transRow->transaksiBank->where('id_bank','<>',$idCash)->first();
                    if(isset($bankFiltered)){
                        $kategori[$kategoriIndex] = [
                            'id'=>$bankFiltered->id,
                            'nama'=>$bankFiltered->simpanan->nama_bank,
                            'type'=>'',
                            'nominal'=> $transRow->nominal,
                            'jenis_transaksi'=> 2,
                            'delete_able'=>true
                        ];
                        $kategoriIndex++;
                    }
                    // cek ada selain bank atau tidak, kalau ada masukan ke $kategori
                    if(isset($transRow->transaksiUmum) && !$transRow->transaksiUmum->isEmpty()){
                        $kategori[$kategoriIndex] = [
                            'id'=>$transRow->transaksiUmum[0]->id,
                            'nama'=>$transRow->transaksiUmum[0]->kategori->nama_kategori,
                            'type'=>'',
                            'nominal'=> $transRow->nominal,
                            'jenis_transaksi'=> 1,
                            'delete_able'=>true
                        ];
                        if(count($transRow->transaksiBank) == 3){
                            $kategori[$kategoriIndex-1]['jenis_transaksi'] = $kategori[$kategoriIndex]['jenis_transaksi'];
                            $kategori[$kategoriIndex-1]['delete_able'] = false;
                        }
                        $kategoriIndex++;
                    }
                    if(isset($transRow->transaksiProyek) && !$transRow->transaksiProyek->isEmpty()){
                        $kategori[$kategoriIndex] = [
                            'id'=>$transRow->transaksiProyek[0]->id,
                            'nama'=>$transRow->transaksiProyek[0]->proyek->nama_kegiatan,
                            'type'=>'',
                            'nominal'=> $transRow->nominal,
                            'jenis_transaksi'=> 3,
                            'delete_able'=>true
                        ];
                        if(count($transRow->transaksiBank) == 3){
                            $kategori[$kategoriIndex-1]['jenis_transaksi'] = $kategori[$kategoriIndex]['jenis_transaksi'];
                            $kategori[$kategoriIndex-1]['delete_able'] = false;
                        }
                        $kategoriIndex++;
                    }
                    if(isset($transRow->transaksiUnit) && !$transRow->transaksiUnit->isEmpty()){
                        $kategori[$kategoriIndex] = [
                            'id'=>$transRow->transaksiUnit[0]->id,
                            'nama'=>$transRow->transaksiUnit[0]->Unit->nama,
                            'type'=>'',
                            'nominal'=> $transRow->nominal,
                            'jenis_transaksi'=> 4,
                            'delete_able'=>true
                        ];
                        if(count($transRow->transaksiBank) == 3){
                            $kategori[$kategoriIndex-1]['jenis_transaksi'] = $kategori[$kategoriIndex]['jenis_transaksi'];
                            $kategori[$kategoriIndex-1]['delete_able'] = false;
                        }
                        $kategoriIndex++;
                    }
                    //$kategoriIndex = 0;
                    Storage::disk('local')->append($path, json_encode($kategori));
                    //iterate transaksi bank yang cash
                    foreach($transRow->transaksiBank as $transBankRow){
                        if($transBankRow->id_bank == $idCash){
                            $kategori[$dataIndex]['type'] = $transBankRow->tipe_nominal;
                            if(!$firstRowinBulan){
                                //kalo kredit saldo ditambah, debit saldo dikurang
                                if($this->isKredit($kategori[$dataIndex-1]['type'])){
                                    $saldo += $kategori[$dataIndex-1]['nominal'];
                                }else{
                                    $saldo -= $kategori[$dataIndex-1]['nominal'];
                                }
                            }
                            $firstRowinBulan = false;
                            Storage::disk('local')->append($path, json_encode($kategori));
                            //Storage::disk('local')->append($path, $kategori[$dataIndex]['nominal']);
                            Storage::disk('local')->append($path, $saldo);
                            $data[$dataIndex] = [
                                'id_transaksi'=>$transRow->id,
                                'jenis_transaksi'=> $kategori[$dataIndex]['jenis_transaksi'],
                                'id'=> $kategori[$dataIndex]['id'],
                                'pegawai'=>$transRow->pegawai->nama,
                                'tanggal'=>$transRow->tanggal,
                                'keterangan'=>$transRow->keterangan,
                                'nominal_type'=>$kategori[$dataIndex]['type'],
                                'nominal'=>$transRow->nominal,
                                'saldo'=>$saldo,
                                'kategori'=> $kategori[$dataIndex]['nama'],
                                'edit_able'=> true,
                                'delete_able'=> $kategori[$dataIndex]['delete_able']
                            ];
                            $dataIndex++;
                        }
                    }
                }
                //saldo awal tahun
                if($this->isKredit($kategori[$dataIndex-1]['type'])){
                    $saldo += $kategori[$dataIndex-1]['nominal'];
                }else{
                    $saldo -= $kategori[$dataIndex-1]['nominal'];
                }
                $prevTahun = (int)$request->tahun - 1;
                $data[$dataIndex] = [
                    'id_transaksi'=>0,
                    'jenis_transaksi'=> 0,
                    'id'=>0,
                    'pegawai'=>'',
                    'tanggal'=>'',
                    'keterangan'=>'Saldo tanggal 31 Desember '.$prevTahun,
                    'nominal_type'=>'',
                    'nominal'=>'',
                    'saldo'=>$saldo,
                    'kategori'=> '',
                    'edit_able'=> false,
                    'delete_able'=> false
                ];
            }
            $data = array_reverse($data);
            return ['data'=>$data];
        }
        catch(Exception $err){
            Log::info($err);
        }
    }

    public function generateLaporan(Request $request){
        $data = $this->getAllTransaksi($request);
        $array = $this->cvtArray($data['data']);
        $filename = 'Laporan Keuangan '.$request->tahun;
        $sheetname = $request->tahun;
        $title = 'Laporan Keuangan Pusat Studi Biofarmaka Tropika LPPM IPB Tahun '.$request->tahun;
        $this->createExcel($array['array'],$filename,$sheetname,$title,$array['width']);
        $this->xlsToXlsx(storage_path('exports/'.$filename.'.xls'),$filename);
        return ['status'=>true];
    }

    public function downloadLaporan(Request $request){
        $filename = 'Laporan Keuangan '.$request->tahun;
        $fileContents = Storage::disk('export')->get($filename.'.xlsx');
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', MimeType::get('xlsx'));
        return $response;
    }
}
