<?php

namespace App\Filament\Resources\Ujian\UjianSiswaResource\Pages;

use App\Filament\Resources\Ujian\UjianSiswaResource;
use App\Models\RekapNilai;
use App\Models\TahunAjaran;
use App\Models\Ujian;
use App\Models\UjianSiswa;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListUjianSiswas extends ListRecords
{
    protected static string $resource = UjianSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('rekapNilai')
                ->label('Rekap Semua Nilai Ujian')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Rekap Nilai Otomatis')
                ->modalDescription('Apakah Anda yakin ingin merekap semua data ujian siswa yang belum direkap?')
                ->action(function () {
                    // Ambil Tahun Ajaran Aktif
                    $taAktif = TahunAjaran::where('is_active', true)->first();
                    $tahunAjaranId = $taAktif?->id;
                    $tahunAjaranText = $taAktif
                        ? $taAktif->tahun . ' (' . ucfirst($taAktif->semester) . ')'
                        : '-';

                    $totalBerhasil = 0;

                    // Load Eager Loading untuk efisiensi query
                    UjianSiswa::with(['user', 'ujian.mapel', 'ujian.soals'])
                        ->where('status', 'selesai')
                        ->where('is_rekaped', false)
                        ->chunkById(100, function ($ujianSiswas) use ($tahunAjaranId, $tahunAjaranText, &$totalBerhasil) {
                            foreach ($ujianSiswas as $ujianSiswa) {
                                DB::transaction(function () use ($ujianSiswa, $tahunAjaranId, $tahunAjaranText, &$totalBerhasil) {
                                    $user = $ujianSiswa->user;
                                    $ujian = $ujianSiswa->ujian;

                                    if (!$user || !$ujian) {
                                        return;
                                    }

                                    // Ambil koleksi soal via HasManyThrough
                                    $soals = $ujian->soals;

                                    // Jika soal tidak ditemukan, lewati (jangan ditandai is_rekaped dulu)
                                    if ($soals->isEmpty()) {
                                        return;
                                    }

                                    $jawabanSiswa = $ujianSiswa->jawaban_siswa ?? [];

                                    $totalSoal = count($soals);
                                    $jawabanBenar = 0;
                                    $jawabanSalah = 0;
                                    $bobotDapat = 0;

                                    foreach ($soals as $soal) {
                                        $bobotSoal = $soal->bobot_nilai ?? 1;
                                        $jawaban = $jawabanSiswa[$soal->id] ?? null;

                                        // Pencocokan Jawaban
                                        $isBenar = false;

                                        if (is_array($jawaban)) {
                                            if ($soal->jenis_soal === 'pilihan_ganda_kompleks') {
                                                $pilihanJawaban = is_array($soal->pilihan_jawaban)
                                                    ? $soal->pilihan_jawaban
                                                    : json_decode($soal->pilihan_jawaban ?? '[]', true);

                                                if (!empty($jawaban)) {
                                                    $poinDapatKompleks = 0;
                                                    $totalPoinMaksimal = 0;

                                                    foreach ($pilihanJawaban as $item) {
                                                        $teksOpsi = $item['teks'] ?? '';
                                                        $nilaiOpsi = (int) ($item['nilai'] ?? 0);
                                                        $isActive = $item['is_active'] ?? false;

                                                        if ($isActive || $nilaiOpsi > 0) {
                                                            $totalPoinMaksimal += $nilaiOpsi;
                                                        }

                                                        if (in_array($teksOpsi, $jawaban)) {
                                                            $poinDapatKompleks += $nilaiOpsi;
                                                        }
                                                    }

                                                    $bobotDapat += $poinDapatKompleks;

                                                    if ($poinDapatKompleks >= $totalPoinMaksimal && $totalPoinMaksimal > 0) {
                                                        $jawabanBenar++;
                                                    } else {
                                                        $jawabanSalah++;
                                                    }
                                                } else {
                                                    $jawabanSalah++;
                                                }

                                                continue;
                                            } elseif ($soal->jenis_soal === 'menjodohkan') {
                                                $pilihanJawaban = is_array($soal->pilihan_jawaban)
                                                    ? $soal->pilihan_jawaban
                                                    : json_decode($soal->pilihan_jawaban ?? '[]', true);

                                                if (!empty($pilihanJawaban)) {
                                                    $totalPasangan = count($pilihanJawaban);
                                                    $jumlahBenarPasangan = 0;

                                                    foreach ($pilihanJawaban as $index => $item) {
                                                        $keyKiri = $index + 1;
                                                        $jawabanBenarSeharusnya = $item['nilai_pasangan'] ?? '';
                                                        $poinPerItem = $item['nilai'] ?? 5;
                                                        $jawabanSiswaBaris = $jawaban[$keyKiri] ?? null;

                                                        if (!is_null($jawabanSiswaBaris) && trim((string)$jawabanSiswaBaris) === trim((string)$jawabanBenarSeharusnya)) {
                                                            $jumlahBenarPasangan++;
                                                            $bobotDapat += $poinPerItem;
                                                        }
                                                    }

                                                    if ($jumlahBenarPasangan === $totalPasangan) {
                                                        $jawabanBenar++;
                                                    } else {
                                                        $jawabanSalah++;
                                                    }
                                                } else {
                                                    $jawabanSalah++;
                                                }

                                                continue;
                                            }
                                        } else {
                                            $isBenar = ($jawaban === $soal->kunci_jawaban);
                                        }

                                        if ($isBenar) {
                                            $jawabanBenar++;
                                            $bobotDapat += $bobotSoal;
                                        } else {
                                            $jawabanSalah++;
                                        }
                                    }

                                    $nilaiAkhir = $bobotDapat;
                                    $namaKelas = $user->kelase->name ?? $user->kelas->name ?? $user->kelas->nama_kelas ?? '-';

                                    // Simpan Nilai ke RekapNilai
                                    RekapNilai::updateOrCreate(
                                        [
                                            'ujian_id' => $ujian->id,
                                            'user_id'  => $user->id,
                                        ],
                                        [
                                            'tahun_ajaran_id' => $tahunAjaranId,
                                            'nama_mapel'      => $ujian->mapel->name ?? '-',
                                            'nama_siswa'      => $user->name,
                                            'nama_kelas'      => $namaKelas,
                                            'tahun_ajaran'    => $tahunAjaranText,
                                            'total_soal'      => $totalSoal,
                                            'jawaban_benar'   => $jawabanBenar,
                                            'jawaban_salah'   => $jawabanSalah,
                                            'nilai_akhir'     => round($nilaiAkhir, 2),
                                        ]
                                    );

                                    // Tandai data UjianSiswa ini sudah direkap
                                    $ujianSiswa->update(['is_rekaped' => true]);
                                    $totalBerhasil++;
                                });
                            }
                        });

                    if ($totalBerhasil > 0) {
                        Notification::make()
                            ->title('Rekap Nilai Berhasil')
                            ->body("Berhasil merekap {$totalBerhasil} data ujian siswa.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Tidak Ada Data Baru')
                            ->body('Semua data ujian yang selesai sudah direkap sebelumnya.')
                            ->warning()
                            ->send();
                    }
                }),

        ];
    }
}
