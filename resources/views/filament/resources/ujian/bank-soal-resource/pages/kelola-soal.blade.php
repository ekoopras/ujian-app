<x-filament-panels::page>
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