<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Support\Facades\Input;
use Storage;
use Illuminate\Http\Request;
use Response;
use Illuminate\Http\Testing\MimeType;
use App\Proyek;
use App\Transaksi;
use App\TransaksiProyek;
use App\PenelitiPSB;
use App\PesertaKegiatan;
use App\HistoriSaldoProyekPerbulan;
use App\Http\Controllers\TransaksiBankController;
use App\Http\Controllers\GeneratePdfController;
use App\Http\Resources\Proyek as ProyekResource;

class TransaksiProyekController extends Controller
{
    //
    public function getAllProyekList(Request $request){
        try{
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'Get All Proyek List Routes');
			$options = $request->has('options') ? $request->options : false;
			$idPeneliti = $request->has('idPeneliti') ? $request->idPeneliti : 0;
			$tahun = $request->has('tahun') ? $request->tahun : 0;
			if($options){
				$proyek = [];
				$index = 0;
				$proyekList = PesertaKegiatan::with(['kegiatan'=> function($q) use ($tahun){
								  $q->whereIn('id_tipe_kegiatan',[1,2])
								    ->when($tahun,function($q1,$tahun){
									    return $q1->whereYear('tanggal_akhir','>=',$tahun);
								  });
							  }])->whereHas('kegiatan',function($q) use ($tahun){
									$q->whereIn('id_tipe_kegiatan',[1,2])
									  ->when($tahun,function($q1,$tahun){
										  return $q1->whereYear('tanggal_akhir','<=',$tahun)
												 ->whereYear('tanggal_awal','>=',$tahun);
									  });
							  })->where('id_peran',1)->when($idPeneliti,function($q,$idPeneliti){
									return $q->where('id_peneliti',$idPeneliti);
							  })->get();
				foreach($proyekList as $pl){
					$proyek[$index] = [
										'nama_proyek' => $pl->kegiatan->nama_kegiatan,
										'id' => $pl->kegiatan->id
									  ];
					$index++;
				}
				Log::info($proyek);
				if(!empty($proyek)) return ['data'=>$proyek,'empty'=>false]; 
				else return ['data'=>[],'empty'=>true];
			}else{
				$proyek = PesertaKegiatan::with(['kegiatan'=> function($q) use ($tahun){
								$q->whereIn('id_tipe_kegiatan',[1,2])
								  ->when($tahun,function($q1,$tahun){
									  return $q1->whereYear('tanggal_akhir','>=',$tahun);
								  });
						  }])->with(['peneliti'=> function($q){
								$q->with(['penelitipsb'=> function($q1){
									$q1->with(['pegawai'])->first();
								}])->first();
						  }])->whereHas('kegiatan',function($q) use ($tahun){
								$q->whereIn('id_tipe_kegiatan',[1,2])
								  ->when($tahun,function($q1,$tahun){
									  return $q1->whereYear('tanggal_akhir','<=',$tahun)
											 ->whereYear('tanggal_awal','>=',$tahun);
								  });
						  })->where('id_peran',1)->when($idPeneliti,function($q,$idPeneliti){
								return $q->where('id_peneliti',$idPeneliti);
						  })->paginate(10)
						  ->appends(Input::except('page'));
				if(!$proyek->isEmpty()) return ['data'=>$proyek,'empty'=>false]; 
				else return ['data'=>[],'empty'=>true];		  
			}
            Storage::disk('local')->append($path, json_encode($proyek));
        }
        catch(Exception $err){
            Log::info($err);
        }
    }
	
	public function getAllPeneliti(Request $request){
		$peserta = PenelitiPSB::with(['pegawai'])->get();
		return ['data'=>$peserta];
	}

    public function storeTransProyek($idTransaksi,$idProyek,$nominalType,$nominal,$tanggal){
        try{
            $path = 'logger.txt';
            Storage::disk('local')->append($path, 'Store Transaksi Proyek '.$idProyek);
            $time  = strtotime($tanggal);
            $bulan = date('n',$time);
            $tahun  = date('Y',$time);
            $transaksiProyek = new TransaksiProyek; 
            $proyek = Proyek::findOrFail($idProyek);
            $historiProyek = HistoriSaldoProyekPerbulan::where('id_kegiatan',$idProyek)->where('tahun',(int)$tahun)->where('bulan',(int)$bulan)->first();
            $nextHistoriProyek = HistoriSaldoProyekPerbulan::where('id_kegiatan',$idProyek)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','>',(int)$bulan);
                                            })->orWhere('tahun','>',(int)$tahun);
                                        })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
            if($historiProyek == null){
                $historiProyek = new HistoriSaldoProyekPerbulan;
                $prevHistoriProyek = HistoriSaldoProyekPerbulan::where('id_kegiatan',$idProyek)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                            })->orWhere('tahun','<',(int)$tahun);
                                        })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                if($prevHistoriProyek == null){
                    if($nextHistoriProyek->isEmpty()){
                        $historiProyek->saldo = $proyek->saldo;
                        Storage::disk('local')->append($path, 'next empty');
                    }
                    else{
                        $historiProyek->saldo = $nextHistoriProyek[0]->saldo;
                        Storage::disk('local')->append($path, 'historiProyek next tahun : '.$nextHistoriProyek[0]->tahun);
                        Storage::disk('local')->append($path, 'historiProyek next bulan : '.$nextHistoriProyek[0]->bulan);
                        $transaksi = Transaksi::with(['transaksiProyek'=>function($query) use ($idProyek){
                                        $query->where('id_kegiatan',$idProyek);
                                     }])->whereYear('tanggal',$nextHistoriProyek[0]->tahun)
                                     ->whereMonth('tanggal',$nextHistoriProyek[0]->bulan)->get();
                        Storage::disk('local')->append($path, 'transaksi : '.json_encode($transaksi));
                        foreach($transaksi as $trans){
                            foreach($trans->transaksiProyek as $transProyek){
                                if($transProyek->tipe_nominal == 'k'){
                                    $historiProyek->saldo += $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiProyek Saldo k : '.$historiProyek->saldo);
                                }
                                else{
                                    $historiProyek->saldo -= $trans->nominal;
                                    Storage::disk('local')->append($path, 'historiProyek Saldo d : '.$historiProyek->saldo);
                                }
                            }
                        }
                        Storage::disk('local')->append($path, 'historiProyek Saldo akhir : '.$historiProyek->saldo);
                    }
                }
                else{
                    $historiProyek->saldo = $prevHistoriProyek->saldo;
                }
                $historiProyek->id_kegiatan = $idProyek;
                $historiProyek->bulan = (int)$bulan;
                $historiProyek->tahun = (int)$tahun;
            }
            $transaksiProyek->id_transaksi = $idTransaksi;
            $transaksiProyek->id_kegiatan = $idProyek;
            $transaksiProyek->tipe_nominal = $nominalType;
            $transaksiProyek->status = 1;
            if($transaksiProyek->save()){
                if($nominalType == 'd'){
                    $proyek->saldo += $nominal;
                    $historiProyek->saldo += $nominal;
                    if(isset($nextHistoriProyek)){
                        foreach($nextHistoriProyek as $NHP){
                            $NHP->saldo += $nominal;
                            $NHP->save();
                        }
                    }
                }
                elseif($nominalType == 'k'){
                    $proyek->saldo -= $nominal;
                    $historiProyek->saldo -= $nominal;
                    if(isset($nextHistoriProyek)){
                        foreach($nextHistoriProyek as $NHP){
                            $NHP->saldo -= $nominal;
                            $NHP->save();
                        }
                    }
                }
                if($proyek->save()){
                    if($historiProyek->save()){
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

    public function editTransProyek(TransaksiProyek $transProyek, $nominal){
        
    }

    /* Mengurangi saldo proyek, mengurangi saldo proyek perbulan, dan menghapus transaksiProyek */
    public function deleteTransProyek(TransaksiProyek $transProyek, $nominal,$tanggal){
        Log::info('delete trans proyek route');
        $time  = strtotime($tanggal);
        $bulan = date('n',$time);
        $tahun  = date('Y',$time);
        $proyek = Proyek::find($transProyek->id_kegiatan);
        $historiSaldoProyek = HistoriSaldoProyekPerbulan::where('id_kegiatan',$transProyek->id_kegiatan)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','>=',(int)$bulan);
                                            })->orWhere('tahun','>',(int)$tahun);
                                        })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
        foreach($historiSaldoProyek as $histProRow){
            if($transProyek->tipe_nominal == 'k'){
                $histProRow->saldo += $nominal;
            }else{
                $histProRow->saldo -= $nominal;
            }
            $histProRow->save();
        }
        if($transProyek->tipe_nominal == 'k'){
            $proyek->saldo += $nominal;
        }else{
            $proyek->saldo -= $nominal;
        }
        if($proyek->save()){
            $deleted = TransaksiProyek::destroy($transProyek->id);
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
            $transaksiProyek = TransaksiProyek::where('id_transaksi',$request->idTransaksi)->first();
            $proyek = Proyek::findOrFail($transaksiProyek->id_kegiatan);
            /* ambil semua histori saldo proyek perbulan pada tanggal $transaksi dan setelahnya */
            $historiProyek = HistoriSaldoProyekPerbulan::where('id_kegiatan',$transaksiProyek->id_kegiatan)
                             ->where(function($first) use ($tahun,$bulan){
                                $first->where(function($second) use ($tahun,$bulan){
                                    $second->where('tahun',(int)$tahun)->where('bulan','>=',(int)$bulan);
                                })->orWhere('tahun','>',(int)$tahun);
                             })->orderBy('tahun','asc')->orderBy('bulan','asc')->get();
            /* ganti status transaksi 1 */
            $transaksi->status = 1;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->nominal = $request->nominal;
            if($transaksi->save()){
                /* ganti tipe_nominal sesuai input dan status 1*/
                $transaksiProyek->tipe_nominal = $request->nominalType;
                $transaksiProyek->status = 1;
                $transaksiProyek->save();
                /* jumlahin saldo di bulan $transaksi pada proyek */
                if($transaksiProyek->tipe_nominal == 'k'){
                    $proyek->saldo += $transaksi->nominal;
                }else{
                    $proyek->saldo -= $transaksi->nominal;
                }
                $proyek->save();
                if($historiProyek->isEmpty()){
                    /* jumlahin saldo di bulan $transaksi pada histori proyek */
                    foreach($historiProyek as $hisRow){
                        $hisRow->id_kegiatan = $proyek->id;
                        if($transaksiProyek->tipe_nominal == 'k'){
                            $hisRow->saldo += $transaksi->nominal;
                        }else{
                            $hisRow->saldo -= $transaksi->nominal;
                        }
                        $hisRow->save();
                    }
                }else{
                    /* buat record baru kalo ga ada histori */
                    $prevHistoriProyek = HistoriSaldoProyekPerbulan::where('id_kegiatan',$transaksiProyek->id_kegiatan)->where(function($first) use ($tahun,$bulan)
                                        {
                                            $first->where(function($second) use ($tahun,$bulan){
                                                $second->where('tahun',(int)$tahun)->where('bulan','<',(int)$bulan);
                                            })->orWhere('tahun','<',(int)$tahun);
                                        })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                    $historiProyek = new HistoriSaldoProyekPerbulan;
                    $historiProyek->id_kegiatan = $proyek->id;
                    $historiProyek->tahun = $tahun;
                    $historiProyek->bulan = $bulan;
                    if($prevHistoriProyek == null){
                        /* tidak ada histori sama sekali */
                        $historiProyek->saldo = $proyek->saldo;
                    }else{
                        /* tidak ada histori sekarang dan setelahnya tapi ada histori sebelumnya */
                        $historiProyek->saldo = $prevHistoriProyek->saldo;
                        if($transaksiProyek->tipe_nominal == 'k'){
                            $historiProyek->saldo += $transaksi->nominal;
                        }else{
                            $historiProyek->saldo -= $transaksi->nominal;
                        }
                    }
                    $historiProyek->save();
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
    public function reverseNominalType($nominalType){
        if($nominalType == 'd'){
            return 'k';
        }
        else return 'd';
    }
    // return semua pengajuan penggunaan dana proyek
    public function getAllPengajuanDanaProyek(Request $request){
        try{
            $data = collect();
            $dataindex = 0;
            $tanggal = date('Y-m-d',strtotime($request->tanggal)); 
            Log::info($tanggal);
            if($request->has('idProyek')){
                $transaksi = Transaksi::whereHas('transaksiProyek',function($query) use ($request){
                                            $query->where('id_kegiatan',$request->idProyek);
                                        })->with(['transaksiProyek'=>function($query) use ($request){
                                            $query->with(['proyek'])->where('id_kegiatan',$request->idProyek);
                                        }])->with(['pegawai'])->where('tanggal',$tanggal)->where('status',3)->get();
            }else{
                $transaksi = Transaksi::whereHas('transaksiProyek')->with(['transaksiProyek'=>function($query){
                                            $query->with(['proyek']);
                                        }])->with(['pegawai'])->where('status',3)->get();
            }
            if(!$transaksi->isEmpty()){
                foreach($transaksi as $transRow){
                    $data->push([
                        'id'=> $transRow->id,
                        'tanggal'=> $transRow->tanggal,
                        'pegawai'=> $transRow->pegawai->nama,
                        'keteranganFull'=> $transRow->transaksiProyek[0]->keterangan.' '.$transRow->transaksiProyek[0]->jumlah
                                       .' '.$transRow->transaksiProyek[0]->unit.' x '.$transRow->transaksiProyek[0]->perkiraan_biaya,
                        'keterangan'=> $transRow->transaksiProyek[0]->keterangan,
                        'jumlah'=> $transRow->transaksiProyek[0]->jumlah,
                        'unit'=> $transRow->transaksiProyek[0]->unit,
                        'perkiraan_biaya'=> $transRow->transaksiProyek[0]->perkiraan_biaya,
                        'nominal'=> $transRow->nominal,
                        'kategori'=> $transRow->transaksiProyek[0]->proyek->nama_kegiatan,
                    ]);
                }
                $dataGrouped = $data->groupBy('tanggal')->transform(function($item,$key){
                    return $item->groupBy('kategori');
                });
                $returnedData = collect();
                foreach($dataGrouped as $dataTanggal){
                    foreach($dataTanggal as $dataKategori){
                        $sum = 0;
                        for($i=0; $i<sizeof($dataKategori); $i++) {$sum += $dataKategori[$i]['nominal'];}
                        $returnedData->push([
                            'tanggal'=> $dataKategori[0]['tanggal'],
                            'pegawai'=> $dataKategori[0]['pegawai'],
                            'kategori'=> $dataKategori[0]['kategori'],
                            'total'=>$sum,
                            'item'=> $dataKategori
                        ]);
                    }
                }
                return ['data'=>$returnedData,'empty'=>false];
            }else{
                return ['empty'=>true];
            }
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function deletePengajuanDana(Request $request){
        $idTransaksi = $request->idTransaksi;
        $transProyek = TransaksiProyek::where('id_transaksi',$idTransaksi);
        $deletedTransProyek = $transProyek->delete();
        $transaksi = Transaksi::find($idTransaksi);
        $deletedTransaksi = $transaksi->delete();
        return ['status'=>true];
    }

    public function getAllTransaksiProyek(Request $request){
        try{
            $data = [];
            $dataindex = 0;
            $bulan = '';
            $saldo = 0;
            $transaksi = Transaksi::whereHas('transaksiProyek',function($query) use ($request){
                                        $query->where('id_kegiatan',$request->idProyek);
                                    })->with(['transaksiProyek'=>function($query) use ($request){
                                        $query->with(['proyek'])->where('id_kegiatan',$request->idProyek);
                                    }])->with(['pegawai'])->whereYear('tanggal',$request->tahun)
                                    ->where('status',1)->orderBy('tanggal','desc')->get();
            $historiSimpanan = HistoriSaldoProyekPerbulan::where('id_kegiatan',$request->idProyek)
                                ->where('tahun',(int)$request->tahun)
                                ->get();
            if(!$transaksi->isEmpty()){
                foreach($transaksi as $transProyekRow){
                    $time  = strtotime($transProyekRow->tanggal);
                    if($bulan != date('n',$time)){
                        $firstRowinBulan = true;
                        $bulan = date('n',$time);
                        //ambil histori saldo pada $bulan dari tanggal transaksi
                        $historiProyekBulan = $historiSimpanan->where('bulan',(int)$bulan)->first();
                        // disimpan di saldo
                        $saldo = $historiProyekBulan->saldo;
                    }
                    if(!$firstRowinBulan){
                        //kalo kredit saldo ditambah, debit saldo dikurang
                        if($transaksi[$dataindex-1]->transaksiProyek[0]->tipe_nominal == 'k'){
                            $saldo += $transaksi[$dataindex-1]->nominal;
                        }else{
                            $saldo -= $transaksi[$dataindex-1]->nominal;
                        }
                    }
                    $firstRowinBulan = false;
                    $data[$dataindex] = [
                        'id_transaksi'=>$transProyekRow->id,
                        'jenis_transaksi'=> 3,
                        'id' => $transProyekRow->transaksiProyek[0]->id,
                        'tanggal'=> $transProyekRow->tanggal,
                        'pegawai'=> $transProyekRow->pegawai->nama,
                        'keterangan'=> $transProyekRow->keterangan,
                        'nominal_type'=> $transProyekRow->transaksiProyek[0]->tipe_nominal,
                        'nominal'=> $transProyekRow->nominal,
                        'saldo'=> $saldo,
                        'kategori'=> $transProyekRow->transaksiProyek[0]->proyek->nama_kegiatan,
                        'edit_able'=> true,
                        'delete_able'=> true
                    ];
                    $dataindex++;
                }
                if($transaksi[$dataindex-1]->transaksiProyek[0]->tipe_nominal == 'k'){
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

    public function generateExcelProyek(Request $request){
        try{
            $data = $this->getAllTransaksiProyek($request);
            if($data['empty'] == false){
                $transaksiController = new TransaksiController;
                $array = $transaksiController->cvtArray($data['data']);
                $proyek = Proyek::find($request->idProyek);
                $sheetname = $request->tahun;
                $filename = 'Laporan Keuangan Proyek '.$proyek->nama_kegiatan.' '.$request->tahun;
                $title = 'Laporan Keuangan '.$proyek->nama_kegiatan;
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

    public function downloadExcelProyek(Request $request){
        $proyek = Proyek::find($request->idProyek);
        $sheetname = $request->tahun;
        $filename = 'Laporan Keuangan Proyek '.$proyek->nama_kegiatan.' '.$request->tahun;
        $fileContents = Storage::disk('export')->get($filename.'.xlsx');
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', MimeType::get('xlsx'));
        return $response;
    }

    public function storePengajuanDanaAsisten(Request $request){
        try{
            $idProyek = $request->idProyek['value'];
            $idPegawai = $request->idPegawai['value'];
            $tanggal = date('Y-m-d',strtotime($request->tanggal)); 
            $perkiraanBiaya = $request->perkiraanBiaya;
            $keterangan = $request->keterangan;
            $jumlah = $request->jumlah;
            $unit = $request->unit;
            $arrLength = sizeof($perkiraanBiaya);
            Log::info($arrLength);
            $transaksiList = collect();
            for($i = 0; $i < $arrLength; $i++){
                $transaksi = new Transaksi;
                $transaksiProyek = new TransaksiProyek;
                $transaksi->id_pegawai = $idPegawai;
                $transaksi->nominal = $jumlah[$i]['value'] == 0 ? 
                    $perkiraanBiaya[$i]['value'] : ($perkiraanBiaya[$i]['value'] * $jumlah[$i]['value']);
                $transaksi->tanggal = $tanggal;
                $transaksi->status = 3;
                if($transaksi->save()){
                    $transaksiProyek->id_transaksi = $transaksi->id;
                    $transaksiProyek->id_kegiatan = $idProyek;
                    $transaksiProyek->keterangan = $keterangan[$i];
                    $transaksiProyek->jumlah = $jumlah[$i]['value'];
                    $transaksiProyek->unit = $unit[$i];
                    $transaksiProyek->perkiraan_biaya = $perkiraanBiaya[$i]['value'];
                    $transaksiProyek->status = 3;
                    $transaksiProyek->save();
                }
                $transaksiList->push($transaksi->id);
                Log::info($transaksiList);
            }
            return ['data'=>$transaksiList];
        }catch(Exception $err){
            Log::info($err);
        }
    }

    public function generatePdf(Request $request){
        $transaksi = $request->transaksiList;
        $transaksi = Transaksi::whereHas('transaksiProyek')->with(['transaksiProyek'=>function($query){
                                            $query->with(['proyek']);
                                        }])->with(['pegawai'])->whereIn('id',$transaksi)->get();
        $pdfController = new GeneratePdfController;
        return $pdfController->generate($transaksi);
    }

    public function downloadPdf(Request $request){
        $tanggal = date('Y-m-d',strtotime($request->tanggal)); 
        $transaksi = Transaksi::whereHas('transaksiProyek',function($query) use ($request){
                                            $query->where('id_kegiatan',$request->idProyek);
                                        })->with(['transaksiProyek'=>function($query) use ($request){
                                            $query->with(['proyek'])->where('id_kegiatan',$request->idProyek);
                                        }])->with(['pegawai'])->where('tanggal',$tanggal)->where('status',3)->get();
        $pdfController = new GeneratePdfController;
        return $pdfController->generate($transaksi);
    }
}
