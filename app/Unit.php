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

    public function transaksi()
    {
        return $this->hasMay('App\Pegawai','foreign_key','id_unit');
    }
}
