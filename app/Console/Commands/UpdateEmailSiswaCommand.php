<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Kelase;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateEmailSiswaCommand extends Command
{
    // Nama perintah yang akan dipanggil di terminal beserta argumen slug-nya
    protected $signature = 'siswa:update-email {slug}';

    // Deskripsi perintah
    protected $description = 'Mengubah email siswa berdasarkan slug kelas secara aman dan dinamis';

    public function handle()
    {
        // Mengambil argumen slug yang diketik di terminal
        $targetSlug = $this->argument('slug');

        $kelas = Kelase::where('slug', $targetSlug)->first();

        if (!$kelas) {
            $this->error("❌ ERROR: Kelas dengan slug '{$targetSlug}' tidak ditemukan!");
            return Command::FAILURE;
        }

        $siswas = User::where('role', 'siswa')
            ->where('kelase_id', $kelas->id)
            ->get();

        $this->info("=== MEMULAI UPDATE VIA COMMAND ===");
        $this->info("Target Kelas : {$kelas->name} (Slug: {$targetSlug})");
        $this->info("Total Data   : " . $siswas->count() . " siswa");

        $sukses = 0;
        $gagal = 0;

        foreach ($siswas as $user) {
            $slugKelas = Str::replace(['kelas', '-'], '', Str::lower($kelas->slug));
            $formattedAbsen = str_pad($user->nomor_absen, 2, '0', STR_PAD_LEFT);
            $emailBaru = "siswa{$slugKelas}{$formattedAbsen}@spensata.com";

            try {
                $user->update(['email' => $emailBaru]);
                $sukses++;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $gagal++;
                $this->warn("⚠️  GAGAL (Duplikat): ID {$user->id} - {$user->name} (No Absen: {$user->nomor_absen})");
            }
        }

        $this->info("=== HASIL PROSES ===");
        $this->info("✅ Berhasil diupdate : {$sukses}");
        if ($gagal > 0) {
            $this->error("❌ Gagal (Duplikat)  : {$gagal}");
        }

        return Command::SUCCESS;
    }
}
