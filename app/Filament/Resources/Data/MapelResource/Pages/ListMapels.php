<?php

namespace App\Filament\Resources\Data\MapelResource\Pages;

use App\Filament\Resources\Data\MapelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMapels extends ListRecords
{
    protected static string $resource = MapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
