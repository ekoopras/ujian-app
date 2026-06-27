<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Siswa extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        // Mengunci query agar model ini hanya membaca user dengan role 'siswa'
        static::addGlobalScope('siswa', function (Builder $builder) {
            $builder->where('role', 'siswa');
        });
    }
}
