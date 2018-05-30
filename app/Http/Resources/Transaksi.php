<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TransaksiBank;
use App\Http\Resources\TransaksiUmum;
use App\Http\Resources\Pegawai;

class Transaksi extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'id_pegawai' => $this->id_pegawai,
            'pegawai'=> Pegawai::collection($this->pegawai),
            'keterangan'=> $this->keterangan, 
            'nominal'=> $this->nominal, 
            'tanggal'=> $this->tanggal, 
            'status'=> $this->status,
            'transaksiBank'=> TransaksiBank::collection($this->transaksiBank),
            'transaksiUmum'=> TransaksiUmum::collection($this->transaksiUmum)
        ];
    }
}