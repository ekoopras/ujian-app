<?php

namespace App\Filament\Resources\Ujian\UjianSiswaResource\Pages;

use App\Filament\Resources\Ujian\UjianSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUjianSiswa extends EditRecord
{
    protected static string $resource = UjianSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
