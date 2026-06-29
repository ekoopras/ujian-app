<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SesiUjian extends Model
{
    protected $fillable = [
        'mapel_id',
        'bank_soal_id',
        'durasi',
        'token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function kelase(): BelongsToMany
    {
        return $this->belongsToMany(Kelase::class, 'kelas_sesi_ujian', 'sesi_ujian_id', 'kelase_id');
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'bank_soal_id');
    }
}
