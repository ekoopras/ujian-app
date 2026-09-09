<div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-6">
    <template x-if="currentSoal">
        <div x-data="{
            // Helper terpusat untuk mendeteksi tipe/jenis soal secara presisi
            getTipeSoal() {
                return (currentSoal.jenis_soal || currentSoal.tipe_soal || currentSoal.tipe || 'pilihan_ganda').toLowerCase();
            }
        }">
            <!-- Header Tipe Soal & Nomor -->
            <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                    Soal No. <strong class="text-gray-900 dark:text-gray-100" x-text="currentIndex + 1"></strong>
                </span>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold uppercase tracking-wide bg-primary-50 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 border border-primary-200 dark:border-primary-800"
                    x-text="getTipeSoal().replace(/_/g, ' ')">
                </span>
            </div>

            <!-- Teks Pertanyaan -->
            <div class="text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 leading-relaxed mb-6 prose dark:prose-invert max-w-none" x-html="currentSoal.pertanyaan"></div>

            <!-- Gambar Soal Utama (Jika Ada) -->
            <template x-if="currentSoal.gambar_soal">
                <div class="mb-4">
                    <img
                        :src="'/storage/' + currentSoal.gambar_soal"
                        class="max-h-72 w-auto rounded-lg object-contain border border-gray-200 dark:border-gray-700"
                        alt="Gambar Soal">
                </div>
            </template>

            <div class="space-y-3">

                <!-- ========================================== -->
                <!-- 1. PILIHAN GANDA BIASA / BENAR SALAH       -->
                <!-- ========================================== -->
                <template x-if="['pilihan_ganda', 'benar_salah'].includes(getTipeSoal())">
                    <div class="space-y-3">
                        <template x-for="(opsi, key) in currentSoal.pilihan_jawaban" :key="'pg-' + currentSoal.id + '-' + key">
                            <div
                                x-data="{ 
                    labelHuruf: String.fromCharCode(65 + parseInt(key)),
                    
                    // FUNGSI getTeksOpsi DITARUH DI SINI
                    getTeksOpsi(item) {
                        if (typeof item === 'object' && item !== null) {
                            if ('teks' in item) return item.teks || '';
                            if ('jawaban' in item) return item.jawaban || '';
                            if ('pilihan' in item) return item.pilihan || '';
                            return '';
                        }
                        return item || '';
                    }
                }"
                                @click="pilihJawaban(currentSoal.id, labelHuruf)"
                                class="flex items-start p-3 sm:p-3.5 rounded-xl border cursor-pointer transition-all duration-150 select-none"
                                :class="jawabanSiswa[currentSoal.id] === labelHuruf 
                    ? 'bg-primary-50 border-primary-500 text-primary-900 dark:bg-primary-950/60 dark:border-primary-500 dark:text-primary-200 ring-2 ring-primary-500/20' 
                    : 'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300'">

                                <!-- Label Huruf (A, B, C, D) -->
                                <div class="flex items-center h-5 mt-0.5">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold uppercase transition-colors"
                                        :class="jawabanSiswa[currentSoal.id] === labelHuruf 
                            ? 'bg-primary-600 text-white' 
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                                        <span x-text="labelHuruf"></span>
                                    </span>
                                </div>

                                <!-- Teks / Gambar Opsi Jawaban -->
                                <div class="ml-3 text-sm font-medium flex-1 leading-snug">
                                    <!-- Render Gambar Jika Ada -->
                                    <template x-if="typeof opsi === 'object' && opsi !== null && (opsi.gambar || opsi.gambar_jawaban)">
                                        <img :src="'/storage/' + (opsi.gambar || opsi.gambar_jawaban)" class="max-h-40 rounded-md mb-2 object-contain border">
                                    </template>

                                    <!-- Render Teks (Hanya jika teks tidak kosong) -->
                                    <span x-show="getTeksOpsi(opsi) !== ''" x-html="getTeksOpsi(opsi)"></span>
                                </div>

                            </div>
                        </template>
                    </div>
                </template>


                <!-- ========================================== -->
                <!-- 2. PILIHAN GANDA KOMPLEKS (CHECKBOX MULTI-SELECT) -->
                <!-- ========================================== -->
                <template x-if="getTipeSoal() === 'pilihan_ganda_kompleks'">
                    <div class="space-y-3"
                        x-data="{
                initKompleks() {
                    if (!Array.isArray(jawabanSiswa[currentSoal.id])) {
                        jawabanSiswa[currentSoal.id] = [];
                    }
                },
                isTerpilih(huruf) {
                    return Array.isArray(jawabanSiswa[currentSoal.id]) && jawabanSiswa[currentSoal.id].includes(huruf);
                }
            }"
                        x-init="initKompleks()">

                        <template x-for="(opsi, key) in currentSoal.pilihan_jawaban" :key="'pgk-' + currentSoal.id + '-' + key">
                            <div x-data="{ 
                    labelHuruf: String.fromCharCode(65 + parseInt(key)),
                    getTeksOpsi(item) {
                        if (typeof item === 'object' && item !== null) {
                            return item.teks || item.jawaban || item.pilihan || '';
                        }
                        return item;
                    },
                    getGambarOpsi(item) {
                        if (typeof item === 'object' && item !== null) {
                            return item.gambar_jawaban || item.gambar || null;
                        }
                        return null;
                    }
                }">
                                <label
                                    @click.prevent="toggleJawabanKompleks(currentSoal.id, labelHuruf)"
                                    class="flex items-start p-3 sm:p-3.5 border rounded-xl cursor-pointer transition-all duration-150 select-none"
                                    :class="isTerpilih(labelHuruf) 
                            ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-1 ring-indigo-500 text-indigo-900 dark:text-indigo-200' 
                            : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300'">

                                    <!-- Native Input Checkbox -->
                                    <input
                                        type="checkbox"
                                        :value="labelHuruf"
                                        :checked="isTerpilih(labelHuruf)"
                                        class="mt-1 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 cursor-pointer">

                                    <div class="ml-3 text-sm flex-1 leading-snug">
                                        <div class="font-medium text-gray-900 dark:text-gray-100 flex items-start gap-1">
                                            <span class="font-bold mr-1" x-text="labelHuruf + '.'"></span>
                                            <span x-html="getTeksOpsi(opsi)"></span>
                                        </div>

                                        <!-- Gambar Opsi Jawaban -->
                                        <template x-if="getGambarOpsi(opsi)">
                                            <div class="mt-2">
                                                <img :src="'/storage/' + getGambarOpsi(opsi)" class="max-h-36 rounded-lg border dark:border-gray-700 object-contain">
                                            </div>
                                        </template>
                                    </div>
                                </label>
                            </div>
                        </template>
                    </div>
                </template>


                <!-- ========================================== -->
                <!-- 3. MENJODOHKAN (MATCHING PAIRS)           -->
                <!-- ========================================== -->
                <template x-if="getTipeSoal() === 'menjodohkan'">
                    <div class="space-y-4"
                        x-data="{
            opsiKananShuffled: [],
            
            initMenjodohkan() {
                // PERBAIKAN: Hanya inisialisasi jika BELUM ADA jawaban sama sekali.
                // Jangan timpa jawaban jika sudah ada di Local Storage!
                if (!jawabanSiswa[currentSoal.id] || typeof jawabanSiswa[currentSoal.id] !== 'object' || Array.isArray(jawabanSiswa[currentSoal.id])) {
                    jawabanSiswa[currentSoal.id] = {};
                }

                // Ekstrak daftar jawaban sisi kanan & filter unik
                let rawKanan = (currentSoal.pilihan_jawaban || [])
                    .map(item => typeof item === 'object' ? item.nilai_pasangan : item)
                    .filter(Boolean);

                let uniqueKanan = Array.from(new Set(rawKanan));
                this.opsiKananShuffled = this.shuffleWithSeed(uniqueKanan, currentSoal.id);
            },

            shuffleWithSeed(array, seed) {
                let list = [...array];
                let m = list.length, t, i;
                let numSeed = typeof seed === 'number' ? seed : String(seed).split('').reduce((a, b) => a + b.charCodeAt(0), 0);
                
                while (m) {
                    i = Math.floor(this.seededRandom(numSeed++) * m--);
                    t = list[m];
                    list[m] = list[i];
                    list[i] = t;
                }
                return list;
            },

            seededRandom(s) {
                let x = Math.sin(s++) * 10000;
                return x - Math.floor(x);
            }
        }"
                        x-init="initMenjodohkan()">

                        <div class="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-800 dark:text-blue-300 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Pilih/jodohkan pasangan jawaban yang tepat untuk setiap pernyataan di bawah ini.</span>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, idx) in currentSoal.pilihan_jawaban" :key="'jodoh-' + currentSoal.id + '-' + idx">
                                <div class="p-4 border rounded-xl dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col md:flex-row md:items-center justify-between gap-4">

                                    <!-- Sisi Kiri (Pernyataan / Soal) -->
                                    <div class="flex-1 space-y-2">
                                        <div class="flex items-start gap-2">
                                            <span class="font-bold text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded" x-text="idx + 1"></span>
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="item.kunci"></span>
                                        </div>

                                        <!-- Gambar Sisi Kiri (Jika Ada) -->
                                        <template x-if="item.kunci_gambar">
                                            <div class="mt-1 pl-6">
                                                <img :src="'/storage/' + item.kunci_gambar" class="max-h-32 rounded border dark:border-gray-700 object-contain">
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Sisi Kanan (Dropdown Pasangan) -->
                                    <div class="w-full md:w-64 shrink-0">
                                        <select
                                            :value="(jawabanSiswa[currentSoal.id] && jawabanSiswa[currentSoal.id][item.kunci]) ? jawabanSiswa[currentSoal.id][item.kunci] : ''"
                                            @change="simpanMenjodohkan(currentSoal.id, item.kunci, $event.target.value)"
                                            class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                            <option value="">-- Pilih Pasangan --</option>
                                            <template x-for="(opsiKanan, kIdx) in opsiKananShuffled" :key="'opt-' + kIdx">
                                                <option :value="opsiKanan" x-text="opsiKanan" :selected="jawabanSiswa[currentSoal.id] && jawabanSiswa[currentSoal.id][item.kunci] === opsiKanan"></option>
                                            </template>
                                        </select>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

        </div>
    </template>

    <!-- State Loading -->
    <template x-if="!currentSoal">
        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
            <span class="text-xs font-medium">Memuat Soal...</span>
        </div>
    </template>
</div>