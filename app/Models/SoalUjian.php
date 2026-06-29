<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoalUjian extends Model
{
    protected $fillable = [
        'bank_soal_id',
        'text_soal',
        'tipe_soal'
    ];

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class);
    }

    // Relasi ke tabel opsi jawaban
    public function opsis(): HasMany
    {
        return $this->hasMany(OpsiSoalUjian::class, 'soal_ujian_id');
    }
}
