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
        return $this->hasMay('App\TransaksiBank','id_bank');
    }

    public function historiSaldo()
    {
        return $this->hasMay('App\HistoriSaldoSimpananPerbulan','id_bank');
    }
}
