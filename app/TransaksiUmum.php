<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiUmum extends Model
{
    //
    protected $table = 'transaksi_umum';
    public $timestamps = false;
    protected $fillable = [
        'id_transaksi', 'id_kategori', 'tipe_nominal', 'status'
    ];

    public function transaksi()
    {
        return $this->belongsTo('App\Transaksi','id_transaksi');
    }

    public function kategori()
    {
        return $this->belongsTo('App\KategoriUmum','id_kategori');
    }
}
