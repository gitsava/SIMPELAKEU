<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Simpanan extends Model
{
    //
    protected $table = 'akun_bank';
    public $timestamps = false;
    protected $fillable = [
        'nama_bank', 'saldo', 'status'
    ];

    public function transaksiBank()
    {
        return $this->hasMany('App\TransaksiBank','id_bank');
    }

    public function historiSaldo()
    {
        return $this->hasMany('App\HistoriSaldoSimpananPerbulan','id_bank');
    }
}
