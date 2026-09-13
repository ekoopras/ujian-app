<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Kelase;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class SiswaImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): ?Model
    {
        // 1. Cari data kelas berdasarkan slug yang ada di Excel
        $slugInput = Str::slug($row['kelas']);
        $kelas = Kelase::where('slug', $slugInput)->first();

        // Jika kelas tidak ditemukan, lewati baris ini
        if (!$kelas) {
            return null;
        }

        // 2. Cek apakah email sudah terdaftar untuk menghindari error duplikat
        $userEksis = User::where('email', $row['email'])->first();
        if ($userEksis) {
            return null;
        }

        // 3. Kembalikan data berupa objek model User
        return new User([
            'nomor_absen' => $row['nomor_absen'],
            'name'        => $row['name'],
            'kelase_id'   => $kelas->id,
            'email'       => $row['email'],
            'password'    => Hash::make($row['password']),
            'role'        => 'siswa',
        ]);
    }
}
