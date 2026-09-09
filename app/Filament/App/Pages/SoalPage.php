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
    public $ujianSiswaId;
    public $sisaDetik = 0;

    // Payload awal untuk di-pass ke LocalStorage
    public array $payloadSoal = [];
    public array $initialJawaban = [];
    public array $initialRagu = [];

    public function mount()
    {
        $this->ujianId = request()->query('ujianId');
        $user = Auth::user();

        $ujianSiswa = UjianSiswa::where('ujian_id', $this->ujianId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($ujianSiswa->status === 'selesai') {
            return redirect()->route('filament.app.pages.ujian-page');
        }

        $this->ujianSiswaId = $ujianSiswa->id;
        $ujian = Ujian::findOrFail($this->ujianId);

        // Hitung Sisa Waktu
        $waktuSelesai = $ujianSiswa->waktu_selesai_seharusnya ?? $ujianSiswa->waktu_mulai->addMinutes($ujian->durasi_menit);
        $this->sisaDetik = max(0, now()->diffInSeconds($waktuSelesai, false));

        if ($this->sisaDetik <= 0) {
            $this->forceSubmit();
            return;
        }

        // Ambil soal berdasarkan variasi urutan_soal yang telah ditentukan
        $urutanIds = $ujianSiswa->urutan_soal ?? [];
        if (!empty($urutanIds)) {
            $soalRaw = Soal::whereIn('id', $urutanIds)->get()->keyBy('id');
            // Urutkan collection sesuai array $urutanIds
            $soalOrdered = collect($urutanIds)->map(fn($id) => $soalRaw->get($id))->filter();
        } else {
            $soalOrdered = Soal::where('bank_soal_id', $ujian->bank_soal_id)->get();
        }

        // Susun payload ringan tanpa overhead Eloquent
        $this->payloadSoal = $soalOrdered->map(function ($item) {
            return [
                'id' => $item->id,
                'jenis_soal' => $item->jenis_soal,
                'pertanyaan' => $item->pertanyaan,
                'gambar_soal' => $item->gambar_soal,
                'pilihan_jawaban' => $item->pilihan_jawaban, // Sudah di-cast array
            ];
        })->values()->toArray();

        $this->initialJawaban = $ujianSiswa->jawaban_siswa ?? [];

        $rawRagu = $ujianSiswa->ragu_siswa;
        $this->initialRagu = is_array($rawRagu) ? $rawRagu : (json_decode($rawRagu ?? '[]', true) ?? []);
    }

    // Listener Livewire untuk Sinkronisasi Jawaban & Ragu secara background
    public function syncJawaban($jawaban = [], $ragu = [])
    {
        $jawabanData = is_array($jawaban) ? $jawaban : (array) $jawaban;
        $raguData = is_array($ragu) ? $ragu : (array) $ragu;

        $ujianSiswa = UjianSiswa::find($this->ujianSiswaId);

        if ($ujianSiswa && $ujianSiswa->status === 'sedang_mengerjakan') {
            $ujianSiswa->jawaban_siswa = $jawabanData;
            $ujianSiswa->ragu_siswa = $raguData;
            $ujianSiswa->save();
        }
    }

    // Submit Akhir Ujian
    public function submitFinal($jawaban = [], $ragu = [])
    {
        $jawabanData = is_array($jawaban) ? $jawaban : (array) $jawaban;
        $raguData = is_array($ragu) ? $ragu : (array) $ragu;

        $ujianSiswa = UjianSiswa::find($this->ujianSiswaId);

        if ($ujianSiswa && $ujianSiswa->status !== 'selesai') {
            // 1. Simpan jawaban & ragu-ragu terakhir
            $ujianSiswa->jawaban_siswa = $jawabanData;
            $ujianSiswa->ragu_siswa = $raguData;

            // 2. Set waktu submit & update status ke selesai
            $ujianSiswa->waktu_submit = now();
            $ujianSiswa->status = 'selesai';
            $ujianSiswa->save();
        }

        // 3. Redirect siswa kembali ke halaman daftar ujian
        return redirect()->route('filament.app.pages.ujian-page');
    }

    private function forceSubmit()
    {
        $ujianSiswa = UjianSiswa::find($this->ujianSiswaId);
        if ($ujianSiswa && $ujianSiswa->status !== 'selesai') {
            $ujianSiswa->update([
                'waktu_submit' => now(),
                'status' => 'selesai',
            ]);
        }
        return redirect()->route('filament.app.pages.ujian-page');
    }
}
