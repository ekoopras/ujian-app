<?php

namespace App\Filament\Resources\Data\UserResource\Pages;

use App\Filament\Resources\Data\UserResource;
use App\Imports\UsersImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importExcel')
                ->label('Import Excel')
                ->color('primary')
                ->icon('heroicon-m-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('File Excel (.xlsx)')
                        ->storeFiles(false) // Tidak perlu simpan file ke storage permanen
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        // Langsung import dari Temporary File Filament
                        Excel::import(new UsersImport, $data['attachment']->getRealPath());

                        Notification::make()
                            ->title('Berhasil!')
                            ->body('Data guru berhasil diimpor.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Import!')
                            ->body('Pesan Error: ' . $e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->button()
                ->label('Tambah User')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->modalHeading('Tambah User'),
        ];
    }
}
