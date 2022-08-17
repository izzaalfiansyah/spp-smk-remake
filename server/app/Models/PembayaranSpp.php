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
        'status_kelas'
    ];

    public $with = [
        'user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
