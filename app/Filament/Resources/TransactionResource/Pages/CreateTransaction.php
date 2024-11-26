<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
