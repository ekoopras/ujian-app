<?php

namespace App\Filament\App\Pages\Auth;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterSiswa extends BaseRegister
{
    // 1. Beritahu Filament model yang digunakan
    public function getModel(): string
    {
        return User::class;
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(User::class) // <-- Tambahkan ini agar form tahu model utamanya
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->extraInputAttributes(['style' => 'text-transform: capitalize;']),

                Select::make('kelase_id')
                    ->label('Kelas')
                    ->relationship('kelase', 'name') // Atau nama kolom di tabel kelases (misal 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('nomor_absen')
                    ->label('Nomor Absen')
                    ->options(
                        collect(range(1, 35))
                            ->mapWithKeys(fn($number) => [
                                sprintf('%02d', $number) => sprintf('%02d', $number)
                            ])
                            ->toArray()
                    )
                    ->required(),

                TextInput::make('email')
                    ->label('Gmail / Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email'),
            ]);
    }

    // Mengubah alur pendaftaran
    public function register(): ?\Filament\Http\Responses\Auth\Contracts\RegistrationResponse
    {
        $data = $this->form->getState();

        // 1. Simpan data user
        $user = $this->handleRegistration($data);

        // 2. Tampilkan notifikasi sukses
        Notification::make()
            ->title('Pendaftaran Berhasil!')
            ->body('Akun Anda berhasil dibuat. Silakan login menggunakan email Anda.')
            ->success()
            ->send();

        // 3. Redirect spesifik ke halaman login panel 'app'
        $this->redirect(filament()->getPanel('app')->getLoginUrl());

        return null;
    }

    protected function handleRegistration(array $data): User
    {
        $data['role'] = 'siswa';

        // Set password default ke 'spensatajaya' dan di-hash
        $data['password'] = Hash::make('spensatajaya');

        return User::create($data);
    }
}
