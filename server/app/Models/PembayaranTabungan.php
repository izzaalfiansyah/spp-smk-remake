<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTabungan extends Model
{
    use HasFactory;

    public $table = 'pembayaran_tabungan';

    public $fillable = [
        'siswa_nisn',
        'user_id',
        'jumlah_bayar',
        'status_tabungan',
    ];

    public $casts = [
        'jumlah_bayar' => 'int',
    ];

    public $with = [
        'user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public $appends = [
        'status_tabungan_detail',
    ];

    public function getStatusTabunganDetailAttribute()
    {
        return $this->status_tabungan == '1' ? 'Wajib' : 'Pribadi';
    }
}
