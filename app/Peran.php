<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    //
	protected $table = 'peran';
    public $timestamps = false;
    protected $fillable = [
        'nama_peran'
    ];

    public function peran()
    {
        return $this->hasMany('App\PesertaKegiatan','id_peran');
    }
	
}
