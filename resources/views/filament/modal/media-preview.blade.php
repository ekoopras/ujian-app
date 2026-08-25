@php
// Mengambil container state path dari Filament secara dinamis
$stateContainerPath = $getContainer()->getStatePath();
$fullPath = $stateContainerPath ? $stateContainerPath . '.' . $targetFieldName : $targetFieldName;
@endphp

<div x-data="{ state: $wire.$entangle('{{ $fullPath }}') }" class="mt-2">
    <!-- JIKA GAMBAR TERPILIH (PREVIEW BISA TER-LOAD) -->
    <template x-if="state">
        <div class="flex items-center gap-4 p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 shadow-sm">
            <!-- Box Image Preview -->
            <div class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0 bg-gray-100 dark:bg-gray-800">
                <img :src="state.startsWith('http') ? state : '/storage/' + state"
                    class="w-full h-full object-cover"
                    alt="Preview Gambar">
            </div>

            <!-- Detail Path -->
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Preview Realtime Terpilih
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate font-mono bg-gray-50 dark:bg-gray-800 p-1.5 rounded border border-gray-100 dark:border-gray-700/50" x-text="state"></p>
            </div>

            <!-- Tombol Hapus -->
            <button type="button"
                x-on:click="state = null"
                title="Hapus pilihan"
                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </template>

    <!-- JIKA BELUM TERPILIH -->
    <template x-if="!state">
        <div class="flex items-center gap-3 p-3 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                <svg class="w-6 h-6 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <span class="text-xs text-gray-400 italic">Belum ada foto terpilih.</span>
        </div>
    </template>
</div>