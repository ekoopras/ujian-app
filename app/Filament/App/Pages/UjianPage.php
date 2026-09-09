<?php

namespace App\Filament\App\Pages;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianSiswa;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class UjianPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $title = 'Daftar Ujian Siswa';

    protected ?string $heading = '';

    protected static string $view = 'filament.app.pages.ujian-page';

    // Beri default array kosong
    public array $tokenInputs = [];

    // Mengirim data ujian ke Blade secara aman tanpa bentrok dengan method Filament
    protected function getViewData(): array
    {
        $user = Auth::user();

        $ujians = Ujian::with(['mapel', 'bankSoal'])
            ->where('is_active', true)
            ->whereHas('kelases', fn($q) => $q->where('kelases.id', $user->kelase_id))
            ->get();

        return [
            'ujians' => $ujians,
        ];
    }

    public function mulaiUjian(int $ujianId)
    {
        $user = Auth::user();
        $ujian = Ujian::findOrFail($ujianId);

        $ujianSiswa = UjianSiswa::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->first();

        if ($ujianSiswa && $ujianSiswa->status === 'selesai') {
            Notification::make()
                ->title('Ujian Selesai')
                ->body('Anda sudah menyelesaikan ujian ini.')
                ->warning()
                ->send();
            return;
        }

        if ($ujianSiswa && $ujianSiswa->status === 'sedang_mengerjakan') {
            return redirect()->route('filament.app.pages.soal-page', ['ujianId' => $ujian->id]);
        }

        $tokenSiswa = strtoupper(trim($this->tokenInputs[$ujianId] ?? ''));

        if (empty($tokenSiswa) || $tokenSiswa !== strtoupper($ujian->token)) {
            Notification::make()
                ->title('Token Salah')
                ->body('Token yang Anda masukkan tidak valid!')
                ->danger()
                ->send();
            return;
        }

        // Ambil seluruh ID Soal
        $allSoalIds = Soal::where('bank_soal_id', $ujian->bank_soal_id)
            ->pluck('id')
            ->toArray();

        // Logika Pengacakan 10 Variasi Paket:
        // Gunakan nilai sisa bagi ID user terhadap 10 sebagai SEED acak
        $paketSeed = ($user->id % 10);

        $urutanSoal = $allSoalIds;
        mt_srand($ujian->id + $paketSeed); // Seed tetap per kombinasi ujian & variasi
        shuffle($urutanSoal);
        mt_srand(); // Reset generator acak PHP

        UjianSiswa::create([
            'ujian_id' => $ujian->id,
            'user_id' => $user->id,
            'waktu_mulai' => now(),
            'waktu_selesai_seharusnya' => now()->addMinutes($ujian->durasi_menit),
            'status' => 'sedang_mengerjakan',
            'urutan_soal' => $urutanSoal, // Menyimpan urutan variasi paket
        ]);

        return redirect()->route('filament.app.pages.soal-page', ['ujianId' => $ujian->id]);
    }
}
