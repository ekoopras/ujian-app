<?php

namespace App\Filament\Resources\Ujian;

use App\Filament\Resources\Ujian\UjianSiswaResource\Pages;
use App\Filament\Resources\Ujian\UjianSiswaResource\RelationManagers;
use App\Models\UjianSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UjianSiswaResource extends Resource
{
    protected static ?string $model = UjianSiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Manajemen Ujian';
    protected static ?string $pluralModelLabel = 'Sesi Ujian Siswa';

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
                Tables\Columns\TextColumn::make('user.nomor_absen')
                    ->label('Absen')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.kelase.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ujian.mapel.name')
                    ->label('Mapel')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('waktu_mulai')
                //     ->label('Mulai')
                //     ->dateTime('d M Y H:i')
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('waktu_selesai_seharusnya')
                //     ->label('Batas Waktu')
                //     ->dateTime('H:i:s')
                //     ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_pelanggaran')
                    ->label('Pelanggaran')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_submit')
                    ->label('Submit')
                    ->dateTime('H:i:s')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'proses' => 'warning',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                Tables\Columns\IconColumn::make('is_rekaped')
                    ->label('Rekap Nilai')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success'),
            ])
            ->defaultPaginationPageOption(30) // Set default awal ke 10 data
            ->paginationPageOptions([30])
            ->filters([
                // 1. Filter Kelas (via relasi user.kelase)
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(
                        fn() => \App\Models\Kelase::pluck('name', 'id')->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $q, $value) => $q->whereHas(
                                'user',
                                fn(Builder $uq) => $uq->where('kelase_id', $value)
                            )
                        );
                    })
                    ->searchable(),

                // 2. Filter Mata Pelajaran (langsung via mapel_id di UjianSiswa)
                SelectFilter::make('mapel_id')
                    ->label('Mata Pelajaran')
                    ->options(
                        fn() => \App\Models\Mapel::pluck('name', 'id')->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $q, $value) => $q->whereHas(
                                'ujian',
                                fn(Builder $uq) => $uq->where('mapel_id', $value)
                            )
                        );
                    })
                    ->searchable(),

                // 3. Filter Nomor Absen (via relasi user)
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
                            fn(Builder $q, $value) => $q->whereHas(
                                'user',
                                fn(Builder $uq) => $uq->where('nomor_absen', $value)
                            )
                        );
                    })
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Ujian Siswa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUjianSiswas::route('/'),
            'create' => Pages\CreateUjianSiswa::route('/create'),
            'edit' => Pages\EditUjianSiswa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->role === 'guru') {
            // Ambil ID mapel yang diampu oleh guru yang sedang login
            $mapelIds = $user->mapel()->pluck('mapels.id'); // sesuaikan 'mapels.id' atau 'id'

            // Filter tabel utama berdasarkan hubungan ke ujian -> mapel
            return $query->whereHas('ujian', function (Builder $query) use ($mapelIds) {
                $query->whereIn('mapel_id', $mapelIds); // sesuaikan 'mapel_id' di tabel ujians
            });
        }

        return $query;
    }
}
