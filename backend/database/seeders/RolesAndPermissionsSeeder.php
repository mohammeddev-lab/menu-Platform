<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::create(['name' => 'manage plans']);
        Permission::create(['name' => 'manage restaurants']);
        Permission::create(['name' => 'view platform analytics']);
        Permission::create(['name' => 'view system settings']);
        Permission::create(['name' => 'view activity logs']);

        Permission::create(['name' => 'manage menu']);
        Permission::create(['name' => 'manage offers']);
        Permission::create(['name' => 'manage settings']);
        Permission::create(['name' => 'view analytics']);

        // Create Roles and assign permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $restaurantAdmin = Role::create(['name' => 'restaurant-admin']);
        $restaurantAdmin->givePermissionTo([
            'manage menu',
            'manage offers',
            'manage settings',
            'view analytics',
        ]);
    }
}
