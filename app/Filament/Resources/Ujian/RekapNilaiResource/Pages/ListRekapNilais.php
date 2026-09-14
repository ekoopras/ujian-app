<?php

namespace App\Filament\Resources\Ujian\RekapNilaiResource\Pages;

use App\Filament\Resources\Ujian\RekapNilaiResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Rap2hpoutre\FastExcel\FastExcel;

class ListRekapNilais extends ListRecords
{
    protected static string $resource = RekapNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // 1. Ambil query yang sudah menerapkan Search & SelectFilter aktif di layar
                    // dan load relasi 'user' agar query lebih efisien (mencegah N+1 query)
                    $query = $this->getFilteredTableQuery()->with('user');

                    // 2. Ekspor dan langsung download menggunakan FastExcel
                    return (new FastExcel($query->cursor()))->download('rekap-nilai-' . now()->format('Y-m-d') . '.xlsx', function ($row) {
                        return [
                            'Absen'         => $row->user?->nomor_absen ?? '-',
                            'Siswa'         => $row->user?->name ?? '-',
                            'Kelas'         => $row->nama_kelas ?? '-',
                            'Mata Pelajaran' => $row->nama_mapel ?? '-',
                            'Tahun Ajaran'  => $row->tahun_ajaran ?? '-',
                            'Nilai Akhir'   => $row->nilai_akhir ?? 0,
                        ];
                    });
                }),
            //Actions\CreateAction::make(),
        ];
    }
}
