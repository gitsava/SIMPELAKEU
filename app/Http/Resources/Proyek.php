<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Proyek extends JsonResource
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
            'id_tipe_kegiatan' => $this->id_tipe_kegiatan,
            'nama_proyek' => $this->nama_kegiatan,
            'tanggal_awal' => $this->tanggal_awal,
            'tanggal_akhir' => $this->tanggal_akhir,
            'keterangan' => $this->keteragan,
            'lokasi' => $this->lokasi,
            'saldo' => $this->saldo,
        ];
    }
}
