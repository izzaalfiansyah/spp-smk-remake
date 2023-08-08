<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    use HasFactory;

    public $table = 'pembayaran_spp';

    public $fillable = [
        'siswa_nisn',
        'user_id',
        'bulan',
        'jumlah_bayar',
        'tabungan_wajib',
        'uang_praktik',
        'status_kelas'
    ];

    public $casts = [
        'jumlah_bayar' => 'int',
        'tabungan_wajib' => 'int',
        'uang_praktik' => 'int',
    ];

    public $with = [
        'user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
