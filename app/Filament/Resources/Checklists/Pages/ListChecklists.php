<?php

namespace App\Filament\Resources\Checklists\Pages;

use App\Filament\Resources\Checklists\ChecklistResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChecklists extends ListRecords
{
    protected static string $resource = ChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rekap')
                ->label('Rekap Checklist')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->url(fn () => ChecklistResource::getUrl('rekap')),
            CreateAction::make(),
        ];
    }
}
