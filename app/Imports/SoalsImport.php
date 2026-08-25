<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Str;

class SoalsImport implements ToArray
{
    private array $data = [];

    public function array(array $array): void
    {
        foreach ($array as $index => $row) {
            // Lewati baris header pertama (Row 1)
            if ($index === 0) {
                continue;
            }

            // Kolom A (0) = No (Abaikan)
            // Kolom B (1) = jenis_soal
            // Kolom C (2) = pertanyaan
            $jenisSoal = strtolower(trim((string)($row[1] ?? '')));
            $pertanyaan = $row[2] ?? null;

            if (empty($jenisSoal) || empty($pertanyaan)) {
                continue;
            }

            // Kolom L (Index 11) = Kunci Jawaban
            $kunciRaw = (string)($row[11] ?? '');
            preg_match_all('/[a-z0-9]+/i', strtolower($kunciRaw), $matches);
            $kunciList = $matches[0] ?? [];

            $pilihanJawaban = [];

            // Mapping posisi kolom Excel baru:
            // Opsi A => Kolom D (Index 3) & E (Index 4)
            // Opsi B => Kolom F (Index 5) & G (Index 6)
            // Opsi C => Kolom H (Index 7) & I (Index 8)
            // Opsi D => Kolom J (Index 9) & K (Index 10)
            $opsiMap = [
                1 => ['teks' => 3, 'nilai' => 4],
                2 => ['teks' => 5, 'nilai' => 6],
                3 => ['teks' => 7, 'nilai' => 8],
                4 => ['teks' => 9, 'nilai' => 10],
            ];

            // 1 & 2. PILIHAN GANDA & PILIHAN GANDA KOMPLEKS
            if (in_array($jenisSoal, ['pilihan_ganda', 'pilihan_ganda_kompleks'])) {
                foreach ($opsiMap as $noOpsi => $posisi) {
                    $teksOpsi = $row[$posisi['teks']] ?? null;

                    if ($teksOpsi !== null && $teksOpsi !== '') {
                        $huruf = chr(96 + $noOpsi); // 1 = a, 2 = b, 3 = c, 4 = d

                        $isKunci = in_array((string)$noOpsi, $kunciList) || in_array($huruf, $kunciList);

                        $pilihanJawaban[(string) Str::uuid()] = [
                            'teks'      => (string) $teksOpsi,
                            'nilai'     => (int) ($row[$posisi['nilai']] ?? 0),
                            'is_active' => $isKunci,
                        ];
                    }
                }
            }
            // 3. BENAR / SALAH
            elseif ($jenisSoal === 'benar_salah') {
                foreach ($opsiMap as $noOpsi => $posisi) {
                    $teksPernyataan = $row[$posisi['teks']] ?? null;

                    if ($teksPernyataan !== null && $teksPernyataan !== '') {
                        $huruf = chr(96 + $noOpsi); // 1 = 'a', 2 = 'b', dst.

                        // Cek apakah opsi ini ditandai sebagai kunci jawaban
                        $isActive = in_array((string)$noOpsi, $kunciList) || in_array($huruf, $kunciList);

                        $pilihanJawaban[(string) Str::uuid()] = [
                            'teks'           => (string) $teksPernyataan,
                            'gambar_jawaban' => null,
                            'nilai'          => (int) ($row[$posisi['nilai']] ?? 0),
                            'is_active'      => $isActive, // Menggunakan is_active persis seperti Pilihan Ganda
                        ];
                    }
                }
            }

            $this->data[(string) Str::uuid()] = [
                'jenis_soal'      => $jenisSoal,
                'pertanyaan'      => '<p>' . e($pertanyaan) . '</p>',
                'pilihan_jawaban' => $pilihanJawaban,
            ];
        }
    }

    public function getImportedData(): array
    {
        return $this->data;
    }
}
