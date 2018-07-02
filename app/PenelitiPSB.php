<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PenelitiPSB extends Model
{
    //
	protected $table = 'peneliti_psb';
    public $timestamps = false;
    protected $fillable = [
        'id_peneliti','id_pegawai','id_departemen'
    ];
	
	public function pegawai()
    {
        return $this->belongsTo('App\Pegawai','id_pegawai');
    }
	
	public function peneliti()
    {
        return $this->belongsTo('App\Peneliti','id_peneliti');
    }
}
