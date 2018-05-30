<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KategoriUmum extends JsonResource
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
            'nama_kategori' => $this->nama_kategori,
            'saldo' => $this->saldo,
            'status' => $this->status,
            'empty' => false
        ];
    }
}
