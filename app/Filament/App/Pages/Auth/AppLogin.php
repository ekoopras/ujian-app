<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Pages\Page;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class AppLogin extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();

            if (! filament()->auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
                $this->throwFailureValidationException();
            }

            session()->regenerate();

            return app(LoginResponse::class);
        } catch (TooManyRequestsException $exception) {
            // Abaikan batasan rate limit
            return parent::authenticate();
        }
    }
}
