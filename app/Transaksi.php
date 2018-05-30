<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    //
    protected $table = 'transaksi';
    public $timestamps = false;
    protected $fillable = [
        'id_pegawai', 'keterangan', 'nominal', 'tanggal', 'saldo',
    ];

    public function pegawai()
    {
        return $this->belongsTo('App\Pegawai','id_pegawai');
    }

    public function transaksiUmum()
    {
        return $this->hasMany('App\TransaksiUmum','id_transaksi');
    }

    public function transaksiBank()
    {
        return $this->hasMany('App\TransaksiBank','id_transaksi');
    }

    public function transaksiProyek()
    {
        return $this->hasMany('App\TransaksiProyek','id_transaksi');
    }
}
