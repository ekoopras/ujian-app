<?php

namespace App\Filament\Resources\Ujian;

use App\Filament\Resources\Ujian\BankSoalResource\Pages;
use App\Filament\Resources\Ujian\BankSoalResource\RelationManagers;
use App\Models\TahunAjaran;
use App\Models\BankSoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankSoalResource extends Resource
{
    protected static ?string $model = BankSoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Manajemen Ujian';
    protected static ?string $pluralModelLabel = 'Bank Soal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bank Soal')
                    ->schema([

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Bank Soal / Ujian')
                            ->placeholder('Contoh: PTS Semester Ganjil IPA')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('kelas')
                            ->options([
                                'Kelas 7' => 'Kelas 7',
                                'Kelas 8' => 'Kelas 8',
                                'Kelas 9' => 'Kelas 9',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('mapel_id')
                            ->label('Mata Pelajaran')
                            ->options(function () {
                                $user = auth()->user();

                                // Jika Guru, ambil mapel yang terhubung lewat relasi mapel()
                                if ($user?->role === 'guru') {
                                    return $user->mapel()->pluck('mapels.name', 'mapels.id')->toArray();
                                }

                                // Jika Super Admin / Admin / Pengawas, tampilkan semua mapel
                                return \App\Models\Mapel::pluck('name', 'id')->toArray();
                            })
                            ->placeholder('Pilih Mata Pelajaran')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(
                                TahunAjaran::all()->mapWithKeys(function ($ta) {
                                    return [$ta->id => "{$ta->tahun} - Semester {$ta->semester}" . ($ta->is_active ? ' (Aktif)' : '')];
                                })
                            )
                            // Otomatis memilih Tahun Ajaran yang aktif
                            ->default(fn() => TahunAjaran::where('is_active', true)->first()?->id)
                            ->required()
                            ->native(false),

                        // Simpan ID Pembuat Soal secara tersembunyi
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => auth()->id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('mapel.name')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tahunAjaran.tahun')
                    ->label('Tahun Ajaran')
                    ->formatStateUsing(fn($record) => "{$record->tahunAjaran?->tahun} ({$record->tahunAjaran?->semester})"),
            ])
            ->defaultPaginationPageOption(10) // Set default awal ke 10 data
            ->paginationPageOptions([10])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('manage_soal')
                    ->label('Kelola Soal')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->button()
                    ->url(fn(BankSoal $record): string => static::getUrl('manage-soal', ['record' => $record])),

                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('')
                    ->color('success')
                    ->modalHeading('Edit Bank Soal'),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->label('')
                    ->color('danger')
                    ->modalHeading('Hapus Bank Soal'),
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
            'index' => Pages\ListBankSoals::route('/'),
            'manage-soal' => Pages\ManageSoal::route('/{record}/soal'),
            'view-soal'   => Pages\ViewSoal::route('/{record}/view-soal'),
            //'create' => Pages\CreateBankSoal::route('/create'),
            //'edit' => Pages\EditBankSoal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->role === 'guru') {
            // Ambil semua ID mapel milik guru tersebut dari relasi mapel()
            $mapelIds = $user->mapel()->pluck('mapels.id');

            return $query->whereIn('mapel_id', $mapelIds);
        }

        return $query;
    }
}
