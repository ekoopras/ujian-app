<?php

namespace App\Filament\Resources\Ujian;

use App\Filament\Resources\Ujian\BankSoalResource\Pages;
use App\Filament\Resources\Ujian\BankSoalResource\Pages\KelolaSoal;
use App\Filament\Resources\Ujian\BankSoalResource\RelationManagers;
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

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Ujian';
    protected static ?string $modelLabel = 'Bank Soal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Soal Ujian')
                    ->description('Lengkapi detail data pembuatan soal ujian di bawah ini.')
                    ->schema([
                        // 1. Input Title Soal Ujian
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Ujian')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Ujian Tengah Semester Matematika'),

                        // 2. Pilih Mapel Relation (Drop-down dinamis dari DB)
                        Forms\Components\Select::make('mapel_id')
                            ->relationship('mapel', 'name') // 'nama' adalah nama kolom di tabel mapels (ubah ke 'nama_mapel' jika perlu)
                            ->label('Mata Pelajaran')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // 3. Pilihan Kelas (Statis: Kelas 7, 8, dan 9)
                        Forms\Components\Select::make('kelas')
                            ->label('Tingkat Kelas')
                            ->options([
                                '7' => 'Kelas 7',
                                '8' => 'Kelas 8',
                                '9' => 'Kelas 9',
                            ])
                            ->required()
                            ->native(false), // Menonaktifkan select bawaan browser agar menggunakan style UI Filament
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Ujian')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mapel.name') // Mengambil relasi nama Mapel
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas')
                    ->label('Kelas')
                    ->badge() // Menampilkan kelas dalam bentuk badge agar menarik
                    ->color(fn(string $state): string => match ($state) {
                        '7' => 'info',
                        '8' => 'warning',
                        '9' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('addSoal')
                    ->label('Add Soal')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(fn(BankSoal $record): string => static::getUrl('kelola-soal', ['record' => $record])),

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
            //'create' => Pages\CreateBankSoal::route('/create'),
            'edit' => Pages\EditBankSoal::route('/{record}/edit'),
            'kelola-soal' => KelolaSoal::route('/{record}/kelola-soal'),

        ];
    }
}
