<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Ujian extends Model
{
    protected $fillable = [
        'mapel_id',
        'bank_soal_id',
        'tahun_ajaran_id',
        'token',
        'durasi_menit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ujian) {
            if (empty($ujian->token)) {
                $ujian->token = strtoupper(Str::random(6));
            }
        });
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kelases(): BelongsToMany
    {
        return $this->belongsToMany(Kelase::class, 'kelase_ujian', 'ujian_id', 'kelase_id');
    }
}
