<?php

namespace App\Filament\Resources\Ujian\BankSoalResource\Pages;

use App\Filament\Resources\Ujian\BankSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankSoal extends EditRecord
{
    protected static string $resource = BankSoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
