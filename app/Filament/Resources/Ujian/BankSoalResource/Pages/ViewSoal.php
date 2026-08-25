<?php

namespace App\Filament\Resources\Ujian\BankSoalResource\Pages;

use App\Filament\Resources\Ujian\BankSoalResource;
use App\Models\BankSoal;
use App\Models\Soal;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;

class ViewSoal extends Page
{
    protected static string $resource = BankSoalResource::class;

    protected static string $view = 'filament.resources.ujian.bank-soal-resource.pages.view-soal';

    public int|string $recordId;
    public BankSoal $record;

    public function mount(int|string|BankSoal $record): void
    {
        $this->recordId = $record instanceof BankSoal ? $record->id : $record;
        $this->record = BankSoal::with('mapel')->findOrFail($this->recordId);
    }

    protected function getViewData(): array
    {
        return [
            'soals' => Soal::where('bank_soal_id', $this->recordId)->get(),
        ];
    }

    public function getTitle(): string
    {
        return 'View Soal: ' . ($this->record->nama ?? 'Bank Soal');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembaliInput')
                ->label('Kembali / Edit Soal')
                ->icon('heroicon-m-pencil-square')
                ->color('warning')
                ->url(fn() => BankSoalResource::getUrl('manage-soal', ['record' => $this->recordId])),
        ];
    }
}
