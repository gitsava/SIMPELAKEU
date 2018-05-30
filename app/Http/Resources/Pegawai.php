<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Pegawai extends JsonResource
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
            'id_unit' => $this->id_unit,
            'nama' => $this->nama,
            'nip' => $this->nip, 
            'gelar_depan' => $this->gelar_depan,
            'gelar_belakang' => $this->gelar_belakang, 
            'no_ktp' => $this->no_ktp, 
            'tanggal_lahir' => $this->tanggal_lahir, 
            'tempat_lahir' => $this->tempat_lahir, 
            'jabatan' => $this->jabatan, 
            'jenis_kelamin' => $this->jenis_kelamin,
            'agama' => $this->agama, 
            'status_kawin' => $this->status_kawin, 
            'email' => $this->email, 
            'nomor_hp' => $this->nomor_hp, 
            'telepon' => $this->telepon, 
            'faks' => $this->faks, 
            'alamat' => $this->alamat, 
            'gambar' => $this->gambar,
        ];
    }
}
