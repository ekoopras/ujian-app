<div class="p-4" x-data="{ search: '' }">
    <!-- Bagian Atas: Form Upload -->
    <div class="mb-6 p-4 border rounded-xl bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800">
        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
            Unggah Foto Baru ke Pustaka
        </label>
        <div class="flex items-center gap-4">
            <input type="file" wire:model="media"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-800 dark:file:text-primary-400 cursor-pointer">

            <button type="button" wire:click="uploadFoto" wire:loading.attr="disabled"
                class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-medium shadow hover:bg-primary-500 disabled:opacity-50 whitespace-nowrap transition-colors">
                <span wire:loading.remove>Upload Sekarang</span>
                <span wire:loading>Mengupload...</span>
            </button>
        </div>
        @error('media')
        <span class="text-danger-600 text-xs mt-1.5 block font-medium">{{ $message }}</span>
        @enderror
    </div>

    <hr class="my-4 border-gray-200 dark:border-gray-800">

    <!-- Pencarian -->
    <div class="mb-4">
        <input type="text" x-model="search" placeholder="Cari nama foto..."
            class="w-full max-w-md px-3.5 py-2 text-sm border rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
    </div>

    <!-- Bagian Bawah: Galeri 3 Kolom Presisi (Disertai Direct CSS Style) -->
    @if ($mediaItems->isEmpty())
    <div class="text-center py-10 border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
        <p class="text-sm text-gray-400">Pustaka media Anda masih kosong.</p>
    </div>
    @else
    <!-- DIKUNCI 3 KOLOM MENGGUNAKAN INLINE STYLE -->
    <div
        class="grid gap-4 max-h-[420px] overflow-y-auto p-1 pr-2"
        style="display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important;">
        @foreach ($mediaItems as $media)
        <div x-show="search === '' || '{{ strtolower($media->file_name) }}'.includes(search.toLowerCase())"
            class="group relative border rounded-xl overflow-hidden cursor-pointer hover:border-primary-500 hover:shadow-md transition-all p-1.5 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 flex flex-col justify-between"
            x-on:click="
                        const targetInput = document.getElementById('{{ $statePath }}') || document.querySelector('[name=\'{{ $statePath }}\']');
                        if (targetInput) {
                            targetInput.value = '{{ $media->file_path }}';
                            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        close();
                    ">

            {{-- Box Foto Rasio 1:1 --}}
            <div class="w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-900 relative" style="aspect-ratio: 1 / 1;">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($media->file_path) }}"
                    alt="{{ $media->file_name }}"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    style="width: 100%; height: 100%; object-fit: cover;">

                {{-- Hover Badge --}}
                <div class="absolute inset-0 bg-primary-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2">
                    <span class="bg-primary-600 text-white text-xs px-2.5 py-1 rounded-md shadow font-semibold">
                        Pilih
                    </span>
                </div>
            </div>

            {{-- Nama File --}}
            <div class="p-1 mt-1 text-[11px] font-medium truncate text-gray-700 dark:text-gray-300 text-center"
                title="{{ $media->file_name }}">
                {{ $media->file_name }}
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>