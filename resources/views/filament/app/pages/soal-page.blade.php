<x-filament-panels::page>
    <div x-data="ujianApp({
        ujianSiswaId: @js($ujianSiswaId),
        payloadSoal: @js($payloadSoal),
        initialJawaban: @js($initialJawaban),
        initialRagu: @js($initialRagu),
        sisaDetik: @js($sisaDetik)
    })" x-init="initApp()" class="space-y-4 relative pb-28 min-h-screen">

        <!-- 1. Header -->
        @include('filament.app.pages.components.soal.header')

        <!-- 2. Area Main Soal -->
        <div class="mb-6">
            @include('filament.app.pages.components.soal.main')
        </div>

        <!-- 3. Footer -->
        @include('filament.app.pages.components.soal.footer')

    </div>

    <!-- 4. Scurity -->
    @include('filament.app.pages.components.soal.security')

    <!-- Script Alpine JS Kompatibel Android 9 -->
    <script>
        document.addEventListener('alpine:init', function() {
            Alpine.data('ujianApp', function(config) {
                return {
                    ujianSiswaId: config.ujianSiswaId,
                    soals: [],
                    currentIndex: 0,
                    currentSoal: null,
                    jawabanSiswa: {},
                    raguSiswa: [],
                    sisaDetik: config.sisaDetik,
                    formattedTime: '00:00:00',
                    timer: null,
                    syncInterval: null,

                    initApp: function() {
                        var storageKey = 'ujian_data_' + this.ujianSiswaId;
                        var cachedData = localStorage.getItem(storageKey);

                        if (cachedData) {
                            try {
                                var parsed = JSON.parse(cachedData);
                                this.soals = parsed.soals || config.payloadSoal;

                                this.jawabanSiswa = (parsed.jawabanSiswa && typeof parsed.jawabanSiswa === 'object' && !Array.isArray(parsed.jawabanSiswa)) ?
                                    parsed.jawabanSiswa : {};

                                this.raguSiswa = parsed.raguSiswa || config.initialRagu;
                            } catch (e) {
                                this.loadInitialData(config);
                            }
                        } else {
                            this.loadInitialData(config);
                        }

                        if (this.soals.length > 0) {
                            this.currentSoal = this.soals[0];
                        }

                        this.startTimer();
                        this.startAutoSync();
                        this.saveToStorage();
                    },

                    loadInitialData: function(cfg) {
                        this.soals = cfg.payloadSoal;

                        this.jawabanSiswa = (cfg.initialJawaban && typeof cfg.initialJawaban === 'object' && !Array.isArray(cfg.initialJawaban)) ?
                            cfg.initialJawaban : {};

                        this.raguSiswa = cfg.initialRagu || [];
                    },

                    saveToStorage: function() {
                        var storageKey = 'ujian_data_' + this.ujianSiswaId;
                        var payload = {
                            soals: this.soals,
                            jawabanSiswa: this.jawabanSiswa,
                            raguSiswa: this.raguSiswa
                        };
                        localStorage.setItem(storageKey, JSON.stringify(payload));
                    },

                    // Single Select (Pilihan Ganda Biasa)
                    pilihJawaban: function(soalId, val) {
                        this.jawabanSiswa[soalId] = val;
                        this.saveToStorage();
                    },

                    // Multi Select (Pilihan Ganda Kompleks)
                    toggleJawabanKompleks: function(soalId, huruf) {
                        if (!Array.isArray(this.jawabanSiswa[soalId])) {
                            this.jawabanSiswa[soalId] = [];
                        }

                        let index = this.jawabanSiswa[soalId].indexOf(huruf);
                        if (index > -1) {
                            this.jawabanSiswa[soalId].splice(index, 1);
                        } else {
                            this.jawabanSiswa[soalId].push(huruf);
                        }

                        this.jawabanSiswa[soalId] = [...this.jawabanSiswa[soalId]];
                        this.saveToStorage(); // <-- TAMBAHAN: Autosave ke LocalStorage
                    },

                    // PERBAIKAN: Simpan & Hapus Jawaban Menjodohkan
                    simpanMenjodohkan: function(soalId, kunciKiri, nilaiKanan) {
                        // 1. Pastikan terinisialisasi sebagai object
                        if (!this.jawabanSiswa[soalId] || typeof this.jawabanSiswa[soalId] !== 'object' || Array.isArray(this.jawabanSiswa[soalId])) {
                            this.jawabanSiswa[soalId] = {};
                        }

                        // 2. Set atau hapus jawaban
                        if (nilaiKanan === '' || nilaiKanan === null) {
                            delete this.jawabanSiswa[soalId][kunciKiri];
                        } else {
                            this.jawabanSiswa[soalId][kunciKiri] = nilaiKanan;
                        }

                        // 3. Paksa Alpine mendeteksi perubahan Object
                        this.jawabanSiswa[soalId] = Object.assign({}, this.jawabanSiswa[soalId]);

                        // 4. SIMPAN KE LOCAL STORAGE
                        this.saveToStorage();
                    },

                    isRagu: function(soalId) {
                        if (!soalId) return false;
                        return this.raguSiswa.indexOf(soalId) !== -1;
                    },

                    toggleRagu: function(soalId) {
                        if (!soalId) return;
                        var idx = this.raguSiswa.indexOf(soalId);
                        if (idx === -1) {
                            this.raguSiswa.push(soalId);
                        } else {
                            this.raguSiswa.splice(idx, 1);
                        }
                        this.saveToStorage();
                    },

                    jumpTo: function(idx) {
                        this.currentIndex = idx;
                        this.currentSoal = this.soals[idx];
                        this.syncToDatabase();
                    },

                    nextSoal: function() {
                        if (this.currentIndex < this.soals.length - 1) {
                            this.jumpTo(this.currentIndex + 1);
                        }
                    },

                    prevSoal: function() {
                        if (this.currentIndex > 0) {
                            this.jumpTo(this.currentIndex - 1);
                        }
                    },

                    // PERBAIKAN: Pengecekan Status Terjawab (Mendukung Object Menjodohkan & Array PG Kompleks)
                    isAnswered: function(soalId) {
                        var jwb = this.jawabanSiswa[soalId];
                        if (typeof jwb === 'undefined' || jwb === null) return false;

                        // Jika tipe PG Kompleks (Array)
                        if (Array.isArray(jwb)) return jwb.length > 0;

                        // Jika tipe Menjodohkan (Object)
                        if (typeof jwb === 'object') return Object.keys(jwb).length > 0;

                        // Jika tipe PG Biasa / Essay (String/Number)
                        return String(jwb).trim() !== '';
                    },

                    // PERBAIKAN: Style Grid Tombol Nomor Soal
                    getGridStyle: function(soalId, idx) {
                        var isCurrent = this.currentIndex === idx;
                        var isTerjawab = this.isAnswered(soalId);
                        var isRaguRagu = this.isRagu(soalId);

                        if (isCurrent) {
                            return 'border-primary-600 ring-2 ring-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300';
                        }
                        if (isRaguRagu) {
                            return 'bg-amber-400 border-amber-500 text-white';
                        }
                        if (isTerjawab) {
                            return 'bg-primary-600 border-primary-600 text-white';
                        }
                        return 'border-gray-200 bg-gray-50 text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
                    },

                    startTimer: function() {
                        var self = this;
                        this.updateFormattedTime();
                        this.timer = setInterval(function() {
                            if (self.sisaDetik > 0) {
                                self.sisaDetik--;
                                self.updateFormattedTime();
                            } else {
                                clearInterval(self.timer);
                                self.submitFinal();
                            }
                        }, 1000);
                    },

                    updateFormattedTime: function() {
                        var jam = Math.floor(this.sisaDetik / 3600);
                        var menit = Math.floor((this.sisaDetik % 3600) / 60);
                        var detik = Math.floor(this.sisaDetik % 60);

                        var pad = function(n) {
                            return (n < 10 ? '0' : '') + n;
                        };

                        this.formattedTime = pad(jam) + ':' + pad(menit) + ':' + pad(detik);
                    },

                    startAutoSync: function() {
                        var self = this;
                        this.syncInterval = setInterval(function() {
                            self.syncToDatabase();
                        }, 30000);
                    },

                    syncToDatabase: function() {
                        var rawJawaban = JSON.parse(JSON.stringify(this.jawabanSiswa));
                        var rawRagu = JSON.parse(JSON.stringify(this.raguSiswa));

                        this.$wire.syncJawaban(rawJawaban, rawRagu);
                    },

                    confirmSubmit: function() {
                        if (confirm('Apakah Anda yakin ingin menyelesaikan ujian ini?')) {
                            this.submitFinal();
                        }
                    },

                    submitFinal: function() {
                        clearInterval(this.timer);
                        clearInterval(this.syncInterval);

                        var rawJawaban = JSON.parse(JSON.stringify(this.jawabanSiswa));
                        var rawRagu = JSON.parse(JSON.stringify(this.raguSiswa));

                        localStorage.removeItem('ujian_data_' + this.ujianSiswaId);

                        this.$wire.submitFinal(rawJawaban, rawRagu);
                    }
                };
            });
        });
    </script>
</x-filament-panels::page>