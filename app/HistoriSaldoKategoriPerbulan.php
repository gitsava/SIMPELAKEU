<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoriSaldoKategoriPerbulan extends Model
{
    //
    protected $table = 'histori_saldo_kategoriumum_perbulan';
    public $timestamps = false;
    protected $fillable = [
        'id_kategori','tahun', 'bulan', 'saldo'
    ];

    public function kategori()
    {
        return $this->belongsTo('App\KategoriUmum','id_kategori');
    }
}
