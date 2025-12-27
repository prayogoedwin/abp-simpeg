<?php

namespace App\Filament\Resources\Posisis\Pages;

use App\Filament\Resources\Posisis\PosisiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosisi extends EditRecord
{
    protected static string $resource = PosisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}