<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    //
    protected $table = 'unit_kerja';
    public $timestamps = false;
    protected $fillable = [
        'nama',
    ];

    public function transaksiUnit()
    {
        return $this->hasMay('App\TransaksiUnit','id_unit');
    }
}
