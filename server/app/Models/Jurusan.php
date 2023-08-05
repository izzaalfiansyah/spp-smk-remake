<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    public $table = 'jurusan';

    public $primaryKey = 'kode';

    public $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public $fillable = [
        'kode',
        'nama',
        'kategori',
        'jumlah_spp',
        'tabungan_wajib',
    ];

    public $casts = [
        'jumlah_spp' => 'int',
    ];
}
