<div class="fixed bottom-0 left-0 right-0 z-30 p-4 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-700 shadow-lg">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">

        <!-- Tombol Soal Sebelumnya -->
        <button
            type="button"
            @click="prevSoal()"
            :disabled="currentIndex === 0"
            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold text-xs sm:text-sm disabled:opacity-40 transition-colors">
            Sebelumnya
        </button>

        <!-- Toggle Status Ragu-Ragu -->
        <label class="flex items-center gap-2 cursor-pointer select-none px-3 py-2.5 bg-amber-50 border border-amber-200 hover:bg-amber-100 rounded-xl dark:bg-amber-950/60 dark:border-amber-800 transition-colors">
            <input
                type="checkbox"
                :checked="isRagu(currentSoal ? currentSoal.id : null)"
                @change="toggleRagu(currentSoal ? currentSoal.id : null)"
                class="rounded text-amber-500 focus:ring-amber-400 h-4 w-4">
            <span class="text-xs font-bold text-amber-700 dark:text-amber-300">Ragu-Ragu</span>
        </label>

        <!-- Tombol Soal Selanjutnya -->
        <button
            type="button"
            x-show="currentIndex < soals.length - 1"
            @click="nextSoal()"
            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl font-semibold text-xs sm:text-sm transition-colors shadow-sm">
            Selanjutnya
        </button>

        <!-- Tombol Submit Ujian (Hanya Soal Terakhir) -->
        <button
            type="button"
            x-show="currentIndex === soals.length - 1"
            @click="confirmSubmit()"
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-xl font-semibold text-xs sm:text-sm transition-colors shadow-sm">
            Submit Ujian
        </button>

    </div>
</div>

<!-- TAMBAHAN: MODAL KONFIRMASI SUBMIT UJIAN (Bebas dari Event Blur Browser) -->
<template x-if="showSubmitModal">
    <div class="fixed inset-0 z-[95] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white p-6 rounded-2xl shadow-xl text-center max-w-md w-full border border-slate-200 dark:border-slate-700 animate-in fade-in zoom-in duration-150">
            <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-500" />
            </div>

            <h4 class="text-lg font-bold">Kumpulkan Jawaban Ujian?</h4>
            <p class="text-sm mt-1 text-slate-600 dark:text-slate-300">
                Apakah kamu yakin ingin menyelesaikan dan mengumpulkan ujian ini? Jawaban tidak dapat diubah setelah dikumpulkan.
            </p>

            <div class="flex gap-3 mt-6">
                <button
                    type="button"
                    @click="showSubmitModal = false"
                    class="flex-1 py-2.5 px-4 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-sm transition-all">
                    Batal
                </button>
                <button
                    type="button"
                    @click="submitFinal()"
                    class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all shadow-md active:scale-95">
                    Ya, Kumpulkan
                </button>
            </div>
        </div>
    </div>
</template>