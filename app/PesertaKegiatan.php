<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PesertaKegiatan extends Model
{
    //
	protected $table = 'peserta_kegiatan';
    public $timestamps = false;
    protected $fillable = [
        'id_peneliti','id_kegiatan','status_konfirm','id_peran'
    ];

    public function peran()
    {
        return $this->belongsTo('App\Peran','id_peran');
    }
	
	public function peneliti()
    {
        return $this->belongsTo('App\Peneliti','id_peneliti');
    }
	
	public function kegiatan()
    {
        return $this->belongsTo('App\Proyek','id_kegiatan');
    }
}
