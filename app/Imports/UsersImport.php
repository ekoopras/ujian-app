<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row): Model|array|null
    {
        // Abaikan baris jika email kosong
        if (empty($row['email'])) {
            return null;
        }

        // 1. Simpan/Update User
        $user = User::updateOrCreate(
            ['email' => trim($row['email'])],
            [
                'name'     => $row['name'] ?? 'Tanpa Nama',
                'role'     => $row['role'] ?? 'guru',
                'password' => Hash::make($row['password'] ?? '12345678'),
            ]
        );

        // 2. Hubungkan dengan Mapel jika kolom id_mapel diisi
        if (! empty($row['id_mapel'])) {
            $mapelIds = array_map('trim', explode(',', (string) $row['id_mapel']));
            $mapelIds = array_filter($mapelIds, 'is_numeric');

            if (method_exists($user, 'mapel')) {
                $user->mapel()->sync($mapelIds);
            }
        }

        return $user;
    }
}
