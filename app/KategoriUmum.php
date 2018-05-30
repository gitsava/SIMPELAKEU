<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KategoriUmum extends Model
{
    //
    protected $table = 'kategori_transaksi_umum';
    public $timestamps = false;
    protected $fillable = [
        'nama_kategori', 'saldo', 'status'
    ];

    public function transaksiUmum()
    {
        return $this->hasMay('App\TransaksiUmum','foreign_key','id_kategori');
    }

    public function historiSaldo()
    {
        return $this->hasMany('App\HistoriSaldoKategoriPerbulan','id_kategori');
    }
}
