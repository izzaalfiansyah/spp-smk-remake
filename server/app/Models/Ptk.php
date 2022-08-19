<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ptk extends Model
{
    use HasFactory;

    public $table = 'ptk';

    public $fillable = [
        'kode',
        'nama',
        'jabatan',
    ];
}
