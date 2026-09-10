<!-- Modal Peringatan saat Layar Kehilangan Fokus / Split Screen -->
<div id="security-overlay" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-red-600 text-white p-6 rounded-2xl shadow-2xl text-center max-w-md">
        <x-heroicon-o-exclamation-triangle class="w-16 h-16 mx-auto mb-3 animate-bounce" />
        <h3 class="text-xl font-bold">PERINGATAN KEAMANAN!</h3>
        <p class="text-sm mt-2 opacity-90">
            Aktivitas mencurigakan terdeteksi (pindah tab, split screen, atau keluar fokus browser). Kembalikan fokus ke layar ini untuk melanjutkan ujian!
        </p>
    </div>
</div>

@push('scripts')

<!-- Blokir Aksi Pengguna untuk Mencegah Copy, Paste, dan Inspect Element -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Blokir klik kanan (Context Menu)
        document.addEventListener("contextmenu", e => e.preventDefault());

        // 2. Blokir aksi Copy, Cut, dan Paste
        document.addEventListener("copy", e => e.preventDefault());
        document.addEventListener("cut", e => e.preventDefault());
        document.addEventListener("paste", e => e.preventDefault());

        // 3. Blokir shortcut keyboard umum (Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+U untuk view-source, F12)
        document.addEventListener("keydown", function(e) {
            if (
                (e.ctrlKey && ["c", "v", "x", "u", "s"].includes(e.key.toLowerCase())) ||
                e.key === "F12"
            ) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>

<!-- Blokir Overlay saat Layar Kehilangan Fokus / Split Screen -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('soal-container');
        const footer = document.getElementById('soal-footer-container');
        const overlay = document.getElementById('security-overlay');

        // 1. DUA ARAH: BLUR DAN LOCK INTERAKSI
        function enableProtection() {
            if (container) container.classList.add('blur-2xl', 'pointer-events-none', 'select-none');
            if (footer) footer.classList.add('blur-2xl', 'pointer-events-none', 'select-none');
            if (overlay) overlay.classList.remove('hidden');
        }

        function disableProtection() {
            if (container) container.classList.remove('blur-2xl', 'pointer-events-none', 'select-none');
            if (footer) footer.classList.remove('blur-2xl', 'pointer-events-none', 'select-none');
            if (overlay) overlay.classList.add('hidden');
        }

        // 2. DETEKSI PINDAH TAB / MINIMIZE BROWSER
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                enableProtection();
            } else {
                disableProtection();
            }
        });

        // 3. DETEKSI SPLIT SCREEN / KLIK DI LUAR BROWSER (ALT+TAB, WIN KEY, DLL)
        window.addEventListener('blur', enableProtection);
        window.addEventListener('focus', disableProtection);

        // 4. BLOKIR KLIK KANAN, COPY, PASTE, DRAG
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('paste', e => e.preventDefault());
        document.addEventListener('cut', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        // 5. BLOKIR SHORTCUT KEYBOARD
        document.addEventListener('keydown', function(e) {
            // F12 (DevTools)
            if (e.key === 'F12') e.preventDefault();

            // Ctrl+Shift+I / J / C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'i', 'j', 'c'].includes(e.key)) {
                e.preventDefault();
            }

            // Ctrl+U (View Source)
            if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
                e.preventDefault();
            }

            // Ctrl+C, Ctrl+V, Ctrl+A, Ctrl+P
            if (e.ctrlKey && ['c', 'v', 'a', 'p', 'C', 'V', 'A', 'P'].includes(e.key)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush