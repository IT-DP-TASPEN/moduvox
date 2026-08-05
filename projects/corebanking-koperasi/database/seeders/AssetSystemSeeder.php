<?php

namespace Database\Seeders;

use App\Models\ApprovalConfig;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssetSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Permissions
        $permissions = [
            'assets.inquiry',
            'assets.create',
            'assets.edit',
            'assets.delete',
            'assets.approve',
            'assets.categories',
            'assets.depreciation',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Assign to Roles
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        // 3. Approval Configuration
        // Make asset creation require approval
        ApprovalConfig::updateOrCreate(
            ['module_key' => 'assets.create', 'action' => 'CREATE'],
            [
                'is_active' => true,
                'authorized_roles' => ['admin_authorize']
            ]
        );
    }
}
