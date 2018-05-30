<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoriSaldoSimpananPerbulan extends Model
{
    //
    protected $table = 'histori_saldo_bank_perbulan';
    public $timestamps = false;
    protected $fillable = [
        'id_bank','tahun', 'bulan', 'saldo'
    ];

    public function simpanan()
    {
        return $this->belongsTo('App\Simpanan','id_bank');
    }
}
