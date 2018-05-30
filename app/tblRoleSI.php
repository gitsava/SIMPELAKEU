<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblRoleSI extends Model
{
    //
    protected $table = 'tbl_role_si';
    public $timestamps = false;
    protected $fillable = [
        'id_si', 'id_role',
    ];

    public function roleSI()
    {
        return $this->belongsTo('App\SistemInformasi');
    }
    public function siRole()
    {
        return $this->belongsTo('App\Role');
    }
}
