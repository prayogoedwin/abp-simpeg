<?php

namespace App\Filament\Resources\JenisPegawais\Pages;

use App\Filament\Resources\JenisPegawais\JenisPegawaiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJenisPegawais extends ListRecords
{
    protected static string $resource = JenisPegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
