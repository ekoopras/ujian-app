<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpsiSoalUjian extends Model
{
    protected $fillable = ['soal_ujian_id', 'text_jawaban', 'gambar_jawaban', 'kunci_pasangan', 'is_correct', 'nilai'];

    public function soalUjian(): BelongsTo
    {
        return $this->belongsTo(SoalUjian::class);
    }
}
