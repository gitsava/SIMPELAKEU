<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoriSaldoUnitPerbulan extends Model
{
    //
    protected $table = 'histori_saldo_unit_perbulan';
    public $timestamps = false;
    protected $fillable = [
        'id_unit','tahun', 'bulan', 'saldo'
    ];

    public function unit()
    {
        return $this->belongsTo('App\Unit','id_unit');
    }
}
