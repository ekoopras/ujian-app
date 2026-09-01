<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <!-- Grid 1: Bank Soal & Detail Kelas/Mapel -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Bank Soal
                </span>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ $record->nama }}
                </h2>
            </div>
            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mt-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                Mata Pelajaran: {{ $record->mapel?->name ?? '-' }} | {{ $record->kelas ?? '-' }}
            </p>
        </div>

        <!-- Grid 2: Jumlah Soal -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Jumlah Soal
                </span>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">
                    {{ $record->total_soal }}
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Total butir soal terdaftar
            </p>
        </div>

        <!-- Grid 3: Total Nilai Keseluruhan -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Total Nilai Max
                </span>
                <div class="text-3xl font-extrabold text-primary-600 dark:text-primary-400 mt-2">
                    {{ $record->total_nilai }}
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Akumulasi nilai kunci jawaban
            </p>
        </div>
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