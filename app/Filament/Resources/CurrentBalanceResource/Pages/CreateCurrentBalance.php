<?php

namespace App\Filament\Resources\CurrentBalanceResource\Pages;

use App\Filament\Resources\CurrentBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrentBalance extends CreateRecord
{
    protected static string $resource = CurrentBalanceResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect ke dashboard bawaan Filament
        return filament()->getUrl();
    }
}
