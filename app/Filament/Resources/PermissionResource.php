<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PermissionResource\Pages\ListPermissions;
use App\Filament\Resources\PermissionResource\Pages\EditPermission;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\PermissionResource\Pages;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
   
     protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

     //setting letak grup menu
    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 3; // Urutan setelah Kategori

    // Label
    protected static ?string $modelLabel = 'Permission Admin';
    protected static ?string $pluralModelLabel = 'Permission Admin';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->can('view permissions');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->can('view permissions');
    }

    public static function canView(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('view permissions');
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->can('create permissions');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('edit permissions');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->can('delete permissions');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->can('delete permissions');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Module Name (e.g., users, instansis)')
                    ->helperText('Akan otomatis membuat: view, create, edit, delete')
                    ->required(),

                TextInput::make('guard_name')
                    ->default('web')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('guard_name')->label('Guard name'),

                // Kolom Menu
                TextColumn::make('menu')
                    ->label('Menu')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Ambil kata terakhir dari nama permission
                        return ucfirst(last(explode(' ', $record->name)));
                    }),

                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('menu')
                    ->label('Menu')
                    ->options(function () {
                        return Permission::all()
                            ->map(function ($perm) {
                                return ucfirst(last(explode(' ', $perm->name)));
                            })
                            ->unique()
                            ->sort()
                            ->mapWithKeys(fn($menu) => [$menu => $menu]);
                    })
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->where('name', 'like', "%{$data['value']}");
                        }
                    })
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
