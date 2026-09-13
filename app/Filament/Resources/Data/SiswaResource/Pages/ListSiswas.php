<?php

namespace App\Filament\Resources\Data\SiswaResource\Pages;

use App\Filament\Resources\Data\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->button()
                ->label('Tambah Siswa')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->modalHeading('Tambah Siswa'),

            Actions\Action::make('downloadPdfFiltered')
                ->label('Cetak PDF (Sesuai Filter)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Kuncinya di sini: Di level Page, kita bisa langsung memanggil $this->getFilteredTableQuery()
                    $records = $this->getFilteredTableQuery()->orderBy('nomor_absen', 'asc')->get();

                    // Memuat template PDF yang sudah kita buat di folder resources/views/pdf/
                    $pdf = Pdf::loadView('pdf.filament-email', [
                        'records' => $records
                    ]);

                    // Mengunduh langsung file PDF di browser pengguna
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'Daftar_Email_Siswa_' . now()->format('Ymd_His') . '.pdf');
                }),
        ];
    }
}
