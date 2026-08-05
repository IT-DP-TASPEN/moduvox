<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Roles
        $adminRole = Role::findOrCreate('admin');
        $adminAuthorizeRole = Role::findOrCreate('admin_authorize');

        // 2. Load & Seed Menus from config/menus.php
        $menus = config('menus');

        $order = 0;
        $sections = [
            'categories' => 'Management',
            'master_data' => 'Master Data',
            'system' => 'System',
            'saving' => 'Saving',
            'akuntansi' => 'Akuntansi',
            'cif' => 'CIF',
            'deposit' => 'Deposit',
            'loan' => 'Loan',
            'produk_layanan' => 'Produk Layanan',
            'master_produk' => 'Master Produk',
            'aset_sewa' => 'Aset & Sewa',
            'manajemen_shu' => 'Manajemen SHU'
        ];

        foreach ($sections as $section => $label) {
            if (isset($menus[$section])) {
                foreach ($menus[$section] as $menuName => $data) {
                    \App\Models\Menu::firstOrCreate(
                        ['code' => $data['permission']],
                        [
                            'name' => $menuName,
                            'icon' => $data['icon'],
                            'route' => $data['route'],
                            'permission' => $data['permission'],
                            'category' => $label,
                            'order' => $order++,
                            'is_active' => true,
                        ]
                    );

                    Permission::findOrCreate($data['permission']);
                }
            }
        }

        // 3. Extra permissions
        if (isset($menus['extra_permissions'])) {
            foreach ($menus['extra_permissions'] as $perm => $roles) {
                Permission::findOrCreate($perm);
            }
        }

        // Sync all permissions to admin roles
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);
        $adminAuthorizeRole->syncPermissions($allPermissions);

        // 4. Default Company & Branch
        $company = Company::firstOrCreate(
            ['company_name' => 'PT Moduvox Tech ID'],
            [
                'company_code' => '0001',
                'is_active' => true,
                'address' => 'Jl. Jenderal Sudirman No. 123, Jakarta Selatan',
                'phones' => ['telepon' => '021-55566677', 'whatsapp' => '081234567890'],
                'social_media' => ['twitter' => 'koperasi_sj', 'instagram' => 'koperasisejahtera_official'],
                'description' => 'Koperasi simpan pinjam terpercaya untuk kesejahteraan bersama.',
            ]
        );

        $branch = Branch::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Cabang Utama'],
            [
                'branch_code' => '0001',
                'is_active' => true,
                'address' => 'Jl. Imam Bonjol No. 45, Jakarta Pusat',
                'phones' => ['telepon' => '021-33344455', 'whatsapp' => '089876543210'],
                'social_media' => ['twitter' => 'koperasi_sj_cabut', 'instagram' => 'koperasi_sj_utama'],
                'description' => 'Kantor pusat operasional layanan anggota.',
            ]
        );

        // 5. Users
        $adminUser = User::firstOrCreate(
            ['username' => 'admin.moduvox'],
            [
                'name' => 'Admin Moduvox',
                'email' => 'admin@moduvox.id',
                'password' => Hash::make('@DwiPrana321'),
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]
        );
        $adminUser->assignRole($adminRole);

        $authorizeUser = User::firstOrCreate(
            ['username' => 'auth.moduvox'],
            [
                'name' => 'Admin Authorize Moduvox',
                'email' => 'authorize@moduvox.id',
                'password' => Hash::make('@DwiPrana321'),
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]
        );
        $authorizeUser->assignRole([$adminRole, $adminAuthorizeRole]); // Assign both roles

        // 6. Default Approval Management
        $flow = \App\Models\ApprovalFlow::firstOrCreate(
            ['code' => 'STANDARD'],
            ['name' => 'Standard Approval Workflow']
        );

        \App\Models\ApprovalFlowStep::firstOrCreate(
            [
                'approval_flow_id' => $flow->id,
                'step_no' => 1,
            ],
            [
                'role_id' => $adminAuthorizeRole->id
            ]
        );

        // Daftar seluruh modul untuk konfigurasi persetujuan (EXACT match dengan App\Livewire\Settings\Index.php)
        $approvalModules = [
            // Manajemen Sistem
            'users' => ['CREATE', 'UPDATE', 'DELETE'],
            'roles' => ['CREATE', 'UPDATE', 'DELETE'],
            'branches' => ['CREATE', 'UPDATE', 'DELETE'],
            'companies' => ['CREATE', 'UPDATE', 'DELETE'],
            'marketing-masters' => ['CREATE', 'UPDATE', 'DELETE'],
            'reports.index' => ['EXPORT'],
            'menus' => ['UPDATE'],
            'audit-logs' => ['EXPORT'],
            'approvals.settings' => ['UPDATE'],
            'approvals.inbox' => ['APPROVE', 'REJECT'],

            // Master Wilayah
            'provinces' => ['CREATE', 'UPDATE', 'DELETE'],
            'cities' => ['CREATE', 'UPDATE', 'DELETE'],
            'districts' => ['CREATE', 'UPDATE', 'DELETE'],
            'subdistricts' => ['CREATE', 'UPDATE', 'DELETE'],

            // Master Produk
            'saving-products' => ['CREATE', 'UPDATE', 'DELETE'],
            'deposit-products' => ['CREATE', 'UPDATE', 'DELETE'],
            'loan-products' => ['CREATE', 'UPDATE', 'DELETE'],

            // CIF
            'cifs.inquiry' => ['EXPORT'],
            'cifs.create' => ['CREATE'],
            'cifs.update' => ['UPDATE'],
            'cifs.inactive' => ['INACTIVATE'],
            'cifs.block' => ['BLOCK'],
            'cifs.reactivate' => ['REACTIVATE'],
            'cifs.mutation' => ['MUTATION'],
            'mobile-access.index' => ['CREATE', 'UPDATE', 'DELETE', 'RESET_PIN', 'RESET_PASSWORD', 'TOGGLE_STATUS'],

            // Simpanan
            'savings.inquiry' => ['EXPORT'],
            'savings.create' => ['CREATE'],
            'savings.deposit' => ['DEPOSIT'],
            'savings.withdrawal' => ['WITHDRAWAL'],
            'savings.transfer' => ['TRANSFER'],
            'savings.block' => ['BLOCK'],
            'savings.unblock' => ['UNBLOCK'],
            'savings.reversal' => ['REVERSAL'],
            'savings.print-book' => ['EXPORT'],
            'savings.print-slip' => ['EXPORT'],
            'savings.close' => ['CLOSE'],
            'savings.dormant' => ['DORMANT'],
            'savings.reactivate' => ['REACTIVATE'],

            // Simpanan Berjangka
            'deposits.inquiry' => ['EXPORT'],
            'deposits.placement' => ['CREATE'],
            'deposits.modification' => ['UPDATE'],
            'deposits.withdrawal' => ['CLOSE'],
            'deposits.interest-payment' => ['PAY'],
            'deposits.print-bilyet' => ['EXPORT'],
            'deposits.simulation' => ['EXPORT'],
            'deposit-bilyets' => ['CREATE', 'UPDATE', 'DELETE'],

            // Pinjaman
            'loans.inquiry' => ['EXPORT'],
            'loans.origination' => ['Originate'],
            'loans.edit' => ['UPDATE'],
            'loans.disbursement' => ['Disbursement'],
            'loans.repayment' => ['Repayment'],
            'loans.settlement' => ['Settlement'],
            'loans.reversal' => ['Reversal'],
            'loans.simulation' => ['EXPORT'],
            'loans.documents' => ['UPLOAD', 'VERIFY', 'DELETE'],
            'loans.insurance-claims' => ['CREATE', 'UPDATE', 'DELETE'],

            // Akuntansi
            'coas' => ['CREATE', 'UPDATE', 'DELETE'],
            'journals' => ['CREATE'],
            'ledger' => ['EXPORT'],
            'trial-balance' => ['EXPORT'],
            'transfers.bank' => ['CREATE'],

            // Aset & Sewa
            'assets.inquiry' => ['UPDATE', 'DELETE'],
            'assets.create' => ['CREATE'],
            'assets.depreciation' => ['EXECUTE'],
            'assets.categories' => ['CREATE', 'UPDATE', 'DELETE'],
            'asset-rentals.index' => ['CREATE', 'UPDATE', 'DELETE'],
            'rekanan.index' => ['CREATE', 'UPDATE', 'DELETE'],

            // SHU
            'shu.master' => ['CREATE', 'UPDATE', 'DELETE'],
            'shu.distributions' => ['DISTRIBUTE'],
        ];

        foreach ($approvalModules as $module => $actions) {
            foreach ($actions as $action) {
                \App\Models\ApprovalConfig::updateOrCreate(
                    [
                        'module_key' => $module,
                        'action' => $action,
                    ],
                    [
                        'is_active' => true,
                        'authorized_roles' => ['admin_authorize']
                    ]
                );
            }
        }

        $this->call([
            SqlImportSeeder::class,
            OjkMasterCoaSeeder::class,  // Standar COA OJK/BPR (numeric-only)
            ProductSeeder::class,        // Product COA mapping (mengacu OjkMasterCoaSeeder)
            MarketingMasterSeeder::class,
            AssetCategorySeeder::class,
            AssetSystemSeeder::class,
            SystemUserSeeder::class,
            InsuranceSystemSeeder::class,
            DemoDataSeeder::class,
            MobileAccessSeeder::class,
        ]);
    }
}
