<?php

namespace App\Filament\Resources\Data;

use App\Filament\Resources\Data\KelaseResource\Pages;
use App\Filament\Resources\Data\KelaseResource\RelationManagers;
use App\Models\Kelase;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;


class KelaseResource extends Resource
{
    protected static ?string $model = Kelase::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Data Kelas';
    protected static ?string $pluralLabel = 'Data Kelas';
    // protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Kelas')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                Forms\Components\Hidden::make('slug'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Kelas'),
                TextColumn::make('slug')
                    ->label('Slug'),
            ])
            ->defaultPaginationPageOption(10) // Set default awal ke 10 data
            ->paginationPageOptions([10])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('')
                    ->color('success')
                    ->modalHeading('Edit Kelas'),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->label('')
                    ->color('danger')
                    ->modalHeading('Hapus Kelas'),
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
            'index' => Pages\ListKelases::route('/'),
            //'create' => Pages\CreateKelase::route('/create'),
            //'edit' => Pages\EditKelase::route('/{record}/edit'),
        ];
    }

    //hanya admin
    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin(); // hanya super admin
    }
}
