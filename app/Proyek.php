<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proyek extends Model
{
    //
    protected $table = 'kegiatan';
    public $timestamps = false;

    public function transaksiProyek()
    {
        return $this->hasMany('App\TransaksiProyek','id_kegiatan');
    }

    public function historiSaldo()
    {
        return $this->hasMany('App\HistoriSaldoProyekPerbulan','id_kegiatan');
    }

    public function tipeKegiatan()
    {
        return $this->belongsTo('App\Proyek','id_tipe_kegiatan');
    }
	
	public function peserta()
    {
        return $this->hasMany('App\PesertaKegiatan','id_kegiatan');
    }
}
