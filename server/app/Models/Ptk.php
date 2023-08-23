<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ptk extends Model
{
    use HasFactory;

    public $table = 'ptk';

    public $fillable = [
        'kode',
        'nama',
        'jabatan',
    ];

    public $appends = [
        'total_saldo',
    ];

    function getTotalSaldoAttribute()
    {
        $tabungan =  DB::table('tabungan_ptk')->selectRaw("sum(nominal) as total_saldo")->where('ptk_id', $this->id)->first();

        return (int) $tabungan->total_saldo;
    }
}
