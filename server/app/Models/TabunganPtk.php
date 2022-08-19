<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabunganPtk extends Model
{
    use HasFactory;

    public $table = 'tabungan_ptk';

    public $fillable = [
        'ptk_id',
        'user_id',
        'nominal',
    ];

    public $casts = [
        'nominal' => 'int',
    ];

    public $with = [
        'ptk',
        'user',
    ];

    public function ptk()
    {
        return $this->belongsTo(Ptk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
