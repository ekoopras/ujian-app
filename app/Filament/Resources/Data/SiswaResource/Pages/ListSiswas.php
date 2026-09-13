<?php

namespace App\Filament\Resources\Data\SiswaResource\Pages;

use App\Filament\Resources\Data\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use Illuminate\Support\Facades\Storage;

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
                    }, 'AKUN-' . \Illuminate\Support\Str::replace(['kelas', '-', ' '], '', \Illuminate\Support\Str::upper($records->first()?->kelase?->name ?? 'SEMUA')) . '.pdf');
                }),

            Actions\Action::make('importExcel')
                ->label('Import Siswa Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('file_excel')
                        ->label('Pilih File Excel (.xlsx / .csv)')
                        ->required()
                        ->disk('local') // Tetap gunakan disk local
                        ->directory('temp-imports')
                ])
                ->action(function (array $data) {
                    // PERBAIKAN: Gunakan Storage::disk('local')->path() agar jalurnya dibaca absolut oleh MAMP/Server
                    $filePath = Storage::disk('local')->path($data['file_excel']);

                    // Eksekusi proses import data menggunakan path yang sudah valid
                    Excel::import(new SiswaImport, $filePath);

                    // Tampilkan notifikasi sukses
                    \Filament\Notifications\Notification::make()
                        ->title('Proses Import Selesai')
                        ->body('Data siswa dari Excel berhasil diproses ke database.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
