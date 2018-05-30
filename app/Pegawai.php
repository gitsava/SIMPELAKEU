<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    //
    protected $table = 'pegawai';
    public $timestamps = false;
    protected $fillable = [
        'id_unit','nama', 'nip', 'gelar_depan', 'gelar_belakang', 'no_ktp', 
        'tanggal_lahir', 'tempat_lahir', 'jabatan', 'jenis_kelamin',
        'agama', 'status_kawin', 'email', 'nomor_hp', 'telepon', 
        'faks', 'alamat', 'gambar',
    ];

    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }

    public function transaksi()
    {
        return $this->hasMay('App\Transaksi','id_pegawai');
    }

}
