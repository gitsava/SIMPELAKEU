<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Simpanan extends JsonResource
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
            'nama_bank' => $this->nama_bank,
            'saldo' => $this->saldo,
            'status' => $this->status,
        ];
    }
}
