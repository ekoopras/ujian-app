<?php

namespace App\Filament\App\Pages;

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

        // 1. Cek apakah siswa sudah memiliki sesi ujian sebelumnya
        $ujianSiswa = UjianSiswa::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->first();

        // 2. Jika ujian sudah selesai, beritahu siswa
        if ($ujianSiswa && $ujianSiswa->status === 'selesai') {
            Notification::make()
                ->title('Ujian Selesai')
                ->body('Anda sudah menyelesaikan ujian ini.')
                ->warning()
                ->send();
            return;
        }

        // 3. Jika siswa SEDANG MENGERJAKAN (Lanjutkan Ujian), LANGSUNG REDIRECT tanpa cek token
        if ($ujianSiswa && $ujianSiswa->status === 'sedang_mengerjakan') {
            return redirect()->route('filament.app.pages.soal-page', ['ujianId' => $ujian->id]);
        }

        // 4. Jika BARU PERTAMA KALI MULAI, jalankan validasi token
        $tokenSiswa = strtoupper(trim($this->tokenInputs[$ujianId] ?? ''));

        if (empty($tokenSiswa) || $tokenSiswa !== strtoupper($ujian->token)) {
            Notification::make()
                ->title('Token Salah')
                ->body('Token yang Anda masukkan tidak valid!')
                ->danger()
                ->send();
            return;
        }

        // 5. Buat record baru jika lolos verifikasi token
        UjianSiswa::create([
            'ujian_id' => $ujian->id,
            'user_id' => $user->id,
            'waktu_mulai' => now(),
            'waktu_selesai_seharusnya' => now()->addMinutes($ujian->durasi_menit),
            'status' => 'sedang_mengerjakan',
        ]);

        return redirect()->route('filament.app.pages.soal-page', ['ujianId' => $ujian->id]);
    }
}
