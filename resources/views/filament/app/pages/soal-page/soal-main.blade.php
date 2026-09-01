<!-- Container Utama Soal -->
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow border border-gray-100 dark:border-gray-700">
    @if (isset($soals[$currentIndex]))
    @php
    $soalAktif = $soals[$currentIndex];

    $jenisSoal = $soalAktif->jenis_soal ?? 'pilihan_ganda';
    $abjad = range('A', 'Z');

    $pilihanJawaban = is_array($soalAktif->pilihan_jawaban)
    ? $soalAktif->pilihan_jawaban
    : json_decode($soalAktif->pilihan_jawaban ?? '[]', true);

    $pernyataanBS = is_array($soalAktif->pernyataan_bs)
    ? $soalAktif->pernyataan_bs
    : json_decode($soalAktif->pernyataan_bs ?? '[]', true);

    $pasanganKiri = is_array($soalAktif->pasangan_kiri)
    ? $soalAktif->pasangan_kiri
    : json_decode($soalAktif->pasangan_kiri ?? '[]', true);

    $pasanganKanan = is_array($soalAktif->pasangan_kanan)
    ? $soalAktif->pasangan_kanan
    : json_decode($soalAktif->pasangan_kanan ?? '[]', true);
    @endphp

    <!-- Info Nomor & Badge Tipe Soal -->
    <div class="mb-4 flex justify-between items-center pb-3 border-b dark:border-gray-700">
        <span class="font-bold text-lg text-gray-900 dark:text-white">
            Soal No. {{ $currentIndex + 1 }}
        </span>

        <span class="text-xs px-3 py-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-full font-semibold capitalize">
            {{ str_replace('_', ' ', $jenisSoal) }}
        </span>
    </div>

    <!-- Teks Pertanyaan -->
    <div class="text-base text-gray-800 dark:text-gray-200 mb-6 prose dark:prose-invert max-w-none">
        {!! $soalAktif->pertanyaan !!}
    </div>

    <!-- Gambar Soal (Jika Ada) -->
    @if ($soalAktif->gambar_soal)
    <div class="mb-5">
        <img src="{{ asset('storage/' . $soalAktif->gambar_soal) }}" class="max-h-72 rounded-lg border dark:border-gray-700 object-contain">
    </div>
    @endif

    <!-- RENDER OPSIONAL JAWABAN DINAMIS -->
    @switch($jenisSoal)
    @case('pilihan_ganda')
    @include('filament.app.pages.soal-page.soal-pilihan-ganda')
    @break

    @case('pilihan_ganda_kompleks')
    @include('filament.app.pages.soal-page.soal-pilihan-ganda-kompleks')
    @break

    @case('benar_salah')
    @include('filament.app.pages.soal-page.soal-benar-salah')
    @break

    @case('menjodohkan')
    @include('filament.app.pages.soal-page.soal-menjodohkan')
    @break

    @default
    @include('filament.app.pages.soal-page.soal-pilihan-ganda')
    @endswitch

    @endif
</div>