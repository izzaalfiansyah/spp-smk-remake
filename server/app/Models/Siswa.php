<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    public $table = 'siswa';

    public $primaryKey = 'nisn';

    public $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public $fillable = [
        'nisn',
        'nama',
        'jurusan_kode',
        'kelas',
        'rombel',
        'diskon_spp',
        'diskon_biaya_lain',
    ];

    public $casts = [
        'diskon_spp' => 'int',
        'diskon_biaya_lain' => 'int',
    ];

    public $with = [
        'jurusan',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_kode');
    }
}
