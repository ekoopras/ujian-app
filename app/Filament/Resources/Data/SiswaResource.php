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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Data User';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $pluralLabel = 'Data Siswa';

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
                TextColumn::make('nomor_absen')
                    ->searchable()
                    ->sortable()
                    ->label('Absen'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Siswa'),

                TextColumn::make('kelase.name') // Otomatis me-looping semua nama mapel milik guru
                    ->badge() // Ditampilkan dalam bentuk kotak badge terpisah
                    ->color('success')
                    ->label('Kelas'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('User Gmail'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Tanggal Terdaftar'),
            ])
            ->defaultPaginationPageOption(30) // Set default awal ke 10 data
            ->paginationPageOptions([30])
            ->filters([
                SelectFilter::make('kelase_id')
                    ->label('Select Kelas')
                    ->relationship('kelase', 'name') // Otomatis mengambil daftar dari model Kelase
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('')
                    ->color('success')
                    ->modalHeading('Edit Siswa'),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->label('')
                    ->color('danger')
                    ->modalHeading('Hapus Siswa'),
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
