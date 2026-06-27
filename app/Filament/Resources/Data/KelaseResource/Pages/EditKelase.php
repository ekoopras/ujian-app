<?php

namespace App\Filament\Resources\Data\KelaseResource\Pages;

use App\Filament\Resources\Data\KelaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKelase extends EditRecord
{
    protected static string $resource = KelaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
