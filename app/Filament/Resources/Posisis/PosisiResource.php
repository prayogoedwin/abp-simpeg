<?php

namespace App\Filament\Resources\Posisis;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Posisis\Pages\ListPosisis;
use App\Filament\Resources\Posisis\Pages\CreatePosisi;
use App\Filament\Resources\Posisis\Pages\EditPosisi;
use App\Models\Posisi;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PosisiResource extends Resource
{
    protected static ?string $model = Posisi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Posisi';

    protected static ?string $pluralModelLabel = 'Posisi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('nama')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('kode')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Textarea::make('deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000),

                        ToggleButtons::make('status')
                            ->options([
                                'aktif' => 'Aktif',
                                'nonaktif' => 'Nonaktif',
                            ])
                            ->icons([
                                'aktif' => 'heroicon-o-check-circle',
                                'nonaktif' => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                'aktif' => 'success',
                                'nonaktif' => 'danger',
                            ])
                            ->inline()
                            ->default('aktif'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('members_count')
                    ->label('Jml Pegawai')
                    ->counts('members')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'nonaktif' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosisis::route('/'),
            'create' => CreatePosisi::route('/create'),
            'edit' => EditPosisi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'aktif')->count();
    }
}