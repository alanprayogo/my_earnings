<?php

namespace App\Filament\Resources\BalanceResource\Pages;

use App\Filament\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBalance extends CreateRecord
{
    protected static string $resource = BalanceResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect ke dashboard bawaan Filament
        return filament()->getUrl();
    }
}
