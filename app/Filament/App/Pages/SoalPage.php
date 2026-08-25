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

        $user = Auth::user();
        $ujian = $this->getUjianProperty();
        $totalSoal = count($this->soals);
        $jawabanBenar = 0;
        $jawabanSalah = 0;
        $totalBobot = 0;
        $bobotDapat = 0;

        foreach ($this->soals as $soal) {
            $bobotSoal = $soal->bobot_nilai ?? 1;
            $totalBobot += $bobotSoal;
            $jawaban = $this->jawabanSiswa[$soal->id] ?? null;

            // Pencocokan Jawaban
            $isBenar = false;

            if (is_array($jawaban)) {
                if ($soal->jenis_soal === 'pilihan_ganda_kompleks') {
                    $pilihanJawaban = is_array($soal->pilihan_jawaban)
                        ? $soal->pilihan_jawaban
                        : json_decode($soal->pilihan_jawaban ?? '[]', true);

                    if (is_array($jawaban) && !empty($jawaban)) {
                        $poinDapatKompleks = 0;
                        $totalPoinMaksimal = 0;

                        foreach ($pilihanJawaban as $item) {
                            $teksOpsi = $item['teks'] ?? '';
                            $nilaiOpsi = (int) ($item['nilai'] ?? 0);
                            $isActive = $item['is_active'] ?? false; // Penanda opsi benar

                            // Hitung total poin maksimal yang bisa didapat jika semua opsi benar dicentang
                            if ($isActive || $nilaiOpsi > 0) {
                                $totalPoinMaksimal += $nilaiOpsi;
                            }

                            // Jika siswa mencentang opsi ini, tambahkan nilai opsinya
                            if (in_array($teksOpsi, $jawaban)) {
                                $poinDapatKompleks += $nilaiOpsi;
                            }
                        }

                        // Tambahkan poin yang berhasil didapatkan siswa ke total bobot
                        $bobotDapat += $poinDapatKompleks;

                        // Penentuan rekap jawaban benar/salah
                        // Jika poin dapat sama dengan poin maksimal, dianggap benar penuh
                        if ($poinDapatKompleks >= $totalPoinMaksimal && $totalPoinMaksimal > 0) {
                            $jawabanBenar++;
                        } else {
                            // Jika menjawab sebagian/salah, dianggap tidak sempurna (masuk statistik salah)
                            $jawabanSalah++;
                        }
                    } else {
                        // Jika tidak diisi sama sekali
                        $jawabanSalah++;
                    }

                    // PENTING: Lanjut ke soal berikutnya
                    continue;
                } elseif ($soal->jenis_soal === 'menjodohkan') {
                    $pilihanJawaban = is_array($soal->pilihan_jawaban)
                        ? $soal->pilihan_jawaban
                        : json_decode($soal->pilihan_jawaban ?? '[]', true);

                    if (is_array($jawaban) && !empty($pilihanJawaban)) {
                        $totalPasangan = count($pilihanJawaban);
                        $jumlahBenarPasangan = 0; // Ganti nama variabel agar tidak bentrok dengan $jawabanBenar utama

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
                        // Jika tidak dijawab sama sekali / kosong
                        $jawabanSalah++;
                    }

                    // PENTING: Lanjut ke soal berikutnya agar tidak turun ke pengecekan bawah
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

        // Menggunakan langsung total bobot yang didapatkan siswa
        $nilaiAkhir = $bobotDapat;

        $namaKelas = $user->kelase->name ?? $user->kelas->name ?? $user->kelas->nama_kelas ?? '-';

        // Ambil data Tahun Ajaran yang sedang aktif
        $taAktif = TahunAjaran::where('is_active', true)->first();

        // Siapkan ID
        $tahunAjaranId = $taAktif ? $taAktif->id : null;

        // Siapkan String Teks Backup
        $tahunAjaranText = $taAktif
            ? $taAktif->tahun . ' (' . ucfirst($taAktif->semester) . ')'
            : '-';


        // 1. Simpan Rekap Nilai
        RekapNilai::create([
            'ujian_id'        => $ujian->id,
            'user_id'         => $user->id,
            'tahun_ajaran_id' => $tahunAjaranId,
            'nama_mapel'      => $ujian->mapel->name ?? '-',
            'nama_siswa'      => $user->name,
            'nis'             => $user->nis ?? '-',
            'nama_kelas'      => $namaKelas,
            'tahun_ajaran'    => $tahunAjaranText,
            'total_soal'      => $totalSoal,
            'jawaban_benar'   => $jawabanBenar,
            'jawaban_salah'   => $jawabanSalah,
            'nilai_akhir'     => round($nilaiAkhir, 2),
        ]);

        // 2. Update status pengerjaan siswa
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
