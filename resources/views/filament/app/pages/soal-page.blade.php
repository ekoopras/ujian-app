<x-filament-panels::page>
    {{-- Tambahkan max-w-5xl/6xl dan mx-auto di wrapper utama --}}
    <div class="max-w-4xl mx-auto w-full pb-28">
        @include('filament.app.pages.soal-page.soal-header')
        @include('filament.app.pages.soal-page.soal-main')
    </div>

    <div class="max-w-5xl mx-auto w-full">
        @include('filament.app.pages.soal-page.soal-footer')
    </div>
</x-filament-panels::page>