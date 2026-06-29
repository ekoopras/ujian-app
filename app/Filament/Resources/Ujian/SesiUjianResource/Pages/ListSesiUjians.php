<?php

namespace App\Filament\Resources\Ujian\SesiUjianResource\Pages;

use App\Filament\Resources\Ujian\SesiUjianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSesiUjians extends ListRecords
{
    protected static string $resource = SesiUjianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
