<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


        $permissions = [
            // Clients
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            'restore clients',

            // Communications
            'view communications',
            'create communications',
            'edit communications',
            'delete communications',

            // Follow-ups
            'view followups',
            'create followups',
            'edit followups',
            'delete followups',

            // Users & Settings
            'manage users',
            'manage settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'api', 
            ]);
        }


        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $salesRole = Role::firstOrCreate(['name' => 'sales_rep', 'guard_name' => 'api']);


        $adminRole->givePermissionTo(Permission::all());

        $managerRole->givePermissionTo([
            'view clients', 'create clients', 'edit clients',
            'view communications', 'create communications',
            'view followups', 'create followups',
        ]);

        $salesRole->givePermissionTo([
            'view clients', 
            'create communications', 
            'view followups',
        ]);

        
        $usersToAssign = [
            ['email' => 'admin@example.com',   'role' => $adminRole],
            ['email' => 'manager@example.com', 'role' => $managerRole],
            ['email' => 'sales@example.com',   'role' => $salesRole],
        ];

        foreach ($usersToAssign as $item) {
            $user = User::where('email', $item['email'])->first();
            if ($user && !$user->hasRole($item['role']->name)) {
                $user->assignRole($item['role']);
            }
        }
    }
}
