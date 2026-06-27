<?php

namespace App\Filament\Resources\Data\SiswaResource\Pages;

use App\Filament\Resources\Data\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['role'] = 'siswa'; // Memastikan role otomatis tersimpan saat submit modal
                    return $data;
                }),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->where('role', 'siswa');
    }
}
