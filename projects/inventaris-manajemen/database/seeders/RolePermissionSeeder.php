<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */
        $permissions = [
            // Master Data
            'master.view',
            'master.create',
            'master.edit',
            'master.delete',

            // Inventaris
            'inventaris.view',
            'inventaris.create',
            'inventaris.edit',
            'inventaris.delete',
            'inventaris.mutasi',
            'inventaris.capitalize',
            'inventaris.writeoff',

            // Penyusutan
            'penyusutan.view',
            'penyusutan.calculate',
            'penyusutan.approve',
            'penyusutan.close',
            'penyusutan.reopen',

            // Integration Center
            'integration.view',
            'integration.retry',
            'integration.manage',

            // Laporan
            'laporan.view',
            'laporan.export',

            // User & Role Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Audit Trail
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        // Super Admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Akunting Maker (Staff) — input & calculate, no approve
        $akuntingMaker = Role::firstOrCreate(['name' => 'Akunting Maker']);
        $akuntingMaker->givePermissionTo([
            'master.view',
            'inventaris.view', 'inventaris.create', 'inventaris.edit', 'inventaris.delete',
            'inventaris.mutasi', 'inventaris.capitalize', 'inventaris.writeoff',
            'penyusutan.view', 'penyusutan.calculate',
            'integration.view',
            'laporan.view', 'laporan.export',
        ]);

        // Akunting Checker (Manager) — review & approve, no input
        $akuntingChecker = Role::firstOrCreate(['name' => 'Akunting Checker']);
        $akuntingChecker->givePermissionTo([
            'master.view',
            'inventaris.view',
            'penyusutan.view', 'penyusutan.approve', 'penyusutan.close',
            'integration.view',
            'laporan.view', 'laporan.export',
        ]);

        // Audit — read-only + audit trail
        $audit = Role::firstOrCreate(['name' => 'Audit']);
        $audit->givePermissionTo([
            'master.view',
            'inventaris.view',
            'penyusutan.view',
            'integration.view',
            'laporan.view', 'laporan.export',
            'audit.view',
        ]);

        // Cabang — read-only (branch-scoped)
        $cabang = Role::firstOrCreate(['name' => 'Cabang']);
        $cabang->givePermissionTo([
            'inventaris.view',
            'laporan.view',
        ]);
    }
}
