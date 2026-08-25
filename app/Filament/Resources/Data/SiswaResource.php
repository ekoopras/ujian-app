<?php

namespace App\Filament\Resources\Data;

use App\Filament\Resources\Data\SiswaResource\Pages;
use App\Filament\Resources\Data\SiswaResource\RelationManagers;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Siswa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('nis')
                            ->label('NIS')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Masukkan NIS (Contoh: 2024001)'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama'),

                        Forms\Components\TextInput::make('nomor_absen')
                            ->label('Nomor Absen')
                            ->numeric()
                            ->required()
                            ->placeholder('Contoh: 01'),

                        Forms\Components\Select::make('kelase_id')
                            ->label('Kelas')
                            ->relationship('kelase', 'name')
                            ->preload()
                            ->searchable(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->label('Password'),

                        Forms\Components\Hidden::make('role')
                            ->default('siswa'),

                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')
                    ->searchable()
                    ->sortable()
                    ->label('nis'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Siswa'),

                TextColumn::make('nomor_absen')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor Absen'),

                TextColumn::make('kelase.name') // Otomatis me-looping semua nama mapel milik guru
                    ->badge() // Ditampilkan dalam bentuk kotak badge terpisah
                    ->color('success')
                    ->label('Kelas'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Tanggal Terdaftar'),
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
            'index' => Pages\ListSiswas::route('/'),
            //'create' => Pages\CreateSiswa::route('/create'),
            //'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }

    //hanya admin
    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin(); // hanya super admin
    }
}
