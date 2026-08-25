<div class="space-y-3">
    <p class="text-xs text-gray-500 dark:text-gray-400 italic mb-2">* Pilihlah satu atau lebih jawaban yang menurut Anda benar.</p>
    @foreach ($pilihanJawaban as $idx => $pilihan)
    @php
    $teksJawaban = is_array($pilihan) ? ($pilihan['teks'] ?? '') : $pilihan;
    $gambarJawaban = is_array($pilihan) ? ($pilihan['gambar_jawaban'] ?? null) : null;
    $huruf = $abjad[$idx] ?? ($idx + 1);
    $currentAnswer = $jawabanSiswa[$soalAktif->id] ?? [];
    if (!is_array($currentAnswer)) {
    $currentAnswer = array_filter(explode('; ', $currentAnswer));
    }
    $isSelected = in_array($teksJawaban, $currentAnswer);
    @endphp

    <label
        wire:key="opt-pgk-{{ $soalAktif->id }}-{{ $idx }}"
        class="flex items-start p-4 border rounded-xl cursor-pointer transition duration-150 {{ $isSelected ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-1 ring-indigo-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
        <input
            type="checkbox"
            value="{{ $teksJawaban }}"
            wire:click="simpanJawabanKompleks({{ $soalAktif->id }}, '{{ addslashes($teksJawaban) }}')"
            @checked($isSelected)
            class="mt-1 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600">
        <div class="ml-3 text-sm flex-1">
            <div class="font-medium text-gray-900 dark:text-gray-100">
                <span class="font-bold mr-1">{{ $huruf }}.</span> {{ $teksJawaban }}
            </div>
            @if ($gambarJawaban)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $gambarJawaban) }}" class="max-h-36 rounded-lg border dark:border-gray-700">
            </div>
            @endif
        </div>
    </label>
    @endforeach
</div>