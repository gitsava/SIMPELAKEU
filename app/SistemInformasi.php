<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SistemInformasi extends Model
{
    //
    protected $table = 'sistem_informasi';
    public $timestamps = false;
    protected $fillable = [
        'nama_sistem',
    ];

    public function userSI()
    {
        return $this->hasMay('App\tblUserSI','foreign_key','id_si');
    }
}
