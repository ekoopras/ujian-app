<?php

namespace App\Filament\Resources\Data\KelaseResource\Pages;

use App\Filament\Resources\Data\KelaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelases extends ListRecords
{
    protected static string $resource = KelaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
