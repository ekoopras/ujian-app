<?php

namespace App\Filament\Resources\Data\TahunAjaranResource\Pages;

use App\Filament\Resources\Data\TahunAjaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTahunAjarans extends ListRecords
{
    protected static string $resource = TahunAjaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->button()
                ->label('Tahun Ajaran')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->modalHeading('Tambah Tahun Ajaran'),
        ];
    }
}
