<?php

namespace App\Filament\App\Pages;

use App\Models\RekapNilai;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\Ujian;
use App\Models\UjianSiswa;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SoalPage extends Page
{
    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;

    // Menghapus/menyembunyikan heading halaman Filament
    protected ?string $heading = '';

    protected static string $view = 'filament.app.pages.soal-page';

    public $ujianId;
    public $ujianSiswa;
    public array $soalIds = [];
    public $soals = [];
    public $currentIndex = 0;
    public $jawabanSiswa = [];
    public array $raguSiswa = [];

    // Helper getter agar data $ujian selalu bisa diakses aman di Livewire & Blade ($this->ujian)
    public function getUjianProperty()
    {
        return Ujian::with(['mapel', 'bankSoal'])->findOrFail($this->ujianId);
    }

    public function mount()
    {
        $this->ujianId = request()->query('ujianId');
        $user = Auth::user();

        $this->ujianSiswa = UjianSiswa::where('ujian_id', $this->ujianId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($this->ujianSiswa->status === 'selesai') {
            return redirect()->route('filament.app.pages.ujian-page');
        }

        $ujian = $this->getUjianProperty();

        // Ambil semua soal terkait bank soal ujian ini
        $this->soals = Soal::where('bank_soal_id', $ujian->bank_soal_id)->get();
        $this->soalIds = $this->soals->pluck('id')->toArray();
        $this->jawabanSiswa = $this->ujianSiswa->jawaban_siswa ?? [];

        // Pastikan ragu_siswa selalu ter-decode sebagai array
        $rawRagu = $this->ujianSiswa->ragu_siswa;
        $this->raguSiswa = is_array($rawRagu) ? $rawRagu : (json_decode($rawRagu ?? '[]', true) ?? []);
    }

    //STORE JAWABAN PILIHAN GANDA DAN BENAR SALAH
    public function simpanJawaban($soalId, $pilihan)
    {
        $this->jawabanSiswa[$soalId] = $pilihan;
        $this->ujianSiswa->update([
            'jawaban_siswa' => $this->jawabanSiswa,
        ]);
    }

    //STORE JAWABAN PILIHAN GANDA KOMPLEKS
    public function simpanJawabanKompleks($soalId, $pilihan)
    {
        $current = $this->jawabanSiswa[$soalId] ?? [];
        if (!is_array($current)) {
            $current = array_filter(explode('; ', $current));
        }

        if (in_array($pilihan, $current)) {
            $current = array_diff($current, [$pilihan]);
        } else {
            $current[] = $pilihan;
        }

        $this->jawabanSiswa[$soalId] = array_values($current);
        $this->ujianSiswa->update([
            'jawaban_siswa' => $this->jawabanSiswa,
        ]);
    }

    /// STORE JAWABAN MENJODOHKAN
    public function simpanJawabanMenjodohkan($soalId, $kunciKiri, $nilaiPasangan)
    {
        // 1. Pastikan array terinisialisasi untuk kunci soal ini
        if (!isset($this->jawabanSiswa[$soalId]) || !is_array($this->jawabanSiswa[$soalId])) {
            $this->jawabanSiswa[$soalId] = [];
        }

        // 2. Set atau Hapus nilai pasangan
        if (empty($nilaiPasangan)) {
            unset($this->jawabanSiswa[$soalId][$kunciKiri]);
        } else {
            $this->jawabanSiswa[$soalId][$kunciKiri] = $nilaiPasangan;
        }

        // 3. Update ke Database
        $this->ujianSiswa->jawaban_siswa = $this->jawabanSiswa;
        $this->ujianSiswa->save();
    }

    // Method Navigasi dengan Type Hint Integer
    public function setSoalIndex(int $index): void
    {
        $totalSoal = count($this->soalIds);

        // Bounding check: cegah index di luar jangkauan (misal < 0 atau >= totalSoal)
        if ($index < 0) {
            $this->currentIndex = 0;
        } elseif ($index >= $totalSoal) {
            $this->currentIndex = max(0, $totalSoal - 1);
        } else {
            $this->currentIndex = $index;
        }
    }

    // Helper method untuk navigasi tombol bawah
    public function nextSoal(): void
    {
        $this->setSoalIndex($this->currentIndex + 1);
    }

    public function prevSoal(): void
    {
        $this->setSoalIndex($this->currentIndex - 1);
    }

    public function toggleRagu($soalId)
    {
        // Gunakan string key untuk konsistensi di JSON array
        $key = (string) $soalId;

        $currentStatus = $this->raguSiswa[$key] ?? false;
        $this->raguSiswa[$key] = !$currentStatus;

        // Simpan ke database
        $this->ujianSiswa->ragu_siswa = $this->raguSiswa;
        $this->ujianSiswa->save();
    }

    public function submitUjian()
    {
        if ($this->ujianSiswa->status === 'selesai') {
            return redirect()->route('filament.app.pages.ujian-page');
        }

        // Update status pengerjaan siswa
        $this->ujianSiswa->update([
            'waktu_submit' => now(),
            'status'       => 'selesai',
        ]);

        Notification::make()
            ->title('Ujian Berhasil Disubmit')
            ->success()
            ->send();

        return redirect()->route('filament.app.pages.ujian-page');
    }
}
