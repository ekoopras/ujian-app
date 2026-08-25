<?php

namespace App\Filament\Resources\Ujian\RekapNilaiResource\Pages;

use App\Filament\Resources\Ujian\RekapNilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapNilai extends EditRecord
{
    protected static string $resource = RekapNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
