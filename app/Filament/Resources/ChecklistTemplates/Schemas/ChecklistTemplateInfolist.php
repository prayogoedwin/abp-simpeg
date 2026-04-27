<?php

namespace App\Filament\Resources\ChecklistTemplates\Schemas;

use Dom\Text;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ChecklistTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nama Template'),
                TextEntry::make('instansi.nama')
                    ->label('Instansi'),

                // Ganti Repeater dengan RepeatableEntry
                RepeatableEntry::make('details')
                    ->label('Detail Checklist')
                    ->schema([
                        TextEntry::make('label')
                            ->label('Label'),
                        TextEntry::make('type')
                            ->label('Tipe')
                            ->badge(), // Opsional: dibuat seperti badge agar menarik
                        TextEntry::make('options')
                            ->label('Opsi')
                            ->placeholder('-'),
                        TextEntry::make('value')
                            ->label('Nilai Default')
                            ->placeholder('-'),
                    ])
                    ->columns(4) // Menampilkan field sejajar ke samping dalam setiap baris
                    ->grid(1),   // Tetap 1 baris per detail data


                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
