<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Scooters
            ['name' => 'View Scooters', 'slug' => 'view-scooters', 'group' => 'scooters', 'description' => 'View scooters list'],
            ['name' => 'Create Scooters', 'slug' => 'create-scooters', 'group' => 'scooters', 'description' => 'Create new scooters'],
            ['name' => 'Edit Scooters', 'slug' => 'edit-scooters', 'group' => 'scooters', 'description' => 'Edit existing scooters'],
            ['name' => 'Delete Scooters', 'slug' => 'delete-scooters', 'group' => 'scooters', 'description' => 'Delete scooters'],
            ['name' => 'Lock/Unlock Scooters', 'slug' => 'lock-scooters', 'group' => 'scooters', 'description' => 'Lock or unlock scooters'],

            // Users
            ['name' => 'View Users', 'slug' => 'view-users', 'group' => 'users', 'description' => 'View users list'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'group' => 'users', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'group' => 'users', 'description' => 'Edit existing users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'group' => 'users', 'description' => 'Delete users'],
            ['name' => 'Manage User Wallet', 'slug' => 'manage-user-wallet', 'group' => 'users', 'description' => 'Manage user wallet balance'],

            // Trips
            ['name' => 'View Trips', 'slug' => 'view-trips', 'group' => 'trips', 'description' => 'View trips list'],
            ['name' => 'Create Trips', 'slug' => 'create-trips', 'group' => 'trips', 'description' => 'Create new trips'],
            ['name' => 'Edit Trips', 'slug' => 'edit-trips', 'group' => 'trips', 'description' => 'Edit existing trips'],
            ['name' => 'Complete Trips', 'slug' => 'complete-trips', 'group' => 'trips', 'description' => 'Complete trips'],
            ['name' => 'Cancel Trips', 'slug' => 'cancel-trips', 'group' => 'trips', 'description' => 'Cancel trips'],

            // Geo Zones
            ['name' => 'View Geo Zones', 'slug' => 'view-geo-zones', 'group' => 'geo-zones', 'description' => 'View geo zones'],
            ['name' => 'Create Geo Zones', 'slug' => 'create-geo-zones', 'group' => 'geo-zones', 'description' => 'Create geo zones'],
            ['name' => 'Edit Geo Zones', 'slug' => 'edit-geo-zones', 'group' => 'geo-zones', 'description' => 'Edit geo zones'],
            ['name' => 'Delete Geo Zones', 'slug' => 'delete-geo-zones', 'group' => 'geo-zones', 'description' => 'Delete geo zones'],

            // Penalties
            ['name' => 'View Penalties', 'slug' => 'view-penalties', 'group' => 'penalties', 'description' => 'View penalties'],
            ['name' => 'Create Penalties', 'slug' => 'create-penalties', 'group' => 'penalties', 'description' => 'Create penalties'],
            ['name' => 'Edit Penalties', 'slug' => 'edit-penalties', 'group' => 'penalties', 'description' => 'Edit penalties'],
            ['name' => 'Mark Penalties as Paid', 'slug' => 'mark-penalties-paid', 'group' => 'penalties', 'description' => 'Mark penalties as paid'],

            // Coupons
            ['name' => 'View Coupons', 'slug' => 'view-coupons', 'group' => 'coupons', 'description' => 'View coupons'],
            ['name' => 'Create Coupons', 'slug' => 'create-coupons', 'group' => 'coupons', 'description' => 'Create coupons'],
            ['name' => 'Edit Coupons', 'slug' => 'edit-coupons', 'group' => 'coupons', 'description' => 'Edit coupons'],
            ['name' => 'Delete Coupons', 'slug' => 'delete-coupons', 'group' => 'coupons', 'description' => 'Delete coupons'],

            // Subscriptions
            ['name' => 'View Subscriptions', 'slug' => 'view-subscriptions', 'group' => 'subscriptions', 'description' => 'View subscriptions'],
            ['name' => 'Create Subscriptions', 'slug' => 'create-subscriptions', 'group' => 'subscriptions', 'description' => 'Create subscriptions'],
            ['name' => 'Edit Subscriptions', 'slug' => 'edit-subscriptions', 'group' => 'subscriptions', 'description' => 'Edit subscriptions'],

            // Maintenance
            ['name' => 'View Maintenance', 'slug' => 'view-maintenance', 'group' => 'maintenance', 'description' => 'View maintenance records'],
            ['name' => 'Create Maintenance', 'slug' => 'create-maintenance', 'group' => 'maintenance', 'description' => 'Create maintenance records'],
            ['name' => 'Edit Maintenance', 'slug' => 'edit-maintenance', 'group' => 'maintenance', 'description' => 'Edit maintenance records'],

            // Wallet
            ['name' => 'View Wallet Transactions', 'slug' => 'view-wallet', 'group' => 'wallet', 'description' => 'View wallet transactions'],
            ['name' => 'Manage Wallet', 'slug' => 'manage-wallet', 'group' => 'wallet', 'description' => 'Manage wallet transactions'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'view-reports', 'group' => 'reports', 'description' => 'View reports and statistics'],

            // Roles & Permissions
            ['name' => 'View Roles', 'slug' => 'view-roles', 'group' => 'roles', 'description' => 'View roles'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'group' => 'roles', 'description' => 'Create, edit, and delete roles'],
            ['name' => 'View Permissions', 'slug' => 'view-permissions', 'group' => 'roles', 'description' => 'View permissions'],
            ['name' => 'Manage Permissions', 'slug' => 'manage-permissions', 'group' => 'roles', 'description' => 'Create, edit, and delete permissions'],

            // Scooter Logs
            ['name' => 'View Scooter Logs', 'slug' => 'view-scooter-logs', 'group' => 'logs', 'description' => 'View scooter logs'],
            ['name' => 'Resolve Logs', 'slug' => 'resolve-logs', 'group' => 'logs', 'description' => 'Resolve scooter log issues'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full access to all features',
                'is_active' => true,
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage operations and view reports',
                'is_active' => true,
            ]
        );

        $operatorRole = Role::firstOrCreate(
            ['slug' => 'operator'],
            [
                'name' => 'Operator',
                'description' => 'Can manage daily operations',
                'is_active' => true,
            ]
        );

        // Assign all permissions to admin
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Assign limited permissions to manager
        $managerPermissions = Permission::whereIn('slug', [
            'view-scooters', 'edit-scooters', 'lock-scooters',
            'view-users', 'edit-users', 'manage-user-wallet',
            'view-trips', 'edit-trips', 'complete-trips', 'cancel-trips',
            'view-geo-zones', 'create-geo-zones', 'edit-geo-zones',
            'view-penalties', 'create-penalties', 'edit-penalties', 'mark-penalties-paid',
            'view-coupons', 'create-coupons', 'edit-coupons',
            'view-subscriptions', 'create-subscriptions', 'edit-subscriptions',
            'view-maintenance', 'create-maintenance', 'edit-maintenance',
            'view-wallet', 'manage-wallet',
            'view-reports',
            'view-scooter-logs', 'resolve-logs',
        ])->pluck('id');
        $managerRole->permissions()->sync($managerPermissions);

        // Assign basic permissions to operator
        $operatorPermissions = Permission::whereIn('slug', [
            'view-scooters', 'lock-scooters',
            'view-users',
            'view-trips', 'complete-trips', 'cancel-trips',
            'view-penalties', 'mark-penalties-paid',
            'view-maintenance', 'create-maintenance',
            'view-wallet',
            'view-scooter-logs',
        ])->pluck('id');
        $operatorRole->permissions()->sync($operatorPermissions);
    }
}
