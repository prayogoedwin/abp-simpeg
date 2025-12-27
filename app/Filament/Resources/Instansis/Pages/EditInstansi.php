<?php

namespace App\Filament\Resources\Instansis\Pages;

use App\Filament\Resources\Instansis\InstansiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstansi extends EditRecord
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
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