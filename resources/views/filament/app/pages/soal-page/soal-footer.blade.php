<!-- FIXED BOTTOM NAVIGATION BAR -->
<div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur border-t dark:border-gray-700 py-3 px-4 shadow-lg">
    <div class="max-w-4xl mx-auto px-0 md:px-6 lg:px-8 flex justify-between items-center gap-2 sm:gap-4">

        <!-- KIRI: Tombol Sebelumnya -->
        <x-filament::button
            wire:click="prevSoal"
            :disabled="$currentIndex === 0"
            icon="heroicon-o-chevron-left"
            color="gray">
            Prev
        </x-filament::button>

        <!-- TENGAH: Checkbox Ragu-Ragu & Info Soal -->
        <div class="flex items-center gap-3">
            @if (isset($soals[$currentIndex]))
            @php
            $soalAktifId = $soals[$currentIndex]->id;
            $isRaguAktif = !empty($raguSiswa[$soalAktifId]) || !empty($raguSiswa[(string)$soalAktifId]);
            @endphp

            <label
                wire:key="toggle-ragu-{{ $soalAktifId }}"
                class="flex items-center gap-2 px-3 py-2 rounded-xl border border-amber-300 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-950/40 cursor-pointer select-none transition hover:bg-amber-100 dark:hover:bg-amber-900/50">
                <input
                    type="checkbox"
                    wire:change="toggleRagu({{ $soalAktifId }})"
                    @checked($isRaguAktif)
                    class="rounded text-amber-500 focus:ring-amber-400 border-amber-400 dark:border-amber-600">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-300 whitespace-nowrap">
                    Ragu
                </span>
            </label>
            @endif
        </div>

        <!-- KANAN: Tombol Selanjutnya / Selesaikan Ujian -->
        @if ($currentIndex === count($soalIds) - 1)
        <x-filament::button
            wire:key="btn-finish-{{ $currentIndex }}"
            wire:click="submitUjian"
            color="success"
            icon="heroicon-o-check-circle"
            wire:confirm="Apakah Anda yakin ingin menyelesaikan ujian ini? Seluruh jawaban akan dikirimkan.">
            Submit
        </x-filament::button>
        @else
        <x-filament::button
            wire:key="btn-next-{{ $currentIndex }}"
            wire:click="nextSoal"
            icon="heroicon-o-chevron-right"
            icon-position="after">
            Next
        </x-filament::button>
        @endif

    </div>
</div>