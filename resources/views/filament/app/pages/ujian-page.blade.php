<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Gunakan $ujians langsung dari getViewData() -->
        @forelse ($ujians as $ujian)
        @php
        $ujianSiswa = \App\Models\UjianSiswa::where('ujian_id', $ujian->id)
        ->where('user_id', auth()->id())
        ->first();
        @endphp
        <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start mb-2">
                <span class="px-2 py-1 text-xs font-semibold rounded bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                    {{ $ujian->durasi_menit }} Menit
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Mapel: <strong>{{ $ujian->mapel->name }}</strong>
            </p>

            @if ($ujianSiswa && $ujianSiswa->status === 'selesai')
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm font-semibold rounded-lg text-center">
                ✓ Ujian Telah Selesai
            </div>
            @else
            <div class="space-y-3">
                <input
                    type="text"
                    placeholder="TOKEN 6 DIGIT"
                    wire:model="tokenInputs.{{ $ujian->id }}"
                    maxlength="6"
                    class="w-full text-center font-bold tracking-widest uppercase border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-lg focus:ring-primary-500 focus:border-primary-500" />

                <x-filament::button
                    wire:click="mulaiUjian({{ $ujian->id }})"
                    class="w-full">
                    {{ $ujianSiswa ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}
                </x-filament::button>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            Tidak ada ujian yang aktif untuk kelas Anda saat ini.
        </div>
        @endforelse
    </div>
</x-filament-panels::page>