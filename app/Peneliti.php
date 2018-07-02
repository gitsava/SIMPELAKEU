<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peneliti extends Model
{
    //
	protected $table = 'peneliti';
    public $timestamps = false;
    protected $fillable = [
        
    ];
	
	public function penelitipsb()
    {
        return $this->hasOne('App\PenelitiPSB','id_peneliti');
    }
}
