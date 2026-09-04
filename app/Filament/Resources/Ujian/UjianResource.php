<?php

namespace App\Filament\Resources\Ujian;

use App\Filament\Resources\Ujian\UjianResource\Pages;
use App\Filament\Resources\Ujian\UjianResource\RelationManagers;
use App\Models\Ujian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;


class UjianResource extends Resource
{
    protected static ?string $model = Ujian::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Manajemen Ujian';
    protected static ?string $pluralModelLabel = 'Sesi Ujian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama Ujian')
                    ->schema([
                        Forms\Components\Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->relationship('tahunAjaran', 'tahun')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->tahun} - Semester {$record->semester}")
                            ->default(fn() => \App\Models\TahunAjaran::where('is_active', true)->first()?->id)
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Forms\Components\Select::make('mapel_id')
                        //     ->label('Mata Pelajaran')
                        //     ->relationship('mapel', 'name')
                        //     ->searchable()
                        //     ->preload()
                        //     ->live()
                        //     ->required(),

                        Forms\Components\Select::make('mapel_id')
                            ->label('Mata Pelajaran')
                            ->options(function () {
                                $user = auth()->user();

                                // Jika Guru, ambil mapel yang terhubung (Format: [id => name])
                                if ($user?->role === 'guru') {
                                    return $user->mapel()->pluck('mapels.name', 'mapels.id')->toArray();
                                }

                                // Jika Super Admin / Admin / Pengawas, tampilkan semua mapel
                                return \App\Models\Mapel::pluck('name', 'id')->toArray();
                            })
                            ->placeholder('Pilih Mata Pelajaran')
                            ->searchable()
                            ->live()
                            // Reset nilai bank_soal_id jika user mengganti pilihan mapel
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('bank_soal_id', null))
                            ->required(),

                        Forms\Components\Select::make('bank_soal_id')
                            ->label('Bank Soal')
                            ->options(function (Forms\Get $get) {
                                $mapelId = $get('mapel_id');

                                // Jika mapel_id belum dipilih, kosongkan pilihan
                                if (! $mapelId) {
                                    return [];
                                }

                                // Ambil Bank Soal berdasarkan mapel_id dan format labelnya: "Nama Bank Soal - Kelas X"
                                return \App\Models\BankSoal::where('mapel_id', $mapelId)
                                    ->get()
                                    ->mapWithKeys(fn($bankSoal) => [
                                        $bankSoal->id => "{$bankSoal->nama} - Kelas {$bankSoal->kelas}"
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('kelases')
                            ->label('Pilih Kelas Target')
                            ->relationship('kelases', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Durasi & Token')
                    ->schema([
                        Forms\Components\TextInput::make('durasi_menit')
                            ->label('Durasi Pengerjaan (Menit)')
                            ->numeric()
                            ->default(120)
                            ->suffix('Menit')
                            ->required(),

                        Forms\Components\TextInput::make('token')
                            ->label('Token Ujian (6 Digit)')
                            ->default(fn() => strtoupper(Str::random(6)))
                            ->maxLength(6)
                            ->required()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generateToken')
                                    ->icon('heroicon-m-arrow-path')
                                    ->action(function (Forms\Set $set) {
                                        $set('token', strtoupper(Str::random(6)));
                                    })
                            ),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Ujian')
                            ->default(true)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mapel.name')
                    ->label('Mapel')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelases.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('success')
                    ->separator(','),

                Tables\Columns\TextColumn::make('token')
                    ->label('Token')
                    ->copyable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('durasi_menit')
                    ->label('Durasi')
                    ->formatStateUsing(fn($state) => "{$state} mnt"),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->defaultPaginationPageOption(50) // Set default awal ke 10 data
            ->paginationPageOptions([50])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('')
                    ->color('success')
                    ->modalHeading('Edit Ujian'),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->label('')
                    ->color('danger')
                    ->modalHeading('Hapus Ujian'),
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
            'index' => Pages\ListUjians::route('/'),
            //'create' => Pages\CreateUjian::route('/create'),
            //'edit' => Pages\EditUjian::route('/{record}/edit'),
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
