<?php

namespace App\Filament\Resources\JenisPegawais\Pages;

use App\Filament\Resources\JenisPegawais\JenisPegawaiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJenisPegawai extends EditRecord
{
    protected static string $resource = JenisPegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
