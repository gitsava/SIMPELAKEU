<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiUnit extends Model
{
    //
    protected $table = 'transaksi_unit';
    public $timestamps = false;
    protected $fillable = [
        'id_transaksi', 'id_unit', 'tipe_nominal', 'status', 'keterangan',
        'kegiatan', 'jumlah', 'unit', 'perkiraan_biaya'
    ];

    public function transaksi()
    {
        return $this->belongsTo('App\Transaksi','id_transaksi');
    }

    public function Unit()
    {
        return $this->belongsTo('App\Unit','id_unit');
    }
}
