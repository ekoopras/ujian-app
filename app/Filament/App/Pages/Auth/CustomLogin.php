<?php

namespace App\Filament\App\Pages\Auth;

use App\Filament\App\Pages\ReviewUjian;
use Filament\Pages\Auth\Login;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use App\Models\SesiUjian;

class CustomLogin extends Login
{
    /**
     * 1. Mengubah struktur Form Login agar meminta Username, Password, dan Token
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),

            ])
            ->statePath('data');
    }

    /**
     * Mengganti form komponen email bawaan Filament menjadi Username
     */
    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username Siswa')
            ->placeholder('Masukkan username Anda')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    /**
     * 2. Memberitahu Laravel untuk mengambil credential berbasis 'username' (bukan email)
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
