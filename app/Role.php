<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    protected $table = 'role';
    public $timestamps = false;
    protected $fillable = [
        'nama_role', 'keterangan',
    ];

    public function roleSI()
    {
        return $this->hasMany('App\tblRoleSI','id_role');
    }
    public function userRole()
    {
        return $this->hasMany('App\tblUserSI','id_role');
    }
}
