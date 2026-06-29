<?php

namespace App\Filament\App\Pages;

use App\Models\SesiUjian;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class DaftarUjian extends Page implements HasTable
{

    use InteractsWithTable;

    protected static string $view = 'filament.app.pages.daftar-ujian';
    protected static ?string $title = 'Daftar Pelaksanaan Ujian';
    protected static ?string $navigationLabel = 'Daftar Ujian';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $slug = 'daftar-ujian';

    public function table(Table $table): Table
    {
        $siswa = Auth::user();

        return $table
            // 1. Query data berdasarkan kelas siswa yang login
            ->query(
                SesiUjian::where('is_active', true)
                    ->whereHas('kelase', function (Builder $query) use ($siswa) {
                        $query->where('kelases.id', $siswa->kelas_id);
                    })
            )
            // 2. Definisikan Kolom-Kolom Tabel yang Responsif
            ->columns([
                TextColumn::make('mapel.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('success')
                    ->searchable(),

                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->suffix(' Menit')
                    ->alignCenter(),

                // Kolom Input Token interaktif bawaan Filament
                TextInputColumn::make('token_input')
                    ->label('Masukkan Token')
                    ->placeholder('KODE TOKEN')
                    ->type('text')
                    ->alignCenter()
                    // Mencegah simpan otomatis ke database karena ini hanya untuk validasi sementara
                    ->disabled(false),
            ])
            // 3. Tombol Aksi Kerjakan di Paling Kanan
            ->actions([
                Action::make('kerjakan')
                    ->label('Kerjakan')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-m-play')
                    // Fungsi validasi saat tombol diklik
                    ->action(function (SesiUjian $record, array $data, $inputs) {
                        // Mengambil nilai input token dari baris tersebut
                        // Karena menggunakan TextInputColumn, nilainya tersimpan di state inputs berdasarkan key record
                        $inputSiswa = $inputs['token_input'] ?? '';

                        if (empty($inputSiswa)) {
                            Notification::make()->title('Token wajib diisi!')->danger()->send();
                            return;
                        }

                        if (strtoupper($inputSiswa) !== strtoupper($record->token)) {
                            Notification::make()
                                ->title('Token Salah!')
                                ->body('Token yang Anda masukkan tidak valid.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Jika sukses, simpan sesi dan pindah ke halaman soal
                        session(['active_sesi_ujian_id' => $record->id]);
                        return redirect()->to('/app/ruang-ujian');
                    })
            ])
            ->emptyStateHeading('Belum Ada Ujian Aktif')
            ->emptyStateDescription('Saat ini tidak ada pelaksanaan ujian untuk kelas Anda.');
    }
}
