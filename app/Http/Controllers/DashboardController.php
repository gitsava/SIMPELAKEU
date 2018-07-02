<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use App\HistoriSaldoSimpananPerbulan as HistoriSimpanan;

class DashboardController extends Controller
{
    //
    public function getHistoriSimpanan(Request $request){
        try{
            $idSimpanan = $request->simpanan;
            $tahun = $request->tahun;
			$data = [];
            $HistSimpanan = HistoriSimpanan::where('id_bank',$idSimpanan)
                            ->where('tahun',$tahun)->orderBy('bulan','asc')->get();
            if(!$HistSimpanan->isEmpty()){
				foreach($HistSimpanan as $hist){
					$data[($hist->bulan)-1] = $hist->saldo;
				}
				if(!isset($data[0])){
					$prevHistSimpanan = HistoriSimpanan::where('id_bank',$idSimpanan)
										->where('tahun','<',$tahun)->orderBy('tahun','desc')
										->orderBy('bulan','desc')->first();
					if(isset($prevHistSimpanan)){
						$data[0] = $prevHistSimpanan->saldo;
					}else{
						$data[0] = 0;
					}
				}
				for($dataIndex = 0; $dataIndex < 12; $dataIndex++){
					if(!isset($data[$dataIndex])){
						$data[$dataIndex] = $data[$dataIndex-1];
					}
				}
                return ['data'=>$data,'empty'=>false];    
            }
            return ['empty'=>true];
        }catch(Exception $err){
            Log::info($err);
        }
    }
}
