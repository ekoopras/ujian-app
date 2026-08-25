<?php

namespace App\Livewire;

use App\Models\Media as MediaModel; // <-- Gunakan alias di sini
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Media extends Component
{
    use WithFileUploads;

    public $media;
    public string $statePath = '';

    public function mount(string $statePath = '')
    {
        $this->statePath = $statePath;
    }

    public function uploadFoto()
    {
        $this->validate([
            'media' => 'image|max:8196',
        ]);

        $namaAsli = $this->media->getClientOriginalName();
        $path = $this->media->storeAs(
            'media/upload/user-' . Auth::id(),
            time() . '_' . $namaAsli,
            'public'
        );

        // Gunakan MediaModel
        MediaModel::create([
            'user_id' => Auth::id(),
            'file_name' => $namaAsli,
            'file_path' => $path,
            'file_hash' => md5_file($this->media->getRealPath()),
            'file_size' => round($this->media->getSize() / 1024, 2),
            'mime_type' => $this->media->getClientMimeType(),
        ]);

        $this->media = null;
    }

    public function render()
    {
        return view('livewire.media', [
            // Gunakan MediaModel
            'mediaItems' => MediaModel::where('user_id', Auth::id())->latest()->get()
        ]);
    }
}
