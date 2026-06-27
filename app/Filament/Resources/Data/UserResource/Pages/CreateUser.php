<?php

namespace App\Filament\Resources\Data\UserResource\Pages;

use App\Filament\Resources\Data\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
