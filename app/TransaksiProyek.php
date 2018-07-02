<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiProyek extends Model
{
    //
    protected $table = 'transaksi_proyek';
    public $timestamps = false;
    protected $fillable = [
        'id_transaksi', 'id_kegiatan', 'tipe_nominal', 'status',
    ];

    public function transaksi()
    {
        return $this->belongsTo('App\Transaksi','id_transaksi');
    }

    public function proyek()
    {
        return $this->belongsTo('App\Proyek','id_kegiatan');
    }
	
}
