<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pegawai;
use App\Unit;
use App\Http\Resources\Pegawai as PegawaiResource;

class PegawaiController extends Controller
{
    //
    public function getAllPegawaiList(){
        \DB::connection()->enableQueryLog();
        $pegawai = Pegawai::all();
        $queries = \DB::getQueryLog();
        //return dd($pegawai);
        return PegawaiResource::collection($pegawai);
    }
}
