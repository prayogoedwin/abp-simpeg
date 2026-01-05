<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BannerResource\Pages\ListBanners;
use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

        //setting letak grup menu
    protected static string | \UnitEnum | null $navigationGroup = 'Web Setting';
    protected static ?int $navigationSort = 1; // Urutan setelah Kategori

    // Label
    protected static ?string $modelLabel = 'Banner';
    protected static ?string $pluralModelLabel = 'Banner';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->can('view banners');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->can('view banners');
    }

    public static function canView(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('view banners');
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->can('create banners');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('edit banners');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('delete banners');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->can('delete banners');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('judul')->required(),
                 Toggle::make('status')
                        ->label('Status')
                        ->inline(false),
                FileUpload::make('foto')
                    ->label('Banner (1280px X 400px)')
                    ->disk('public') // PASTIKAN INI ADA
                    ->image()
                    ->directory('banner') // Folder penyimpanan
                    ->preserveFilenames(false) // <-- Biar nama file diacak (hash)
                    ->maxSize(2048) // 2MB
                    // ->imageResizeMode('cover')
                    // ->imageCropAspectRatio('16:9')
                    ->columnSpanFull()
                    ->required(fn (string $context): bool => $context === 'create') // Hanya required saat create
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 ImageColumn::make('foto')
                    ->label('Banner')
                    ->disk('public')
                    ->height(180),
                TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                 BadgeColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => match ($state) {
                    0 => 'Nonaktif',
                    1 => 'Aktif',
                    2 => 'Selesai',
                    default => 'Unknown',
                })
                ->colors([
                    'danger' => 0,
                    'success' => 1,
                    'warning' => 2,
                ])
                ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
