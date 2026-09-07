<x-filament-panels::page.simple>
    {{-- Teks/Header Tambahan (Opsional) --}}
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Pendaftaran Siswa Baru
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Silakan lengkapi data diri Anda di bawah ini. Password akun akan dibuatkan oleh Admin/Sekolah.
        </p>
    </div>

    {{-- Form Bawaan Filament --}}
    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page.simple>