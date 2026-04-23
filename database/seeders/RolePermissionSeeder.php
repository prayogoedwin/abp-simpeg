<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Buat permission
        $permissions = [
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            'view users', 'create users', 'edit users', 'delete users',
            'view activity_log', 'create activity_log', 'edit activity_log', 'delete activity_log',
            'view members', 'create members', 'edit members', 'delete members',
            'view banners', 'create banners', 'edit banners', 'delete banners',
            'view faqs', 'create faqs', 'edit faqs', 'delete faqs',
            'view informasis', 'create informasis', 'edit informasis', 'delete informasis',
            'view beritas', 'create beritas', 'edit beritas', 'delete beritas',
            'view kategoris', 'create kategoris', 'edit kategoris', 'delete kategoris',  
            
            'view instansis', 'create instansis', 'edit instansis', 'delete instansis',
            'view posisis', 'create posisis', 'edit posisis', 'delete posisis',
        
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Buat role super_admin dan admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Kasih semua permission ke super_admin
        $superAdmin->syncPermissions(Permission::all());

        // Admin cuma punya sebagian permission
        $admin->syncPermissions(['view users', 'create users']);
    }
}
