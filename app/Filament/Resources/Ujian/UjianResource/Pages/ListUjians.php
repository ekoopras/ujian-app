<?php

namespace App\Filament\Resources\Ujian\UjianResource\Pages;

use App\Filament\Resources\Ujian\UjianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUjians extends ListRecords
{
    protected static string $resource = UjianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
