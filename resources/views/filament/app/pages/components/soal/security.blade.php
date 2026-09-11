<!-- Modal Ujian Terkunci (Pelanggaran >= 3) dengan Input PIN 6 Digit -->
<div id="locked-modal" wire:ignore.self class="{{ $isLocked ? '' : 'hidden' }} fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-slate-800 text-white p-8 rounded-3xl shadow-2xl text-center max-w-md w-full border border-red-500/50">
        <div class="w-16 h-16 bg-red-500/10 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-red-500/20">
            <x-heroicon-o-lock-closed class="w-10 h-10 animate-pulse" />
        </div>

        <h3 class="text-2xl font-bold text-red-500">UJIAN TERKUNCI!</h3>
        <p class="text-xs mt-2 text-slate-400">
            Kamu terdeteksi meninggalkan halaman ujian sebanyak
            <strong class="text-red-400 underline" id="text-pelanggaran-locked">{{ $jumlahPelanggaran }}x</strong>.
        </p>

        <!-- Form Input PIN Pengawas -->
        <form wire:submit.prevent="unlockWithPin" class="mt-6 space-y-4">
            <div>
                <label for="pinInput" class="block text-xs font-medium text-slate-300 mb-2">
                    Minta Guru / Pengawas Memasukkan PIN (6 Digit):
                </label>
                <input
                    type="password"
                    id="pinInput"
                    wire:model="pinInput"
                    maxlength="6"
                    placeholder="••••••"
                    autocomplete="off"
                    class="w-full text-center text-2xl tracking-[0.5em] font-mono py-3 px-4 rounded-xl bg-slate-900 border border-slate-700 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all"
                    required />
                @if($pinErrorMessage)
                <p class="text-xs text-red-400 mt-2 font-medium animate-shake">
                    {{ $pinErrorMessage }}
                </p>
                @endif
            </div>

            <button
                type="submit"
                class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-sm transition-all shadow-lg shadow-amber-500/20 active:scale-95">
                Buka Kunci Ujian
            </button>
        </form>
    </div>
</div>

<!-- Modal Peringatan Sementara (Pelanggaran 1 & 2) -->
<!-- Atribut wire:ignore.self mencegah Livewire mereset/menyembunyikan modal ini secara otomatis -->
<div id="warning-modal" wire:ignore.self class="hidden fixed inset-0 z-[90] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white p-6 rounded-2xl shadow-xl text-center max-w-md border border-amber-500 animate-in fade-in zoom-in duration-150">
        <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-amber-500 mx-auto mb-2 animate-bounce" />
        <h4 class="text-lg font-bold">Peringatan Pelanggaran!</h4>
        <p class="text-sm mt-1 text-slate-600 dark:text-slate-300">
            Kamu terdeteksi meninggalkan halaman ujian.
        </p>
        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 mt-2">
            Jumlah Pelanggaran: <span id="text-pelanggaran-count">{{ $jumlahPelanggaran }}</span> / 3
        </p>

        <!-- Tombol Satu-satunya untuk Menutup Modal -->
        <button id="btn-close-warning" type="button" class="mt-4 w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-sm transition-all shadow-md active:scale-95">
            Saya Mengerti, Lanjutkan Ujian
        </button>
    </div>
</div>

@script
<script>
    const lockedModal = document.getElementById('locked-modal');
    const warningModal = document.getElementById('warning-modal');
    const btnCloseWarning = document.getElementById('btn-close-warning');
    const textCount = document.getElementById('text-pelanggaran-count');
    const textLockedCount = document.getElementById('text-pelanggaran-locked');
    const soalWrapper = document.getElementById('soal-wrapper');

    let isLocked = @json($isLocked);
    let violationCount = parseInt(@json($jumlahPelanggaran)) || 0;

    // VARIABLE PENCEGAH DOUBLE TRIGGER & PENGUNCI MODAL
    let lastViolationTime = 0;
    let isRequesting = false;
    let isWarningOpen = false;

    function applyLockState() {
        if (soalWrapper) soalWrapper.classList.add('blur-2xl', 'pointer-events-none', 'select-none');
        if (warningModal) warningModal.classList.add('hidden');
        if (lockedModal) lockedModal.classList.remove('hidden');
        isWarningOpen = false;
    }

    function removeLockState() {
        if (soalWrapper) soalWrapper.classList.remove('blur-2xl', 'pointer-events-none', 'select-none');
        if (lockedModal) lockedModal.classList.add('hidden');
        if (warningModal) warningModal.classList.add('hidden');
        isWarningOpen = false;
    }

    function triggerViolation() {
        const now = Date.now();

        // Abaikan jika sedang terkunci, modal peringatan sedang aktif, sedang request, atau jeda < 2 detik
        if (isLocked || isWarningOpen || isRequesting || (now - lastViolationTime < 2000)) {
            return;
        }

        lastViolationTime = now;
        isRequesting = true;

        violationCount++;

        if (textCount) textCount.textContent = violationCount;
        if (textLockedCount) textLockedCount.textContent = violationCount;

        $wire.call('catatPelanggaran', violationCount).then(() => {
            isLocked = $wire.get('isLocked');
            isRequesting = false;

            if (isLocked || violationCount >= 3) {
                applyLockState();
            } else {
                // Tampilkan modal peringatan dan kunci flag agar modal tetap di layar
                if (warningModal) {
                    warningModal.classList.remove('hidden');
                    isWarningOpen = true;
                }
            }
        }).catch(() => {
            isRequesting = false;
        });
    }

    // SATU-SATUNYA CARA MENUTUP MODAL PERINGATAN: KLIK TOMBOL
    if (btnCloseWarning) {
        btnCloseWarning.addEventListener('click', function() {
            if (!isLocked && warningModal) {
                warningModal.classList.add('hidden');
                // Beri jeda 1 detik agar aksi klik tidak memicu event blur baru secara tidak sengaja
                setTimeout(() => {
                    isWarningOpen = false;
                }, 1000);
            }
        });
    }

    // Pantau jika status kunci dibuka oleh pengawas
    $wire.watch('isLocked', (value) => {
        isLocked = value;
        if (!value) {
            violationCount = parseInt($wire.get('jumlahPelanggaran')) || 0;
            if (textCount) textCount.textContent = violationCount;
            removeLockState();
        } else {
            applyLockState();
        }
    });

    // DETEKSI PINDAH TAB / MINIMIZE / BLUR
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && !isLocked && !isWarningOpen) {
            triggerViolation();
        }
    });

    window.addEventListener('blur', function() {
        if (!isLocked && !isWarningOpen && !document.hidden) {
            triggerViolation();
        }
    });

    // KEAMANAN: MATIKAN COPAS & INSPECT ELEMENT
    document.addEventListener("contextmenu", e => e.preventDefault());

    document.addEventListener("selectstart", function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return true;
        }
        e.preventDefault();
    });

    document.addEventListener("copy", e => e.preventDefault());
    document.addEventListener("cut", e => e.preventDefault());
    document.addEventListener("paste", e => e.preventDefault());
    document.addEventListener("dragstart", e => e.preventDefault());

    document.addEventListener("keydown", function(e) {
        if (e.key === "F12") e.preventDefault();

        if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'i', 'j', 'c'].includes(e.key)) {
            e.preventDefault();
        }

        if (e.ctrlKey && ['u', 's', 'p', 'a', 'c', 'v', 'x', 'U', 'S', 'P', 'A', 'C', 'V', 'X'].includes(e.key)) {
            e.preventDefault();
        }
    });
</script>
@endscript