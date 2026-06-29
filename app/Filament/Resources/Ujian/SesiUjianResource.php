<?php

namespace App\Filament\Resources\Ujian;

use App\Filament\Resources\Ujian\SesiUjianResource\Pages;
use App\Filament\Resources\Ujian\SesiUjianResource\RelationManagers;
use App\Models\BankSoal;
use App\Models\SesiUjian;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class SesiUjianResource extends Resource
{
    protected static ?string $model = SesiUjian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Sesi Ujian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Sesi Ujian SPENSATA')
                    ->schema([
                        Grid::make(12)->schema([
                            // Baris 2: Pilih Mapel (Setengah Baris)
                            Select::make('mapel_id')
                                ->label('Mata Pelajaran')
                                ->relationship('mapel', 'name') // Sesuaikan 'nama_mapel' dengan kolom di tabel mapel Anda
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn(Set $set) => $set('bank_soal_id', null))
                                ->columnSpan(6),

                            // Baris 2: Pilih Bank Soal (Setengah Baris) - Menampilkan Title | Mapel | Kelas
                            Select::make('bank_soal_id')
                                ->label('Bank Soal')
                                ->options(function (Get $get) {
                                    $mapelId = $get('mapel_id');
                                    if (! $mapelId) {
                                        return [];
                                    }

                                    return BankSoal::with(['mapel'])
                                        ->where('mapel_id', $mapelId)
                                        ->get()
                                        ->mapWithKeys(function ($bankSoal) {
                                            $tampilanLabel = "{$bankSoal->title}";

                                            return [$bankSoal->id => $tampilanLabel];
                                        });
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disabled(fn(Get $get) => ! $get('mapel_id'))
                                ->columnSpan(6),

                            // Baris 3: Pilih Kelas Multiple (Menggunakan Relasi Pivot 'kelase')
                            Select::make('kelase')
                                ->label('Kelas Peserta')
                                ->relationship('kelase', 'name') // Menghubungkan ke relasi 'kelase' dan mengambil kolom 'name' di tabel kelase
                                ->multiple()
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(12),

                            // Baris 4: Durasi (Sepertiga Baris)
                            TextInput::make('durasi')
                                ->label('Durasi Ujian')
                                ->numeric()
                                ->default(120)
                                ->suffix('Menit')
                                ->required()
                                ->columnSpan(4),

                            // Baris 4: Token (Sepertiga Baris)
                            TextInput::make('token')
                                ->label('Token Ujian')
                                ->default(fn() => strtoupper(Str::random(6)))
                                ->required()
                                ->maxLength(6)
                                ->extraAttributes(['class' => 'font-mono tracking-widest text-lg text-emerald-600 font-bold'])
                                ->hintAction(
                                    \Filament\Forms\Components\Actions\Action::make('generateToken')
                                        ->label('Acak Ulang')
                                        ->icon('heroicon-m-arrow-path')
                                        ->action(fn(Set $set) => $set('token', strtoupper(Str::random(6))))
                                )
                                ->columnSpan(4),

                            // Baris 4: Status Aktif (Sepertiga Baris)
                            Toggle::make('is_active')
                                ->label('Sesi Aktif')
                                ->default(true)
                                ->inline(false)
                                ->columnSpan(4),

                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mapel.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bankSoal.title')
                    ->label('Bank Soal')
                    ->searchable(),

                // Menampilkan nama-nama kelas dari tabel pivot menjadi kumpulan badge rapi
                TextColumn::make('kelase.name')
                    ->label('Kelas Peserta')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->suffix(' Menit')
                    ->alignCenter(),

                TextColumn::make('token')
                    ->label('Token')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Token berhasil disalin!')
                    ->extraAttributes(['class' => 'font-bold text-emerald-600'])
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSesiUjians::route('/'),
            'create' => Pages\CreateSesiUjian::route('/create'),
            'edit' => Pages\EditSesiUjian::route('/{record}/edit'),
        ];
    }
}
