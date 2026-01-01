<?php

namespace App\Filament\Resources\JamKerjas;

use App\Filament\Resources\JamKerjas\Pages\ListJamKerjas;
use App\Filament\Resources\JamKerjas\Pages\CreateJamKerja;
use App\Filament\Resources\JamKerjas\Pages\EditJamKerja;
use App\Models\JamKerja;
use App\Models\Instansi;
use App\Models\JenisPegawai;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JamKerjaResource extends Resource
{
    protected static ?string $model = JamKerja::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Jam Kerja';

    protected static ?string $pluralModelLabel = 'Jam Kerja';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jam Kerja')
                    ->schema([
                        Select::make('instansi_id')
                            ->label('Instansi')
                            ->options(Instansi::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('jenis_pegawai_id')
                            ->label('Jenis Pegawai')
                            ->options(JenisPegawai::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('jenis_jam_kerja')
                            ->label('Jenis Jam Kerja')
                            ->options(JamKerja::JENIS_JAM_KERJA)
                            ->default('NORMAL')
                            ->required(),
                    ])->columns(3),

                Section::make('Waktu Kerja')
                    ->schema([
                        TimePicker::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('jam_pulang')
                            ->label('Jam Pulang')
                            ->seconds(false)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('instansi.nama')
                    ->label('Instansi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenisPegawai.nama')
                    ->label('Jenis Pegawai')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_jam_kerja')
                    ->label('Jenis Jam Kerja')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NORMAL' => 'success',
                        'SHIFT 1' => 'info',
                        'SHIFT 2' => 'warning',
                        'SHIFT 3' => 'danger',
                        'LONGSHIFT' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->time('H:i')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('instansi_id')
                    ->label('Instansi')
                    ->options(Instansi::query()->pluck('nama', 'id')),

                SelectFilter::make('jenis_pegawai_id')
                    ->label('Jenis Pegawai')
                    ->options(JenisPegawai::query()->pluck('nama', 'id')),

                SelectFilter::make('jenis_jam_kerja')
                    ->label('Jenis Jam Kerja')
                    ->options(JamKerja::JENIS_JAM_KERJA),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJamKerjas::route('/'),
            'create' => CreateJamKerja::route('/create'),
            'edit' => EditJamKerja::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}