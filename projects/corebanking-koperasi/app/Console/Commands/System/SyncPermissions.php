<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:sync-permissions {--all : Sync everything from code}';

    //*Define//
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync menus and permissions based on existing Livewire components';

    /**
     * Component mapping to metadata
     */
    protected $mapping = [
        'Users' => ['name' => 'Manajemen User', 'category' => 'Management', 'icon' => 'person', 'route' => 'users.index', 'permission' => 'users.view'],
        'Companies' => ['name' => 'Manajemen Perusahaan', 'category' => 'Master Data', 'icon' => 'domain', 'route' => 'companies.index', 'permission' => 'companies.view'],
        'Branches' => ['name' => 'Manajemen Cabang', 'category' => 'Master Data', 'icon' => 'account_tree', 'route' => 'branches.index', 'permission' => 'branches.view'],
        'Roles' => ['name' => 'Manajemen Role', 'category' => 'Management', 'icon' => 'security', 'route' => 'roles.index', 'permission' => 'roles.view'],
        'AuditLogs' => ['name' => 'Audit Log', 'category' => 'System', 'icon' => 'history', 'route' => 'audit.logs', 'permission' => 'logs.view'],
        'Menus' => ['name' => 'Manajemen Menu', 'category' => 'System', 'icon' => 'menu', 'route' => 'menus.index', 'permission' => 'menus.view'],
        'Settings' => ['name' => 'Pengaturan Persetujuan', 'category' => 'System', 'icon' => 'settings_suggest', 'route' => 'approvals.settings', 'permission' => 'manage.approvals'],
        'BusinessDate' => ['name' => 'Tanggal Operasional', 'category' => 'System', 'icon' => 'event_repeat', 'route' => 'system.business-date', 'permission' => 'system.business-date'],
        'Approvals' => ['name' => 'Daftar Persetujuan', 'category' => 'System', 'icon' => 'fact_check', 'route' => 'approvals.inbox', 'permission' => 'view.approvals'],
        'MarketingMasters' => ['name' => 'Marketing Master', 'category' => 'Master Data', 'icon' => 'recent_actors', 'route' => 'marketing-masters.index', 'permission' => 'marketing-masters.view'],
        'Reports' => ['name' => 'Pusat Laporan', 'category' => 'System', 'icon' => 'assessment', 'route' => 'reports.index', 'permission' => 'reports.index'],
        'Coa' => ['name' => 'Chart of Accounts', 'category' => 'Akuntansi', 'icon' => 'account_tree', 'route' => 'coa.index', 'permission' => 'coa.view'],
        'Journals' => ['name' => 'Jurnal Umum', 'category' => 'Akuntansi', 'icon' => 'menu_book', 'route' => 'journals.index', 'permission' => 'journals.view'],
        'Ledger' => ['name' => 'Buku Besar', 'category' => 'Akuntansi', 'icon' => 'import_contacts', 'route' => 'ledger.index', 'permission' => 'ledger.view'],
        'TrialBalance' => ['name' => 'Neraca Saldo', 'category' => 'Akuntansi', 'icon' => 'balance', 'route' => 'trial-balance.index', 'permission' => 'trial-balance.view'],
        'TaxSettings' => ['name' => 'Pengaturan Pajak', 'category' => 'Akuntansi', 'icon' => 'percent', 'route' => 'tax-settings.index', 'permission' => 'tax-settings.view'],
        'SavingProducts' => ['name' => 'Produk Simpanan', 'category' => 'Master Produk', 'icon' => 'savings', 'route' => 'saving-products.index', 'permission' => 'saving-products.view'],
        'DepositProducts' => ['name' => 'Produk Simpanan Berjangka', 'category' => 'Master Produk', 'icon' => 'inventory_2', 'route' => 'deposit-products.index', 'permission' => 'deposit-products.view'],
        'DepositBilyets'  => ['name' => 'Manajemen Bilyet', 'category' => 'Simpanan Berjangka', 'icon' => 'inventory', 'route' => 'deposit-bilyets.index', 'permission' => 'deposit-bilyets.view'],
        'Cifs'            => ['name' => 'Inquiry CIF', 'category' => 'CIF', 'icon' => 'visibility', 'route' => 'cifs.inquiry', 'permission' => 'cifs.inquiry'],
        'MobileAccess'    => ['name' => 'Akses Mobile', 'category' => 'CIF', 'icon' => 'smartphone', 'route' => 'mobile-access.index', 'permission' => 'mobile-access.index'],
        'Savings'         => ['name' => 'Inquiry Simpanan', 'category' => 'Simpanan', 'icon' => 'person_search', 'route' => 'savings.inquiry', 'permission' => 'savings.inquiry'],
        'Deposits'        => ['name' => 'Inquiry Simpanan Berjangka', 'category' => 'Simpanan Berjangka', 'icon' => 'account_balance_wallet', 'route' => 'deposits.inquiry', 'permission' => 'deposits.inquiry'],
        'Loans'           => ['name' => 'Inquiry Pinjaman', 'category' => 'Pinjaman', 'icon' => 'credit_score', 'route' => 'loans.inquiry', 'permission' => 'loans.inquiry'],
        'LoanProducts'    => ['name' => 'Produk Pinjaman', 'category' => 'Master Produk', 'icon' => 'payments', 'route' => 'loan-products.index', 'permission' => 'loan-products.view'],
        'Provinces' => ['name' => 'Data Provinsi', 'category' => 'Master Data', 'icon' => 'public', 'route' => 'provinces.index', 'permission' => 'provinces.view'],
        'Cities' => ['name' => 'Data Kota/Kabupaten', 'category' => 'Master Data', 'icon' => 'location_city', 'route' => 'cities.index', 'permission' => 'cities.view'],
        'Districts' => ['name' => 'Data Kecamatan', 'category' => 'Master Data', 'icon' => 'share_location', 'route' => 'districts.index', 'permission' => 'districts.view'],
        'Subdistricts' => ['name' => 'Data Kelurahan', 'category' => 'Master Data', 'icon' => 'maps_home_work', 'route' => 'subdistricts.index', 'permission' => 'subdistricts.view'],
        // Inventaris & Sewa
        'Assets' => ['name' => 'Daftar Inventaris', 'category' => 'Aset & Sewa', 'icon' => 'inventory_2', 'route' => 'assets.inquiry', 'permission' => 'assets.inquiry'],
        'Rekanan' => ['name' => 'Master Rekanan', 'category' => 'Aset & Sewa', 'icon' => 'handshake', 'route' => 'rekanan.index', 'permission' => 'rekanan.index'],
        'AssetRentals' => ['name' => 'Jasa Sewa', 'category' => 'Aset & Sewa', 'icon' => 'receipt_long', 'route' => 'asset-rentals.index', 'permission' => 'asset-rentals.index'],
        'AssetRentalPaymentImport' => ['name' => 'Pembayaran Sewa Aset Masal', 'category' => 'Aset & Sewa', 'icon' => 'upload_file', 'route' => 'asset-rentals.payment-import', 'permission' => 'asset-rentals.payment-import'],
        // Asuransi
        'InsuranceProviders' => ['name' => 'Provider Asuransi', 'category' => 'Asuransi', 'icon' => 'domain_add', 'route' => 'insurance-providers.index', 'permission' => 'insurance-providers.index'],
        'InsuranceProducts' => ['name' => 'Produk Asuransi', 'category' => 'Asuransi', 'icon' => 'add_task', 'route' => 'insurance-products.index', 'permission' => 'insurance-products.index'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning Livewire components...');

        $livewirePath = app_path('Livewire');
        $directories = File::directories($livewirePath);

        $adminRole = Role::findOrCreate('admin');
        $syncedPermissions = [];
        $menuEntries = [];

        foreach ($directories as $directory) {
            $folderName = basename($directory);

            // Skip Auth and Profile if they don't follow the menu pattern
            if (in_array($folderName, ['Auth', 'Profile', 'PostalCodes'])) {
                continue;
            }

            if ($folderName === 'Shu') {
                $this->syncShuComponents($directory, $adminRole, $syncedPermissions, $menuEntries);
                continue;
            }

            // Check if Index.php or Inquiry.php exists in the subdirectory
            if (File::exists($directory . '/Index.php') || File::exists($directory . '/Inquiry.php')) {
                $this->comment("Found main component: {$folderName}");

                $config = $this->mapping[$folderName] ?? [
                    'name' => Str::title(Str::snake($folderName, ' ')),
                    'category' => 'General',
                    'icon' => 'link',
                    'route' => Str::kebab($folderName) . '.index',
                    'permission' => Str::kebab($folderName) . '.view'
                ];

                // Create/Update main menu
                $menuOrder = $this->getMenuOrder(Str::kebab($folderName), $config['name']);
                $menu = Menu::updateOrCreate(
                    ['permission' => $config['permission']],
                    [
                        'code' => $config['permission'],
                        'name' => $config['name'],
                        'icon' => $config['icon'],
                        'route' => $config['route'],
                        'category' => $config['category'],
                        'order' => $menuOrder,
                        'is_active' => true,
                    ]
                );

                // Register main permission
                $permission = Permission::findOrCreate($config['permission']);
                $adminRole->givePermissionTo($permission);
                $syncedPermissions[] = $config['permission'];

                // SPECIAL HANDLING for modules with sub-menu components (recursive scan)
                if (in_array($folderName, ['Savings', 'Cifs', 'Deposits', 'Loans', 'Assets', 'AssetRentals'])) {
                    $this->syncSubComponents($directory, $folderName, $config['category'], $adminRole, $syncedPermissions);
                }

                // Also generate basic extra permissions for common modules
                if (!in_array($folderName, ['Savings', 'Cifs', 'Deposits', 'Loans', 'Assets'])) {
                    foreach (['create', 'update', 'delete'] as $action) {
                        $extraPerm = Str::kebab($folderName) . '.' . $action;
                        Permission::findOrCreate($extraPerm);
                        $adminRole->givePermissionTo($extraPerm);
                        $syncedPermissions[] = $extraPerm;
                    }
                }

                // Collect for config generation
                $sectionKey = Str::snake($config['category']);
                $menuEntries[$sectionKey][$config['name']] = [
                    'icon' => $config['icon'],
                    'route' => $config['route'],
                    'permission' => $config['permission'],
                    'roles' => ['admin']
                ];
            }
        }

        // Handle Cleanup of dead menus (optional)
        $this->cleanupMenus($syncedPermissions);

        Role::findOrCreate('admin_authorize')->givePermissionTo($syncedPermissions);

        // Sync with config/menus.php
        $this->updateMenuConfig($menuEntries);

        $this->info('Permissions and Menus synced successfully!');
        return 0;
    }

    private function syncSubComponents($directory, $folderName, $category, $adminRole, &$syncedPermissions)
    {
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $componentName = $file->getBasename('.php');
            if (in_array($componentName, ['Index', 'Inquiry']))
                continue;

            $slug = Str::kebab($componentName);
            $moduleSlug = Str::kebab($folderName);
            $permissionName = $moduleSlug . '.' . $slug;
            $routeName = $moduleSlug . '.' . $slug;

            // Register permission
            $permission = Permission::findOrCreate($permissionName);
            $adminRole->givePermissionTo($permission);
            $syncedPermissions[] = $permissionName;
            $this->syncActionPermissions($permissionName, $adminRole, $syncedPermissions);

            // Create Menu entry
            $menuOrder = $this->getMenuOrder($slug, $this->getFriendlyName($slug, $folderName));
            Menu::updateOrCreate(
                ['permission' => $permissionName],
                [
                    'code' => $permissionName,
                    'name' => $this->getFriendlyName($slug, $folderName),
                    'icon' => $this->getIconForComponent($slug, $folderName),
                    'route' => $routeName,
                    'category' => $category,
                    'order' => $menuOrder,
                    'is_active' => true,
                ]
            );

            $this->comment("  - Registered sub-permission and menu: {$permissionName}");
        }
    }

    private function syncActionPermissions(string $permissionName, Role $adminRole, array &$syncedPermissions): void
    {
        $actions = [
            'assets.inquiry' => ['update', 'delete'],
            'assets.create' => ['create'],
            'assets.update' => ['update'],
            'assets.depreciation' => ['execute'],
            'assets.categories' => ['create', 'update', 'delete'],
            'asset-rentals.index' => ['create', 'update', 'delete'],
            'rekanan.index' => ['create', 'update', 'delete'],

            'savings.inquiry' => ['export'],
            'savings.create' => ['create'],
            'savings.deposit' => ['deposit'],
            'savings.withdrawal' => ['withdrawal'],
            'savings.transfer' => ['transfer'],
            'savings.block' => ['block'],
            'savings.unblock' => ['unblock'],
            'savings.reversal' => ['reversal'],
            'savings.print-book' => ['export'],
            'savings.print-slip' => ['export'],
            'savings.close' => ['close'],
            'savings.dormant' => ['dormant'],
            'savings.reactivate' => ['reactivate'],

            'deposits.inquiry' => ['export'],
            'deposits.placement' => ['create'],
            'deposits.modification' => ['update'],
            'deposits.withdrawal' => ['close'],
            'deposits.interest-payment' => ['pay'],
            'deposits.print-bilyet' => ['export'],
            'deposits.simulation' => ['export'],

            'cifs.inquiry' => ['export'],
            'cifs.create' => ['create'],
            'cifs.update' => ['update'],
            'cifs.inactive' => ['inactive'],
            'cifs.block' => ['block'],
            'cifs.reactivate' => ['reactivate'],
            'cifs.mutation' => ['mutation'],

            'loans.inquiry' => ['export'],
            'loans.origination' => ['originate'],
            'loans.edit' => ['update'],
            'loans.disbursement' => ['disbursement'],
            'loans.repayment' => ['repayment'],
            'loans.settlement' => ['settlement'],
            'loans.reversal' => ['reversal'],
            'loans.simulation' => ['export'],
            'loans.documents' => ['upload', 'verify', 'delete'],
            'loans.insurance-claims' => ['create', 'update', 'delete'],
        ];

        foreach ($actions[$permissionName] ?? [] as $action) {
            $actionPermission = "{$permissionName}.{$action}";
            Permission::findOrCreate($actionPermission);
            $adminRole->givePermissionTo($actionPermission);
            $syncedPermissions[] = $actionPermission;
        }
    }

    private function getFriendlyName($slug, $folderName)
    {
        $mappings = [
            'loans' => [
                'origination'  => 'Pendaftaran Pinjaman',
                'edit'         => 'Perubahan Pengajuan Pinjaman',
                'disbursement' => 'Pencairan Dana Pinjaman',
                'repayment'    => 'Pembayaran Angsuran',
                'settlement'   => 'Pelunasan Pinjaman',
                'reversal'     => 'Reversal Distribusi Pinjaman',
                'simulation'   => 'Simulasi Pinjaman',
                'inquiry'      => 'Inquiry Pinjaman',
                'documents'    => 'Dokumen Pinjaman',
                'insurance-claims' => 'Klaim Asuransi Pinjaman',
            ],
            'savings' => [
                'create' => 'Buka Rekening Baru',
                'deposit' => 'Setoran Simpanan',
                'withdrawal' => 'Penarikan Simpanan',
                'transfer' => 'Transfer Antar Rekening',
                'block' => 'Blokir Saldo',
                'unblock' => 'Buka Blokir Saldo',
                'reversal' => 'Reversal Transaksi',
                'print-book' => 'Cetak Buku Tabungan',
                'print-slip' => 'Cetak Slip Transaksi',
                'close' => 'Penutupan Rekening',
                'dormant' => 'Status Dormant',
                'reactivate' => 'Aktivasi Rekening',
                'distribution' => 'Distribusi Dana Simpanan',
            ],
            'deposits' => [
                'placement'    => 'Penempatan Simpanan Berjangka Baru',
                'simulation'   => 'Simulasi Simpanan Berjangka',
                'withdrawal'   => 'Pencairan Simpanan Berjangka',
                'modification' => 'Perubahan Simpanan Berjangka',
                'interest-payment' => 'Pembayaran Bunga Simpanan Berjangka',
                'print-bilyet' => 'Cetak Bilyet Simpanan Berjangka',
                'inquiry'      => 'Inquiry Simpanan Berjangka',
            ],
            'cifs' => [
                'create' => 'Registrasi CIF',
                'update' => 'Ubah Data CIF',
                'inactive' => 'Nonaktifkan CIF',
                'block' => 'Blokir CIF',
                'reactivate' => 'Reaktivasi CIF',
                'mutation' => 'Mutasi Cabang CIF',
            ],
            'assets' => [
                'inquiry'     => 'Daftar Inventaris',
                'create'      => 'Tambah Aset Baru',
                'update'      => 'Perubahan Inventaris',
                'depreciation' => 'Penyusutan Aset',
                'categories'  => 'Kategori Aset',
            ],
            'asset-rentals' => [
                'payment-import' => 'Pembayaran Sewa Aset Masal',
            ],
        ];

        return $mappings[Str::kebab($folderName)][$slug] ?? Str::title(str_replace('-', ' ', $slug));
    }

    private function getMenuOrder($slug, $name)
    {
        $name = strtolower($name);
        $slug = strtolower($slug);

        // Priority 1: Create / Registrasi / Pendaftaran / Buka / Tambah / Penempatan
        if (
            in_array($slug, ['create', 'origination', 'placement']) ||
            str_contains($name, 'registrasi') ||
            str_contains($name, 'pendaftaran') ||
            str_contains($name, 'penempatan') ||
            str_contains($name, 'tambah') ||
            str_contains($name, 'buka rekening')
        ) {
            return 10;
        }

        // Priority 2: Inquiry / Daftar / Master (List)
        if (
            in_array($slug, ['inquiry', 'index', 'categories']) ||
            str_contains($name, 'inquiry') ||
            str_contains($name, 'daftar') ||
            str_contains($name, 'master') ||
            str_contains($name, 'kategori') ||
            str_contains($name, 'produk') ||
            str_contains($name, 'data')
        ) {
            return 20;
        }

        // Priority 3: Transactions
        if (
            in_array($slug, ['deposit', 'withdrawal', 'transfer', 'repayment', 'disbursement']) ||
            str_contains($name, 'setoran') ||
            str_contains($name, 'penarikan') ||
            str_contains($name, 'transfer') ||
            str_contains($name, 'pencairan') ||
            str_contains($name, 'pembayaran')
        ) {
            return 30;
        }

        // Priority 4: Modifications / Others
        if (
            in_array($slug, ['update', 'modification', 'simulation']) ||
            str_contains($name, 'ubah') ||
            str_contains($name, 'simulasi') ||
            str_contains($name, 'perubahan')
        ) {
            return 40;
        }

        // Priority 8: Print
        if (str_contains($slug, 'print') || str_contains($name, 'cetak')) {
            return 80;
        }

        // Priority 9: Status Changes (Block, Close, Dormant, Inactive)
        if (
            in_array($slug, ['block', 'unblock', 'close', 'dormant', 'reactivate', 'inactive', 'mutation']) ||
            str_contains($name, 'blokir') ||
            str_contains($name, 'tutup') ||
            str_contains($name, 'nonaktif') ||
            str_contains($name, 'mutasi') ||
            str_contains($name, 'reversal')
        ) {
            return 90;
        }

        // Priority 5: Default for everything else
        return 50;
    }

    private function getIconForComponent($slug, $folderName)
    {
        if (Str::lower($folderName) === 'cifs') {
            $cifIcons = [
                'create' => 'person_add',
                'update' => 'manage_accounts',
                'inactive' => 'person_off',
                'block' => 'gpp_bad',
                'reactivate' => 'how_to_reg',
                'mutation' => 'transfer_within_a_station',
            ];
            if (isset($cifIcons[$slug])) return $cifIcons[$slug];
        }

        if (Str::lower($folderName) === 'savings') {
            $savingIcons = [
                'create' => 'add_card',
                'deposit' => 'savings',
                'withdrawal' => 'money_off',
                'transfer' => 'sync_alt',
                'block' => 'lock',
                'unblock' => 'lock_open',
                'reversal' => 'settings_backup_restore',
                'print-book' => 'menu_book',
                'print-slip' => 'receipt_long',
                'close' => 'cancel',
                'dormant' => 'bedtime',
                'reactivate' => 'published_with_changes',
                'distribution' => 'account_balance_wallet',
            ];
            if (isset($savingIcons[$slug])) return $savingIcons[$slug];
        }

        $icons = [
            'simulation'  => 'calculate',
            'origination' => 'post_add',
            'edit'        => 'edit_note',
            'disbursement' => 'request_quote',
            'repayment'   => 'payments',
            'settlement'  => 'task_alt',
            'reversal'    => 'settings_backup_restore',
            'create'      => 'add_circle',
            'update'      => 'edit',
            'delete'      => 'delete',
            'placement'   => 'add_task',
            'withdrawal'  => 'money_off',
            'modification' => 'edit_note',
            'interest-payment' => 'payments',
            'print-bilyet' => 'print',
            'inquiry'     => 'visibility',
            'depreciation' => 'trending_down',
            'documents'   => 'folder_open',
            'payment-import' => 'upload_file',
        ];

        return $icons[$slug] ?? 'link';
    }

    private function cleanupMenus($syncedPermissions)
    {
        $this->warn('Cleaning up non-existent permissions from Admin role...');

        // This is a safety measure: only keep permissions that have code support
        // Note: we might want to keep some manual permissions like dashboard.view
        $alwaysKeep = ['dashboard.view', 'logs.view', 'profile.manage', 'manage.approvals', 'view.approvals'];
        $allValidPerms = array_merge($syncedPermissions, $alwaysKeep);

        // Deactivate menus not in our valid list (unless they are generated by a table like 'cifs' or in 'saving' categories)
        Menu::whereNotIn('permission', $allValidPerms)
            ->where(function ($q) {
                $q->whereNull('table_name')
                    ->orWhere('table_name', '!=', 'cifs');
            })
            ->where('category', '!=', 'Simpanan')      // Protect Simpanan category
            ->where('category', '!=', 'CIF')           // Protect CIF category
            ->where('category', '!=', 'Simpanan Berjangka') // Protect Simpanan Berjangka category
            ->where('category', '!=', 'Pinjaman')      // Protect Pinjaman category
            ->where('category', '!=', 'Aset & Sewa')   // Protect Aset & Sewa category
            ->update(['is_active' => false]);

        // Reactivate core menus
        Menu::whereIn('permission', ['manage.approvals', 'view.approvals'])
            ->orWhereIn('category', ['Simpanan', 'CIF', 'Simpanan Berjangka', 'Pinjaman', 'Aset & Sewa'])
            ->update(['is_active' => true]);

        // DUPLICATE CLEANUP: Ensure only ONE record exists per permission
        // This is a surgical cleanup for the "Duplicate Menus" issue
        foreach ($allValidPerms as $perm) {
            $duplicates = Menu::where('permission', $perm)->orderBy('id')->get();
            if ($duplicates->count() > 1) {
                // Keep the first one, delete the rest
                $keep = $duplicates->shift();
                Menu::where('permission', $perm)->where('id', '!=', $keep->id)->delete();
            }
        }
    }

    private function updateMenuConfig($menuEntries)
    {
        $this->info('Updating config/menus.php...');

        $filePath = config_path('menus.php');

        $content = "<?php\n\nreturn [\n";

        // Map database categories to config sections
        $categoryMap = [
            'Management'          => 'categories',
            'Master Data'         => 'master_data',
            'System'              => 'system',
            'CIF'                 => 'cif',
            'Akuntansi'           => 'akuntansi',
            'Produk Layanan'      => 'produk_layanan',
            'Master Produk'       => 'master_produk',
            'Simpanan'            => 'saving',
            'Simpanan Berjangka'  => 'deposit',
            'Pinjaman'            => 'loan',
            'Aset & Sewa'         => 'aset_sewa',
            'Asuransi'            => 'asuransi',
            'Manajemen SHU'       => 'manajemen_shu',
        ];

        // Also fetch from Database for manually seeded menus like the 7 CIF menus
        $dbMenus = Menu::where('is_active', true)->whereNotNull('category')->get();
        foreach ($dbMenus as $m) {
            $section = $categoryMap[$m->category] ?? Str::snake($m->category);
            $menuEntries[$section][$m->name] = [
                'icon' => $m->icon,
                'route' => $m->route,
                'permission' => $m->permission,
                'roles' => ['admin']
            ];
        }

        foreach (['categories', 'master_data', 'master_produk', 'system', 'saving', 'deposit', 'loan', 'cif', 'akuntansi', 'produk_layanan', 'aset_sewa', 'asuransi', 'manajemen_shu'] as $configKey) {
            $content .= "    '{$configKey}' => [\n";
            if (isset($menuEntries[$configKey])) {
                foreach ($menuEntries[$configKey] as $name => $data) {
                    $content .= "        '{$name}' => [\n";
                    $content .= "            'icon' => '{$data['icon']}',\n";
                    $content .= "            'route' => '{$data['route']}',\n";
                    $content .= "            'permission' => '{$data['permission']}',\n";
                    $content .= "            'roles' => ['admin'],\n";
                    $content .= "        ],\n";
                }
            }
            // Add Dashboard specifically to categories if it's the categories section
            if ($configKey === 'categories' && !isset($menuEntries['categories']['Dashboard'])) {
                $content .= "        'Dashboard' => [\n";
                $content .= "            'icon' => 'dashboard',\n";
                $content .= "            'route' => 'dashboard',\n";
                $content .= "            'permission' => 'dashboard.view',\n";
                $content .= "            'roles' => ['admin'],\n";
                $content .= "        ],\n";
            }
            $content .= "    ],\n";
        }

        // Add placeholders for extra permissions for now
        $content .= "    'extra_permissions' => [\n";
        $content .= "        'dashboard.view' => ['admin'],\n";
        $content .= "    ],\n";
        $content .= "];\n";

        File::put($filePath, $content);
    }

    private function syncShuComponents($directory, $adminRole, &$syncedPermissions, &$menuEntries)
    {
        $components = [
            'master-shu' => [
                'code' => 'shu.master.index',
                'name' => 'Master SHU',
                'icon' => 'settings',
                'route' => 'shu.master.index',
                'category' => 'Manajemen SHU',
                'order' => 20,
            ],
            'transactions' => [
                'code' => 'shu.transactions.index',
                'name' => 'Distribusi SHU',
                'icon' => 'payments',
                'route' => 'shu.transactions.index',
                'category' => 'Manajemen SHU',
                'order' => 30,
            ]
        ];

        foreach ($components as $key => $config) {
            // Register permission
            $permission = \Spatie\Permission\Models\Permission::findOrCreate($config['code']);
            $adminRole->givePermissionTo($permission);
            $syncedPermissions[] = $config['code'];

            // Register extra permissions for approvals: CREATE, UPDATE, DELETE, DISTRIBUTE
            if ($key === 'master-shu') {
                foreach (['create', 'update', 'delete'] as $action) {
                    $extraPerm = 'shu.master.' . $action;
                    \Spatie\Permission\Models\Permission::findOrCreate($extraPerm);
                    $adminRole->givePermissionTo($extraPerm);
                    $syncedPermissions[] = $extraPerm;
                }
            } else {
                $extraPerm = 'shu.distributions.distribute';
                \Spatie\Permission\Models\Permission::findOrCreate($extraPerm);
                $adminRole->givePermissionTo($extraPerm);
                $syncedPermissions[] = $extraPerm;
            }

            // Create Menu entry
            Menu::updateOrCreate(
                ['permission' => $config['code']],
                [
                    'code' => $config['code'],
                    'name' => $config['name'],
                    'icon' => $config['icon'],
                    'route' => $config['route'],
                    'category' => $config['category'],
                    'order' => $config['order'],
                    'is_active' => true,
                ]
            );

            $this->comment("  - Registered SHU menu: {$config['code']}");

            // Collect for config generation
            $sectionKey = 'manajemen_shu';
            $menuEntries[$sectionKey][$config['name']] = [
                'icon' => $config['icon'],
                'route' => $config['route'],
                'permission' => $config['code'],
                'roles' => ['admin']
            ];
        }
    }
}
