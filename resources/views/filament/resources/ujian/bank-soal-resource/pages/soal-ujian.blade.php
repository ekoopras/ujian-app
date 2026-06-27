<x-filament-panels::page>
    <div class="p-4 bg-white rounded-xl shadow-sm dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <h2 class="text-lg font-bold text-gray-800 dark:text-white">
            Kelola Soal: <span class="text-primary-600">{{ $record->title }}</span>
        </h2>
        <p class="text-sm text-gray-500">Mapel: {{ $record->mapel?->nama }} | Kelas: {{ $record->kelas }}</p>
    </div>

    <form wire:submit.prevent="simpan" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-3">
            <x-filament::button href="{{ static::$resource::getUrl('index') }}" color="gray" tag="a">
                Batal
            </x-filament::button>

            <x-filament::button type="submit" color="success">
                Simpan Semua Soal
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
