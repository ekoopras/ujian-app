<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $fillable = [
        'bank_soal_id',
        'jenis_soal',
        'pertanyaan',
        'gambar_soal',
        'pilihan_jawaban',
        'kunci_jawaban',
        'bobot_nilai',
    ];

    protected $casts = [
        'pilihan_jawaban' => 'array', // Mengubah JSON menjadi array
    ];
}
