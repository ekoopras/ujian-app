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
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama'),

                        TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Username'),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->label('Password'),

                        Select::make('kelas_id')
                            ->relationship('kelas', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Kelas'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->label('Username'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Siswa'),

                TextColumn::make('kelas.name') // Otomatis me-looping semua nama mapel milik guru
                    ->badge() // Ditampilkan dalam bentuk kotak badge terpisah
                    ->color('success')
                    ->label('kelas'),

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
}
