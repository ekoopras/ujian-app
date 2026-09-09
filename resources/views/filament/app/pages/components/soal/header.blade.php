<div class="flex items-center justify-between p-3 sm:p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm mb-4 sm:mb-6 border border-gray-100 dark:border-gray-700">

    <!-- Sisi Kiri: Informasi Nomor Soal Aktif -->
    <div class="flex items-center gap-2">
        <div>
            <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider block leading-none mb-0.5">
                Soal No
            </span>
            <div class="flex items-baseline gap-1 leading-none">
                <span class="text-xl sm:text-2xl font-extrabold text-primary-600" x-text="currentIndex + 1">1</span>
                <span class="text-xs text-gray-400 font-medium" x-text="'/ ' + soals.length"></span>
            </div>
        </div>
    </div>

    <!-- Sisi Kanan: Modal Navigasi Soal & Countdown Timer -->
    <div class="flex items-center gap-2 sm:gap-3">

        <!-- Trigger Modal Nomor Soal -->
        <x-filament::modal id="modal-navigasi-soal" width="lg">
            <x-slot name="trigger">
                <x-filament::button icon="heroicon-o-squares-2x2" color="gray" size="md" class="sm:hidden">
                    Soal
                </x-filament::button>
                <x-filament::button icon="heroicon-o-squares-2x2" color="gray" size="sm" class="hidden sm:inline-flex">
                    Nomor Soal
                </x-filament::button>
            </x-slot>

            <x-slot name="heading">
                Nomor Soal
            </x-slot>

            <!-- Grid Nomor Soal (5 kolom di HP, 8 kolom di Desktop) -->
            <div class="grid grid-cols-5 sm:grid-cols-8 gap-1.5 sm:gap-2 p-1 max-h-[60vh] overflow-y-auto">
                <template x-for="(s, idx) in soals" :key="s.id">
                    <button
                        type="button"
                        @click="jumpTo(idx); $dispatch('close-modal', { id: 'modal-navigasi-soal' })"
                        class="h-9 sm:h-10 text-xs sm:text-sm font-bold rounded-lg border flex items-center justify-center transition duration-150 relative"
                        :class="getGridStyle(s.id, idx)">
                        <span x-text="idx + 1"></span>
                    </button>
                </template>
            </div>
        </x-filament::modal>

        <!-- Container Display Timer -->
        <div class="text-right border-l pl-2 sm:pl-3 dark:border-gray-700">
            <span class="text-[9px] sm:text-[10px] text-gray-400 font-semibold uppercase block leading-none mb-0.5" x-text="sisaDetik > 0 ? 'Sisa Waktu' : 'Mode'">
                Sisa Waktu
            </span>

            <template x-if="sisaDetik > 0">
                <span class="text-xs sm:text-sm font-bold text-amber-500 font-mono tracking-tight" x-text="formattedTime">
                    00:00
                </span>
            </template>

            <template x-if="sisaDetik <= 0">
                <span class="text-xs sm:text-sm font-bold text-amber-500">
                    Timer Off
                </span>
            </template>
        </div>

    </div>
</div>