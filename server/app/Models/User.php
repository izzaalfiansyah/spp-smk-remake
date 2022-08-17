<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    public $table = 'user';

    public $fillable = [
        'username',
        'password',
        'nama',
        'foto',
        'role',
    ];

    public $appends = [
        'foto_url',
        'role_detail',
    ];

    public function getFotoUrlAttribute()
    {
        return asset('user/' . ($this->foto ?: 'default.png'));
    }

    public function getRoleDetailAttribute()
    {
        return $this->role == '1' ? 'superadmin' : 'administrator';
    }
}
