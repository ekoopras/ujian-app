<!-- Header Info & Modal Trigger -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl shadow mb-6 border border-gray-100 dark:border-gray-700">

    <div class="flex items-center gap-3 self-end sm:self-auto">
        <!-- Trigger Modal Nomor Soal -->
        <x-filament::modal id="modal-navigasi-soal" width="lg">
            <x-slot name="trigger">
                <x-filament::button icon="heroicon-o-squares-2x2" color="gray">
                    Nomor Soal
                </x-filament::button>
            </x-slot>

            <x-slot name="heading">
                Nomor Soal
            </x-slot>

            <!-- Grid Nomor Soal -->
            <div class="grid grid-cols-5 sm:grid-cols-8 gap-2 p-1">
                @foreach ($soals as $idx => $s)
                @php
                $jawaban = $jawabanSiswa[$s->id] ?? null;
                $sudahDijawab = !empty($jawaban);
                $isAktif = $idx === $currentIndex;

                // Cek status ragu (mengantisipasi key bertipe integer maupun string dari JSON)
                $isRagu = !empty($raguSiswa[$s->id]) || !empty($raguSiswa[(string)$s->id]);
                @endphp
                <button
                    type="button"
                    wire:click="setSoalIndex({{ $idx }})"
                    x-on:click="close"
                    class="h-10 text-sm font-bold rounded-lg border flex items-center justify-center transition duration-150
                {{ $isAktif ? 'ring-2 ring-indigo-500 border-indigo-500 font-extrabold' : '' }}
                @if ($isRagu)
                    bg-amber-500 text-white border-amber-500 hover:bg-amber-600
                @elseif ($sudahDijawab)
                    bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700
                @else
                    bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600
                @endif
            ">
                    {{ $idx + 1 }}
                </button>
                @endforeach
            </div>
        </x-filament::modal>

        <div class="text-right border-l pl-3 dark:border-gray-700"
            x-data="{
        sisaDetik: @js($sisaDetik),
        formattedTime: '',
        timer: null,
        init() {
            if (this.sisaDetik <= 0) return;
            
            this.updateTimer();
            this.timer = setInterval(() => {
                if (this.sisaDetik > 0) {
                    this.sisaDetik--;
                    this.updateTimer();
                } else {
                    clearInterval(this.timer);
                    $wire.submitUjian();
                }
            }, 1000);
        },
        updateTimer() {
            let jam = Math.floor(this.sisaDetik / 3600);
            let menit = Math.floor((this.sisaDetik % 3600) / 60);
            let detik = this.sisaDetik % 60;

            let pad = (num) => String(num).padStart(2, '0');
            
            if (jam > 0) {
                this.formattedTime = `${pad(jam)}:${pad(menit)}:${pad(detik)}`;
            } else {
                this.formattedTime = `${pad(menit)}:${pad(detik)}`;
            }
        }
     }">
            <span class="text-[10px] text-gray-400 font-semibold uppercase block">
                {{ $sisaDetik > 0 ? 'Sisa Waktu' : 'Mode' }}
            </span>

            <template x-if="sisaDetik > 0">
                <span class="text-xs font-bold text-amber-500 font-mono tracking-wider" x-text="formattedTime">
                    00:00
                </span>
            </template>

            <template x-if="sisaDetik <= 0">
                <span class="text-xs font-bold text-amber-500">
                    Timer Off
                </span>
            </template>
        </div>
    </div>
</div>