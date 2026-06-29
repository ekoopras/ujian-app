<x-filament-panels::page>
    <div class="space-y-6">

        <!-- <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-base font-bold text-gray-800 dark:text-gray-200">Peserta: {{ auth()->user()->name }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Silakan isi token pada mata pelajaran terkait lalu klik tombol Kerjakan.</p>
            </div>
            <div class="flex gap-3 text-xs bg-gray-50 dark:bg-gray-950 p-2.5 rounded-lg border border-gray-200 dark:border-gray-850">
                <div>
                    <span class="text-gray-400 block font-medium">KELAS</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ auth()->user()->kelase?->name ?? '-' }}</span>
                </div>
                <div class="border-r border-gray-200 dark:border-gray-700 mx-1"></div>
                <div>
                    <span class="text-gray-400 block font-medium">ABSEN</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ auth()->user()->nomor_absen ?? '-' }}</span>
                </div>
            </div>
        </div> -->

        <div>
            {{ $this->table }}
        </div>

    </div>
</x-filament-panels::page>