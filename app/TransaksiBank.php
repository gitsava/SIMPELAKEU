<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiBank extends Model
{
    //
    protected $table = 'transaksi_bank';
    public $timestamps = false;
    protected $fillable = [
        'id_transaksi', 'id_bank', 'tipe_nominal', 'status',
    ];

    public function transaksi()
    {
        return $this->belongsTo('App\Transaksi','id_transaksi');
    }

    public function simpanan()
    {
        return $this->belongsTo('App\Simpanan','id_bank');
    }
}
