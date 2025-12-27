<?php

namespace App\Filament\Resources\Instansis;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ActionGroup;
use App\Filament\Resources\Instansis\Pages\ListInstansis;
use App\Filament\Resources\Instansis\Pages\CreateInstansi;
use App\Filament\Resources\Instansis\Pages\EditInstansi;
use App\Filament\Resources\Instansis\Pages\ViewInstansi;
use App\Models\Instansi;
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

class InstansiResource extends Resource
{
    protected static ?string $model = Instansi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Instansi';

    protected static ?string $pluralModelLabel = 'Instansi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Instansi')
                    ->schema([
                        TextInput::make('nama')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('kode')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Textarea::make('alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Lokasi')
                    ->schema([
                        TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.00000001),

                        TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.00000001),

                        TextInput::make('google_maps_link')
                            ->label('Google Maps Link')
                            ->url()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Kontak')
                    ->schema([
                        TextInput::make('telepon')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('pic_name')
                            ->label('Nama PIC')
                            ->maxLength(255),

                        TextInput::make('pic_phone')
                            ->label('Telepon PIC')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
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
                    ]),
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

                TextColumn::make('alamat')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->alamat),

                TextColumn::make('pic_name')
                    ->label('PIC')
                    ->searchable(),

                TextColumn::make('telepon')
                    ->icon('heroicon-o-phone'),

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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                ]),
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
            'index' => ListInstansis::route('/'),
            'create' => CreateInstansi::route('/create'),
            'view' => ViewInstansi::route('/{record}'),
            'edit' => EditInstansi::route('/{record}/edit'),
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