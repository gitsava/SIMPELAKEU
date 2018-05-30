<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblUserSI extends Model
{
    //
    protected $table = 'tbl_user_si';
    public $timestamps = false;
    protected $fillable = [
        'id_user', 'id_si', 'id_role',
    ];

    public function userSI()
    {
        return $this->belongsTo('App\SistemInformasi','id_si');
    }
    public function userRole()
    {
        return $this->belongsTo('App\Role','id_role');
    }
    public function userRoleSI()
    {
        return $this->belongsTo('App\User','id_user');
    }
}
