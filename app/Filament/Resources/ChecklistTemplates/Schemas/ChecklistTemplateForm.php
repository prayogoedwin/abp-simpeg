<?php

namespace App\Filament\Resources\ChecklistTemplates\Schemas;

use App\Models\Instansi;
use Dom\Text;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChecklistTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([


                TextInput::make('name')
                    ->label('Nama Template')
                    ->required(),

                Select::make('instansi_id')
                    ->label('Instansi')
                    ->options(Instansi::query()->pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),


                // The Checklist Details Section
                Repeater::make('details')
                    ->relationship('details') // This links it to the HasMany method
                    ->schema([
                        Select::make('type')
                            ->options([
                                'text' => 'Text Input',
                                'number' => 'Number',
                                'date' => 'Date',

                                // For fields that have options (like select, checkbox, radio), we can store the options as a JSON string in the 'options' column. The user can input it as a comma-separated list.
                                'checkbox' => 'Checkbox',
                                'select' => 'Select',
                                'radio' => 'Radio',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),

                        TextInput::make('label')
                            ->required()
                            ->placeholder('e.g., Room Temperature'),

                        TextInput::make('options')
                            ->label('Options (pisahkan dengan koma)')
                            ->placeholder('Comma-separated options, e.g., "Low,Medium,High"')
                            ->visible(fn ($get) => in_array($get('type'), ['select', 'checkbox', 'radio'])),
                            

                        TextInput::make('value')
                            ->placeholder('Default value (optional)'),
                    ])
                    ->addActionLabel('Add Detail') // Renames the "Add to..." button
                    ->columns(3) // Layout the fields horizontally in each row
                    ->grid(1)    // Keep each detail row on its own line
                    ->collapsible() // Optional: allows shrinking rows to save space
                    ->defaultItems(1) // Starts with one row visible
            ])
            ->columns(1); // The main form has a single column layout
            ;
    }
}
