<?php

namespace App\Filament\Resources\Ujian;

use App\Filament\Resources\Ujian\RekapNilaiResource\Pages;
use App\Filament\Resources\Ujian\RekapNilaiResource\RelationManagers;
use App\Models\RekapNilai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RekapNilaiResource extends Resource
{
    protected static ?string $model = RekapNilai::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Manajemen Ujian';
    protected static ?string $pluralModelLabel = 'Rekap Nilai';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.nomor_absen')
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->label('Absen'),

                TextColumn::make('user.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),

                // TextColumn::make('total_soal')
                //     ->label('Total Soal')
                //     ->alignCenter(),

                // TextColumn::make('jawaban_benar')
                //     ->label('Benar')
                //     ->badge()
                //     ->color('success')
                //     ->alignCenter(),

                // TextColumn::make('jawaban_salah')
                //     ->label('Salah')
                //     ->badge()
                //     ->color('danger')
                //     ->alignCenter(),

                TextColumn::make('nilai_akhir')
                    ->label('Nilai Akhir')
                    ->badge()
                    ->color(fn($state) => $state >= 75 ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(30) // Set default awal ke 10 data
            ->paginationPageOptions([30])
            ->filters([
                // 1. Filter Kelas (mengambil nilai unik dari kolom nama_kelas)
                SelectFilter::make('nama_kelas')
                    ->label('Kelas')
                    ->options(
                        fn() => \App\Models\RekapNilai::query()
                            ->whereNotNull('nama_kelas')
                            ->distinct()
                            ->pluck('nama_kelas', 'nama_kelas')
                            ->toArray()
                    )
                    ->searchable(),

                // 2. Filter Mata Pelajaran (mengambil nilai unik dari kolom nama_mapel)
                SelectFilter::make('nama_mapel')
                    ->label('Mata Pelajaran')
                    ->options(
                        fn() => \App\Models\RekapNilai::query()
                            ->whereNotNull('nama_mapel')
                            ->distinct()
                            ->pluck('nama_mapel', 'nama_mapel')
                            ->toArray()
                    )
                    ->searchable(),

                // 3. Filter Nomor Absen (melalui relasi user)
                SelectFilter::make('nomor_absen')
                    ->label('Nomor Absen')
                    ->options(
                        fn() => \App\Models\User::query()
                            ->whereNotNull('nomor_absen')
                            ->distinct()
                            ->orderBy('nomor_absen')
                            ->pluck('nomor_absen', 'nomor_absen')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value) => $query->whereHas(
                                'user',
                                fn(Builder $query) => $query->where('nomor_absen', $value)
                            )
                        );
                    })
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
        ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekapNilais::route('/'),
            'create' => Pages\CreateRekapNilai::route('/create'),
            'edit' => Pages\EditRekapNilai::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->role === 'guru') {
            // Ganti 'mapels.nama_mapel' dengan nama kolom yang benar (contoh: 'mapels.nama')
            $namaMapels = $user->mapel()->pluck('mapels.name');

            return $query->whereIn('nama_mapel', $namaMapels);
        }

        return $query;
    }
}
