<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranBiayaLain extends Model
{
    use HasFactory;

    public $table = 'pembayaran_biaya_lain';

    public $fillable = [
        'siswa_nisn',
        'user_id',
        'biaya_lain_id',
        'jumlah_bayar',
    ];

    public $casts = [
        'jumlah_bayar' => 'int',
    ];

    public $with = [
        'user',
        'biaya_lain',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function biaya_lain()
    {
        return $this->belongsTo(BiayaLain::class);
    }

    public $appends = [
        'print_url',
    ];

    public function getPrintUrlAttribute()
    {
        return url('/pembayaran/biaya-lain/' . $this->id . '/print');
    }
}
