<?php

namespace App\Filament\Resources\JamKerjas\Pages;

use App\Filament\Resources\JamKerjas\JamKerjaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJamKerjas extends ListRecords
{
    protected static string $resource = JamKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
