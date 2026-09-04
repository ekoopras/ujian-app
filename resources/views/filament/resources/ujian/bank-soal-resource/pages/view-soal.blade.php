<x-filament-panels::page>
    @php
    $totalSoal = $soals->count();
    $totalBobot = $soals->sum('bobot_nilai');
    @endphp

    {{-- Header Info Bank Soal --}}
    <div class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                {{ $record->nama ?? 'Bank Soal' }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Mata Pelajaran: <strong>{{ $record->mapel->nama_mapel ?? '-' }}</strong> | Kelas: <strong>{{ $record->kelas ?? '-' }}</strong>
            </p>
        </div>
        <div class="flex space-x-3 text-sm font-medium">
            <span class="px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                Total Soal: <strong>{{ $totalSoal }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                Total Bobot: <strong>{{ $totalBobot }} Poin</strong>
            </span>
        </div>
    </div>

    {{-- Daftar Soal --}}
    <div class="space-y-6">
        @forelse($soals as $index => $soal)
        <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-4">
            {{-- Header Item --}}
            <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3">
                <span class="font-bold text-primary-600 dark:text-primary-400 text-base">
                    Soal No. {{ $index + 1 }}
                </span>
                <div class="flex items-center space-x-2">
                    <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-medium uppercase">
                        {{ str_replace('_', ' ', $soal->jenis_soal) }}
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-semibold">
                        Bobot: {{ $soal->bobot_nilai }} Poin
                    </span>
                </div>
            </div>

            {{-- Pertanyaan --}}
            <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200">
                {!! $soal->pertanyaan !!}
            </div>

            @if($soal->gambar_soal)
            <div class="my-2">
                <img src="{{ asset('storage/' . $soal->gambar_soal) }}" class="max-h-60 rounded-lg border dark:border-gray-700">
            </div>
            @endif

            {{-- Detail Opsi Jawaban --}}
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pilihan & Kunci Jawaban:</span>

                {{-- 1. Pilihan Ganda / Kompleks (Diubah menjadi 1 Grid / Menyusun Kebawah) --}}
                @if(in_array($soal->jenis_soal, ['pilihan_ganda', 'pilihan_ganda_kompleks']))
                <div class="grid grid-cols-1 gap-2 mt-2">
                    @foreach($soal->pilihan_jawaban ?? [] as $optIndex => $opt)
                    @php
                    $isKunci = !empty($opt['is_active']);
                    $huruf = chr(65 + $optIndex);
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg border {{ $isKunci ? 'bg-emerald-50 border-emerald-300 dark:bg-emerald-950/40 dark:border-emerald-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/40 dark:border-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold {{ $isKunci ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $huruf }}.
                            </span>
                            <div class="flex flex-col">
                                <span class="text-sm text-gray-800 dark:text-gray-200">
                                    {{ $opt['teks'] ?? '-' }}
                                </span>

                                {{-- Penambahan Gambar Jawaban --}}
                                @if(!empty($opt['gambar_jawaban']))
                                <img src="{{ Storage::url($opt['gambar_jawaban']) }}"
                                    alt="Gambar {{ $huruf }}"
                                    class="mt-1 max-h-24 w-auto rounded border border-gray-200 dark:border-gray-700 object-contain">
                                @endif
                            </div>
                        </div>
                        @if($isKunci)
                        <span class="text-xs px-2 py-0.5 rounded bg-emerald-200 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100 font-bold">
                            KUNCI
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- 2. Benar / Salah --}}
                @elseif($soal->jenis_soal === 'benar_salah')
                <div class="space-y-2 mt-2">
                    @foreach($soal->pilihan_jawaban ?? [] as $optIndex => $opt)
                    @php
                    // Menggunakan key is_active sesuai logika backend baru
                    $isKunci = !empty($opt['is_active']);
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg border {{ $isKunci ? 'bg-emerald-50 border-emerald-300 dark:bg-emerald-950/40 dark:border-emerald-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/40 dark:border-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $opt['teks'] ?? '-' }}
                                </span>

                                {{-- Render Gambar Jawaban jika diisi --}}
                                @if(!empty($opt['gambar_jawaban']))
                                <img src="{{ Storage::url($opt['gambar_jawaban']) }}"
                                    alt="Gambar Opsi"
                                    class="mt-1 max-h-24 w-auto rounded border border-gray-200 dark:border-gray-700 object-contain">
                                @endif
                            </div>
                        </div>

                        {{-- Badge Penanda Kunci Jawaban --}}
                        @if($isKunci)
                        <span class="text-xs px-2.5 py-1 rounded bg-emerald-200 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100 font-bold">
                            KUNCI JAWABAN
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- 3. Menjodohkan --}}
                @elseif($soal->jenis_soal === 'menjodohkan')
                <div class="space-y-2 mt-2">
                    @foreach($soal->pilihan_jawaban ?? [] as $opt)
                    <div class="grid grid-cols-12 gap-2 p-3 items-center rounded-lg border bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700 text-sm">
                        {{-- Sisi Kiri (Pernyataan/Soal + Gambar) --}}
                        <div class="col-span-5 font-medium text-gray-800 dark:text-gray-200">
                            <div>{{ $opt['kunci'] ?? '-' }}</div>

                            {{-- Render Kunci Gambar jika diisi --}}
                            @if(!empty($opt['kunci_gambar']))
                            <img src="{{ Storage::url($opt['kunci_gambar']) }}"
                                alt="Gambar Pernyataan"
                                class="mt-1.5 max-h-24 w-auto rounded border border-gray-200 dark:border-gray-700 object-contain">
                            @endif
                        </div>

                        {{-- Panah Penghubung --}}
                        <div class="col-span-2 text-center text-primary-600 font-bold">
                            &rarr;
                        </div>

                        {{-- Sisi Kanan (Pasangan Jawaban + Nilai Poin) --}}
                        <div class="col-span-5 flex items-center justify-between">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $opt['nilai_pasangan'] ?? '-' }}
                            </span>

                            {{-- Display Poin Poin Pasangan --}}
                            @if(isset($opt['nilai']))
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-semibold border border-emerald-200 dark:border-emerald-800">
                                +{{ $opt['nilai'] }} Poin
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 text-gray-500">
            Belum ada soal tersimpan untuk Bank Soal ini.
        </div>
        @endforelse
    </div>
</x-filament-panels::page>