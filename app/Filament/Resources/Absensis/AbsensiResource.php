<?php

namespace App\Filament\Resources\Absensis;

use App\Filament\Resources\Absensis\Pages\ListAbsensis;
use App\Filament\Resources\Absensis\Pages\CreateAbsensi;
use App\Filament\Resources\Absensis\Pages\EditAbsensi;
use App\Filament\Resources\Absensis\Pages\RekapAbsensi;
use App\Models\Absensi;
use App\Models\Member;
use App\Models\Instansi;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Kepegawaian';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Absensi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pegawai')
                    ->schema([
                        Select::make('member_id')
                            ->label('Pegawai')
                            ->options(Member::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $member = Member::find($state);
                                    $set('instansi_id', $member?->instansi_id);
                                }
                            }),

                        Select::make('instansi_id')
                            ->label('Instansi')
                            ->options(Instansi::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                    ])->columns(3),

                Section::make('Jadwal')
                    ->schema([
                        TimePicker::make('jadwal_jam_masuk')
                            ->label('Jadwal Jam Masuk')
                            ->seconds(false),

                        TimePicker::make('jadwal_jam_pulang')
                            ->label('Jadwal Jam Pulang')
                            ->seconds(false),
                    ])->columns(2),

                Section::make('Kehadiran Aktual')
                    ->schema([
                        TimePicker::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->seconds(false),

                        TimePicker::make('jam_pulang')
                            ->label('Jam Pulang')
                            ->seconds(false),

                        Select::make('status')
                            ->label('Status')
                            ->options(Absensi::STATUS_OPTIONS)
                            ->required(),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Lokasi Masuk')
                    ->schema([
                        TextInput::make('lat_masuk')
                            ->label('Latitude')
                            ->numeric(),

                        TextInput::make('lng_masuk')
                            ->label('Longitude')
                            ->numeric(),

                        TextInput::make('jarak_lokasi_masuk')
                            ->label('Jarak (meter)')
                            ->numeric()
                            ->disabled(),
                    ])->columns(3),

                Section::make('Lokasi Pulang')
                    ->schema([
                        TextInput::make('lat_pulang')
                            ->label('Latitude')
                            ->numeric(),

                        TextInput::make('lng_pulang')
                            ->label('Longitude')
                            ->numeric(),

                        TextInput::make('jarak_lokasi_pulang')
                            ->label('Jarak (meter)')
                            ->numeric()
                            ->disabled(),
                    ])->columns(3),

                Section::make('Foto')
                    ->schema([
                        FileUpload::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->image()
                            ->directory('absensi/masuk'),

                        FileUpload::make('foto_pulang')
                            ->label('Foto Pulang')
                            ->image()
                            ->directory('absensi/pulang'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('member.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('instansi.nama')
                    ->label('Instansi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jam_masuk')
                    ->label('Masuk')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('jam_pulang')
                    ->label('Pulang')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => Absensi::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (?string $state): string => Absensi::STATUS_OPTIONS[$state] ?? '-'),

                TextColumn::make('telat_menit')
                    ->label('Telat')
                    ->suffix(' menit')
                    ->placeholder('-'),

                TextColumn::make('jarak_lokasi_masuk')
                    ->label('Jarak Masuk')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) . ' m' : '-'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('instansi_id')
                    ->label('Instansi')
                    ->options(Instansi::query()->pluck('nama', 'id')),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Absensi::STATUS_OPTIONS),

                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'], fn ($q, $date) => $q->whereDate('tanggal', '<=', $date));
                    }),

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
            'index' => ListAbsensis::route('/'),
            'create' => CreateAbsensi::route('/create'),
            'edit' => EditAbsensi::route('/{record}/edit'),
            'rekap' => RekapAbsensi::route('/rekap'),
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
        return (string) static::getModel()::whereDate('tanggal', today())->count();
    }
}