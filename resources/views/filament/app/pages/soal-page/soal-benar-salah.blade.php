<div class="space-y-3">
    @php
    $rawPilihan = is_array($soalAktif->pilihan_jawaban)
    ? $soalAktif->pilihan_jawaban
    : json_decode($soalAktif->pilihan_jawaban ?? '[]', true);

    $jawabanSiswaSoal = $jawabanSiswa[$soalAktif->id] ?? null;
    @endphp

    @forelse ($rawPilihan as $index => $item)
    @php
    $huruf = $abjad[$index] ?? ($index + 1);
    $teksOpsi = $item['teks'] ?? '';
    $gambarOpsi = $item['gambar_jawaban'] ?? null;
    $isSelected = ($jawabanSiswaSoal === $teksOpsi);
    @endphp

    <label
        wire:key="opt-bs-{{ $soalAktif->id }}-{{ $index }}"
        class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer 
            {{ $isSelected 
                ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 dark:border-indigo-500' 
                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">

        <input
            type="radio"
            name="soal_{{ $soalAktif->id }}"
            value="{{ $teksOpsi }}"
            wire:change="simpanJawaban({{ $soalAktif->id }}, '{{ addslashes($teksOpsi) }}')"
            @checked($isSelected)
            class="mt-0.5 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">

        <div class="flex-1 text-sm text-gray-800 dark:text-gray-200">
            <span class="font-bold mr-1">{{ $huruf }}.</span> {{ $teksOpsi }}

            @if ($gambarOpsi)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $gambarOpsi) }}" class="max-h-40 rounded-lg border dark:border-gray-700 object-contain">
            </div>
            @endif
        </div>
    </label>
    @empty
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-lg text-sm">
        Opsi pilihan Benar/Salah belum diisi pada soal ini.
    </div>
    @endforelse
</div>