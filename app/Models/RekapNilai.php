<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapNilai extends Model
{
    protected $fillable = [
        'ujian_id',
        'user_id',
        'tahun_ajaran_id',

        'nama_mapel',
        'nama_siswa',
        'nis',
        'nama_kelas',
        'total_soal',
        'jawaban_benar',
        'jawaban_salah',
        'nilai_akhir',
        'tahun_ajaran',
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

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}
