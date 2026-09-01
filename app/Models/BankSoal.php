<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankSoal extends Model
{
    protected $fillable = [
        'nama',
        'user_id',
        'mapel_id',
        'tahun_ajaran_id',
        'kelas',
    ];

    /**
     * Accessor untuk menghitung Total Jumlah Soal
     */
    public function getTotalSoalAttribute(): int
    {
        return $this->soals()->count();
    }

    public function getTotalNilaiAttribute(): int
    {
        $soals = $this->soals()->get();

        $totalNilai = 0;

        foreach ($soals as $soal) {
            $pilihanJawaban = $soal->pilihan_jawaban ?? [];

            if (is_array($pilihanJawaban)) {
                $totalNilai += array_sum(array_column($pilihanJawaban, 'nilai'));
            }
        }

        return $totalNilai;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function soals()
    {
        return $this->hasMany(Soal::class);
    }
}
