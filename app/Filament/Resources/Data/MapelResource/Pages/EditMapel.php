<?php

namespace App\Filament\Resources\Data\MapelResource\Pages;

use App\Filament\Resources\Data\MapelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMapel extends EditRecord
{
    protected static string $resource = MapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
