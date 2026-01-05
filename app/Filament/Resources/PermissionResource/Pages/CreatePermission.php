<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;
use Filament\Notifications\Notification;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $moduleName = strtolower(trim($data['name']));
        $guardName = $data['guard_name'] ?? 'web';
        
        $actions = ['view', 'create', 'edit', 'delete'];
        $created = [];
        $skipped = [];

        foreach ($actions as $action) {
            $permissionName = "{$action} {$moduleName}";
            
            // Cek apakah sudah ada
            if (Permission::where('name', $permissionName)->where('guard_name', $guardName)->exists()) {
                $skipped[] = $permissionName;
                continue;
            }

            Permission::create([
                'name' => $permissionName,
                'guard_name' => $guardName,
            ]);
            $created[] = $permissionName;
        }

        // Notifikasi
        if (count($created) > 0) {
            Notification::make()
                ->title('Permissions Created')
                ->body('Created: ' . implode(', ', $created))
                ->success()
                ->send();
        }

        if (count($skipped) > 0) {
            Notification::make()
                ->title('Some Permissions Skipped')
                ->body('Already exists: ' . implode(', ', $skipped))
                ->warning()
                ->send();
        }

        // Return salah satu permission untuk redirect
        return Permission::where('name', "view {$moduleName}")->first() 
            ?? Permission::where('name', 'like', "%{$moduleName}")->first();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}