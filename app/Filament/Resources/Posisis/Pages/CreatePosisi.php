<?php

namespace App\Filament\Resources\Posisis\Pages;

use App\Filament\Resources\Posisis\PosisiResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosisi extends CreateRecord
{
    protected static string $resource = PosisiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}