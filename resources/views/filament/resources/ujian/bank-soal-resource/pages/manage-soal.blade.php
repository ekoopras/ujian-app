<x-filament-panels::page>
    <div class="mb-4">
        <h2 class="text-xl font-bold">Bank Soal: {{ $record->nama }}</h2>
        <p class="text-sm text-gray-500">Mata Pelajaran: {{ $record->mapel?->name }} | {{ $record->kelas }}</p>
    </div>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                Simpan Soal
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>