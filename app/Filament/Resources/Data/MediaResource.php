<?php

namespace App\Filament\Resources\Data;

use App\Filament\Resources\Data\MediaResource\Pages;
use App\Filament\Resources\Data\MediaResource\RelationManagers;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralLabel = 'File Media';

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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengunggah')
                    ->searchable()
                    ->sortable(),

                // 2. Nama File asli
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Pengguna bisa klik untuk menyalin nama file
                    ->copyMessage('Nama file berhasil disalin'),

                // 3. Tipe File (MIME Type) dengan Badge
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                // 4. Ukuran File (Diformat otomatis ke KB/MB)
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn($state) => match (true) {
                        $state >= 1048576 => number_format($state / 1048576, 2) . ' MB',
                        $state >= 1024 => number_format($state / 1024, 2) . ' KB',
                        default => $state . ' B',
                    })
                    ->sortable(),

                // 5. Preview / Link Download dari Path
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Berkas')
                    ->formatStateUsing(fn() => 'Lihat / Download')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn($record) => asset('storage/' . $record->file_path), shouldOpenInNewTab: true),

                // 6. Hash File (Bisa disembunyikan secara default/toggleable)
                Tables\Columns\TextColumn::make('file_hash')
                    ->label('Hash SHA-256')
                    ->limit(10) // Hanya tampilkan 10 karakter awal
                    ->tooltip(fn($record) => $record->file_hash) // Tampilkan hash utuh saat hover
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true), // Menyembunyikan kolom kecuali dibuka via toggle

                // Timestamp
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(10) // Set default awal ke 10 data
            ->paginationPageOptions([10])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMedia::route('/'),
            //'create' => Pages\CreateMedia::route('/create'),
            //'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Jika user BUKAN admin, filter hanya media milik user tersebut
        if ($user?->role !== 'super_admin') {
            return $query->where('user_id', $user->id);
        }

        // Jika user adalah 'admin', tampilkan semua data
        return $query;
    }
}
