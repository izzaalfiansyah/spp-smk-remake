<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaLain extends Model
{
    use HasFactory;

    public $table = 'biaya_lain';

    public $fillable = [
        'jenis',
        'jumlah_bayar',
        'jurusan_data',
        'kelas_data',
    ];

    public $casts = [
        'jurusan_data' => 'object',
        'kelas_data' => 'object',
        'jumlah_bayar' => 'int',
    ];
}
