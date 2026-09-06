<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianSiswa extends Model
{
    protected $fillable = [
        'ujian_id',
        'user_id',
        'waktu_mulai',
        'waktu_selesai_seharusnya',
        'waktu_submit',
        'jawaban_siswa',
        'status',
        'ragu_siswa',
        'is_rekaped',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai_seharusnya' => 'datetime',
        'waktu_submit' => 'datetime',
        'jawaban_siswa' => 'array',
        'ragu_siswa' => 'array',
    ];

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'nama_mapel', 'name');
    }
}
