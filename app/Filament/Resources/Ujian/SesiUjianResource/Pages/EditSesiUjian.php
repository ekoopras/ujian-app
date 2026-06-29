<?php

namespace App\Filament\Resources\Ujian\SesiUjianResource\Pages;

use App\Filament\Resources\Ujian\SesiUjianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSesiUjian extends EditRecord
{
    protected static string $resource = SesiUjianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
