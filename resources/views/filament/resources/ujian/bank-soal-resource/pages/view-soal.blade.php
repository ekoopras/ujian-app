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

                {{-- 1. Pilihan Ganda / Kompleks --}}
                @if(in_array($soal->jenis_soal, ['pilihan_ganda', 'pilihan_ganda_kompleks']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
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
                            <span class="text-sm text-gray-800 dark:text-gray-200">
                                {{ $opt['teks'] ?? '-' }}
                            </span>
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
                    @php $isBenar = !empty($opt['is_benar']); @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg border bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $optIndex + 1 }}. {{ $opt['teks'] ?? '-' }}
                        </span>
                        <span class="text-xs px-2.5 py-1 rounded font-bold {{ $isBenar ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                            {{ $isBenar ? 'BENAR' : 'SALAH' }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- 3. Menjodohkan --}}
                @elseif($soal->jenis_soal === 'menjodohkan')
                <div class="space-y-2 mt-2">
                    @foreach($soal->pilihan_jawaban ?? [] as $opt)
                    <div class="grid grid-cols-12 gap-2 p-3 rounded-lg border bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700 text-sm">
                        <div class="col-span-5 font-medium text-gray-800 dark:text-gray-200">
                            {{ $opt['kunci'] ?? '-' }}
                        </div>
                        <div class="col-span-2 text-center text-primary-600 font-bold">
                            &rarr;
                        </div>
                        <div class="col-span-5 font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ $opt['nilai_pasangan'] ?? '-' }}
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