<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoriSaldoProyekPerbulan extends Model
{
    //
    protected $table = 'histori_saldo_proyek_perbulan';
    public $timestamps = false;
    protected $fillable = [
        'id_kegiatan','tahun', 'bulan', 'saldo'
    ];

    public function proyek()
    {
        return $this->belongsTo('App\Proyek','id_kegiatan');
    }
}
