<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\KategoriUmum;

class TransaksiUmum extends JsonResource
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
            'id_kategori' => $this->id_kategori,
            'id_transaksi'=> $this->id_transaksi,
            'kategori'=> KategoriUmum::collection($this->kategori),
            'nominal_type' => $this->nominal_type,
        ];
    }
}
