<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Simpanan;

class TransaksiBank extends JsonResource
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
            'id_bank' => $this->id_bank,
            'id_transaksi'=> $this->id_transaksi,
            'simpanan'=> Simpanan::collection($this->simpanan),
            'nominal_type' => $this->nominal_type,
        ];
    }
}
