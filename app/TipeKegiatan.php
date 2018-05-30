<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipeKegiatan extends Model
{
    //
    protected $table = 'tipe_kegiatan';
    public $timestamps = false;

    public function proyek()
    {
        return $this->hasMany('App\Proyek','id_tipe_kegiatan');
    }
}
