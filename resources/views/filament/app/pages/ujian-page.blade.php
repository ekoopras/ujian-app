<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($ujians as $ujian)
        @php
        $user = auth()->user();
        $ujianSiswa = \App\Models\UjianSiswa::where('ujian_id', $ujian->id)
        ->where('user_id', $user->id)
        ->first();
        @endphp
        <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
            <div>
                <!-- Header Kartu: Status Badge & Durasi -->
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        <x-heroicon-m-clock class="w-3.5 h-3.5" />
                        {{ $ujian->durasi_menit }} Menit
                    </span>

                    @if($ujianSiswa)
                    @if($ujianSiswa->status === 'selesai')
                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">Selesai</span>
                    @else
                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">Sedang Berlangsung</span>
                    @endif
                    @else
                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Belum Mulai</span>
                    @endif
                </div>

                <!-- Judul Mapel Ujian -->
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">
                    {{ $ujian->mapel->name ?? 'Mata Pelajaran N/A' }}
                </h3>

                <!-- Detail Tambahan Ujian -->
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300 mb-6">
                    <div class="flex items-center gap-2">
                        <span>
                            <strong class="text-gray-700 dark:text-gray-200">Bank Soal:</strong>
                            <br>{{ $ujian->bankSoal->nama ?? '-' }}
                        </span>
                    </div>

                    @if($ujianSiswa && $ujianSiswa->waktu_mulai)
                    <div class="flex items-start gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                        <x-heroicon-m-calendar class="w-4 h-4 text-gray-400 shrink-0" />
                        <div>
                            <div><strong class="text-gray-700 dark:text-gray-200">Waktu Mulai:</strong> {{ \Carbon\Carbon::parse($ujianSiswa->waktu_mulai)->format('d M Y, H:i') }}</div>
                            <div><strong class="text-gray-700 dark:text-gray-200">Batas Waktu:</strong> {{ \Carbon\Carbon::parse($ujianSiswa->waktu_selesai_seharusnya)->format('H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Ringkasan Informasi Peserta -->
            <div class="p-3 mb-4 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 space-y-1">
                <div class="text-xs text-emerald-700 dark:text-emerald-400 font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>Identitas Peserta</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200">
                        Absen: {{ $user->nomor_absen ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm pt-0.5">
                    <span class="font-bold text-emerald-950 dark:text-emerald-100">{{ $user->name }}</span>
                </div>
                <div class="text-xs text-emerald-700/90 dark:text-emerald-300/80 flex items-center gap-1 pt-0.5">
                    <x-heroicon-m-academic-cap class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                    Kelas: {{ $user->kelase->name ?? '-' }}
                </div>
            </div>

            <!-- Input Token & Tombol Aksi -->
            <div>
                @if ($ujianSiswa && $ujianSiswa->status === 'selesai')
                <div class="p-3 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800/60 text-green-700 dark:text-green-300 text-sm font-semibold rounded-lg text-center flex items-center justify-center gap-2">
                    <x-heroicon-m-check-circle class="w-5 h-5" />
                    Ujian Telah Selesai
                </div>
                @else
                <div class="space-y-3">
                    @if(!$ujianSiswa)
                    <input
                        type="text"
                        placeholder="MASUKKAN TOKEN"
                        wire:model="tokenInputs.{{ $ujian->id }}"
                        maxlength="6"
                        class="w-full text-center font-mono font-bold tracking-widest uppercase border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm" />
                    @endif

                    <x-filament::button
                        wire:click="mulaiUjian({{ $ujian->id }})"
                        class="w-full">
                        {{ $ujianSiswa ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}
                    </x-filament::button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto text-gray-400 mb-3" />
            <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada ujian yang aktif untuk kelas Anda saat ini.</p>
        </div>
        @endforelse
    </div>
</x-filament-panels::page>