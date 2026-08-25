<div class="space-y-4">
    <p class="text-xs text-gray-500 dark:text-gray-400 italic mb-2">
        * Pasangkan item sebelah kiri dengan pilihan sebelah kanan secara tepat.
    </p>

    @php
    $rawPilihan = is_array($soalAktif->pilihan_jawaban)
    ? $soalAktif->pilihan_jawaban
    : json_decode($soalAktif->pilihan_jawaban ?? '[]', true);

    $itemsKiri = [];
    $opsiKanan = [];

    if (is_array($rawPilihan)) {
    foreach ($rawPilihan as $index => $item) {
    $keyKiri = $index + 1;
    $itemsKiri[$keyKiri] = $item['kunci'] ?? '';
    $opsiKanan[] = $item['nilai_pasangan'] ?? '';
    }
    }

    srand($soalAktif->id);
    $opsiKananAcak = $opsiKanan;
    shuffle($opsiKananAcak);
    srand();
    @endphp

    @forelse ($itemsKiri as $kunciKiri => $teksKiri)
    @php
    $jawabanPasangan = $jawabanSiswa[$soalAktif->id][$kunciKiri] ?? '';
    @endphp

    <div
        wire:key="opt-jodoh-{{ $soalAktif->id }}-{{ $kunciKiri }}"
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-800/50">
        <!-- Sisi Kiri -->
        <div class="font-medium text-sm text-gray-900 dark:text-gray-100 flex-1">
            <span class="font-bold mr-1">{{ $kunciKiri }}.</span> {{ $teksKiri }}
        </div>

        <!-- Sisi Kanan: Select Dropdown -->
        <div class="w-full sm:w-64">
            <select
                wire:change="simpanJawabanMenjodohkan({{ $soalAktif->id }}, '{{ $kunciKiri }}', $event.target.value)"
                class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Pilih Pasangan --</option>

                @foreach ($opsiKananAcak as $teksKanan)
                <option value="{{ $teksKanan }}" @selected($jawabanPasangan===$teksKanan)>
                    {{ $teksKanan }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    @empty
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-lg text-sm">
        Data pilihan pasangan belum diisi pada soal ini.
    </div>
    @endforelse
</div>