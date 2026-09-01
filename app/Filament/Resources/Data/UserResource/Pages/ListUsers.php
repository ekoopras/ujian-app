<?php

namespace App\Filament\Resources\Data\UserResource\Pages;

use App\Filament\Resources\Data\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->button()
                ->label('Tambah User')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->modalHeading('Tambah User'),
        ];
    }
}
