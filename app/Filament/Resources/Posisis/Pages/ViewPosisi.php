<?php

namespace App\Filament\Resources\Posisis\Pages;

use App\Filament\Resources\Posisis\PosisiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPosisi extends ViewRecord
{
    protected static string $resource = PosisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
