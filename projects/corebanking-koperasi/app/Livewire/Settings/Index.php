<?php

namespace App\Livewire\Settings;

use App\Models\ApprovalConfig;
use App\Models\Menu;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;

class Index extends Component
{
    use LogsActivity, WithLogout;

    public $modules = [];
    public $configs = []; // [module_key][action]['is_active']
    public $roles = [];

    // Modal state
    public $showEditModal = false;
    public $selectedModuleKey = '';
    public $selectedModuleName = '';

    // Structured role editing
    public $editingConfigs = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->roles = \Spatie\Permission\Models\Role::all();

        $this->modules = [
            // ─── MANAGEMENT ────────────────────────────────────────
            ['key' => 'users', 'group' => 'Manajemen Sistem', 'name' => 'Manajemen User'],
            ['key' => 'roles', 'group' => 'Manajemen Sistem', 'name' => 'Role & Permission'],
            ['key' => 'branches', 'group' => 'Manajemen Sistem', 'name' => 'Manajemen Cabang'],
            ['key' => 'companies', 'group' => 'Manajemen Sistem', 'name' => 'Manajemen Perusahaan'],
            ['key' => 'marketing-masters', 'group' => 'Manajemen Sistem', 'name' => 'Marketing Master'],
            ['key' => 'reports.index', 'group' => 'Manajemen Sistem', 'name' => 'Pusat Laporan'],
            ['key' => 'menus', 'group' => 'Manajemen Sistem', 'name' => 'Manajemen Menu'],
            ['key' => 'audit-logs', 'group' => 'Manajemen Sistem', 'name' => 'Audit Log'],
            ['key' => 'approvals.settings', 'group' => 'Manajemen Sistem', 'name' => 'Pengaturan Persetujuan'],
            ['key' => 'approvals.inbox', 'group' => 'Manajemen Sistem', 'name' => 'Daftar Persetujuan'],

            // ─── MASTER DATA WILAYAH ───────────────────────────────
            ['key' => 'provinces', 'group' => 'Master Wilayah', 'name' => 'Provinsi'],
            ['key' => 'cities', 'group' => 'Master Wilayah', 'name' => 'Kota/Kabupaten'],
            ['key' => 'districts', 'group' => 'Master Wilayah', 'name' => 'Kecamatan'],
            ['key' => 'subdistricts', 'group' => 'Master Wilayah', 'name' => 'Kelurahan'],

            // ─── MASTER PRODUK ─────────────────────────────────────
            ['key' => 'saving-products', 'group' => 'Master Produk', 'name' => 'Produk Simpanan'],
            ['key' => 'deposit-products', 'group' => 'Master Produk', 'name' => 'Produk Simpanan Berjangka'],
            ['key' => 'loan-products', 'group' => 'Master Produk', 'name' => 'Produk Pinjaman'],

            // ─── CIF ───────────────────────────────────────────────
            ['key' => 'cifs.inquiry', 'group' => 'Manajemen CIF', 'name' => 'Inquiry CIF'],
            ['key' => 'cifs.create', 'group' => 'Manajemen CIF', 'name' => 'Registrasi CIF Baru'],
            ['key' => 'cifs.update', 'group' => 'Manajemen CIF', 'name' => 'Ubah Data CIF'],
            ['key' => 'cifs.inactive', 'group' => 'Manajemen CIF', 'name' => 'Nonaktifkan CIF'],
            ['key' => 'cifs.block', 'group' => 'Manajemen CIF', 'name' => 'Blokir CIF'],
            ['key' => 'cifs.reactivate', 'group' => 'Manajemen CIF', 'name' => 'Reaktivasi CIF'],
            ['key' => 'cifs.mutation', 'group' => 'Manajemen CIF', 'name' => 'Mutasi Cabang CIF'],
            ['key' => 'mobile-access.index', 'group' => 'Manajemen CIF', 'name' => 'Akses Mobile Banking'],

            // ─── SIMPANAN ──────────────────────────────────────────
            ['key' => 'savings.inquiry', 'group' => 'Layanan Simpanan', 'name' => 'Inquiry Simpanan'],
            ['key' => 'savings.create', 'group' => 'Layanan Simpanan', 'name' => 'Buka Rekening Simpanan'],
            ['key' => 'savings.deposit', 'group' => 'Layanan Simpanan', 'name' => 'Setoran Simpanan'],
            ['key' => 'savings.withdrawal', 'group' => 'Layanan Simpanan', 'name' => 'Penarikan Simpanan'],
            ['key' => 'savings.transfer', 'group' => 'Layanan Simpanan', 'name' => 'Transfer Antar Rekening'],
            ['key' => 'savings.block', 'group' => 'Layanan Simpanan', 'name' => 'Blokir Saldo Simpanan'],
            ['key' => 'savings.unblock', 'group' => 'Layanan Simpanan', 'name' => 'Buka Blokir Saldo'],
            ['key' => 'savings.reversal', 'group' => 'Layanan Simpanan', 'name' => 'Reversal Transaksi Simpanan'],
            ['key' => 'savings.print-book', 'group' => 'Layanan Simpanan', 'name' => 'Cetak Buku Tabungan'],
            ['key' => 'savings.print-slip', 'group' => 'Layanan Simpanan', 'name' => 'Cetak Slip Transaksi'],
            ['key' => 'savings.close', 'group' => 'Layanan Simpanan', 'name' => 'Penutupan Rekening Simpanan'],
            ['key' => 'savings.dormant', 'group' => 'Layanan Simpanan', 'name' => 'Ubah Status Dormant'],
            ['key' => 'savings.reactivate', 'group' => 'Layanan Simpanan', 'name' => 'Reaktivasi Rekening Simpanan'],
            ['key' => 'savings.distribution', 'group' => 'Layanan Simpanan', 'name' => 'Distribusi Dana Simpanan'],

            // ─── SIMPANAN BERJANGKA ────────────────────────────────
            ['key' => 'deposits.inquiry', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Inquiry Simpanan Berjangka'],
            ['key' => 'deposits.placement', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Penempatan Simpanan Berjangka Baru'],
            ['key' => 'deposits.modification', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Perubahan Data Simpanan Berjangka'],
            ['key' => 'deposits.withdrawal', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Pencairan Simpanan Berjangka'],
            ['key' => 'deposits.print-bilyet', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Cetak Bilyet Simpanan Berjangka'],
            ['key' => 'deposits.simulation', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Simulasi Simpanan Berjangka'],
            ['key' => 'deposit-bilyets', 'group' => 'Layanan Simpanan Berjangka', 'name' => 'Manajemen Bilyet Simpanan Berjangka'],

            // ─── PINJAMAN ─────────────────────────────────────────
            ['key' => 'loans.inquiry', 'group' => 'Layanan Pinjaman', 'name' => 'Inquiry Pinjaman'],
            ['key' => 'loans.origination', 'group' => 'Layanan Pinjaman', 'name' => 'Pendaftaran Fasilitas Pinjaman'],
            ['key' => 'loans.disbursement', 'group' => 'Layanan Pinjaman', 'name' => 'Pencairan Dana Pinjaman'],
            ['key' => 'loans.repayment', 'group' => 'Layanan Pinjaman', 'name' => 'Pembayaran Angsuran'],
            ['key' => 'loans.settlement', 'group' => 'Layanan Pinjaman', 'name' => 'Pelunasan Pinjaman'],
            ['key' => 'loans.reversal', 'group' => 'Layanan Pinjaman', 'name' => 'Reversal Distribusi Pinjaman'],
            ['key' => 'loans.simulation', 'group' => 'Layanan Pinjaman', 'name' => 'Simulasi Pinjaman'],
            ['key' => 'loans.documents', 'group' => 'Layanan Pinjaman', 'name' => 'Dokumen Pendukung Pinjaman'],
            ['key' => 'loans.insurance-claims', 'group' => 'Layanan Pinjaman', 'name' => 'Klaim Asuransi Pinjaman'],

            // ─── AKUNTANSI ─────────────────────────────────────────
            ['key' => 'coas', 'group' => 'Akuntansi', 'name' => 'Bagan Akun (CoA)'],
            ['key' => 'journals', 'group' => 'Akuntansi', 'name' => 'Jurnal Umum (Manual)'],
            ['key' => 'transfers.bank', 'group' => 'Akuntansi', 'name' => 'Transaksi Antar Bank'],
            ['key' => 'ledger', 'group' => 'Akuntansi', 'name' => 'Buku Besar'],
            ['key' => 'trial-balance', 'group' => 'Akuntansi', 'name' => 'Neraca Saldo'],
            ['key' => 'tax-settings', 'group' => 'Akuntansi', 'name' => 'Pengaturan Pajak'],


            // ─── ASET & SEWA ───────────────────────────────────────
            ['key' => 'assets.inquiry', 'group' => 'Aset & Sewa', 'name' => 'Daftar Inventaris'],
            ['key' => 'assets.create', 'group' => 'Aset & Sewa', 'name' => 'Tambah Aset Baru'],
            ['key' => 'assets.update', 'group' => 'Aset & Sewa', 'name' => 'Perubahan Inventaris'],
            ['key' => 'assets.depreciation', 'group' => 'Aset & Sewa', 'name' => 'Penyusutan Aset'],
            ['key' => 'assets.categories', 'group' => 'Aset & Sewa', 'name' => 'Kategori Aset'],
            ['key' => 'asset-rentals.index', 'group' => 'Aset & Sewa', 'name' => 'Jasa Sewa'],
            ['key' => 'asset-rentals.payment-import', 'group' => 'Aset & Sewa', 'name' => 'Pembayaran Sewa Aset Masal'],
            ['key' => 'rekanan.index', 'group' => 'Aset & Sewa', 'name' => 'Master Rekanan'],

            // ─── MANAJEMEN SHU ─────────────────────────────────────
            ['key' => 'shu.master', 'group' => 'Manajemen SHU', 'name' => 'Master Kriteria SHU'],
            ['key' => 'shu.distributions', 'group' => 'Manajemen SHU', 'name' => 'Distribusi SHU'],
        ];

        $this->loadConfigs();
        $this->logActivity('NAVIGATE', 'Pengaturan Persetujuan');
    }

    public function loadConfigs()
    {
        $existing = ApprovalConfig::all();
        foreach ($this->modules as $mod) {
            $actions = $this->getActionsForModule($mod['key']);
            foreach ($actions as $action) {
                $config = $existing->where('module_key', $mod['key'])->where('action', $action)->first();
                $this->configs[$mod['key']][$action] = [
                    'is_active' => $config ? $config->is_active : false,
                    'roles' => $config ? ($config->authorized_roles ?? []) : []
                ];
            }
        }
    }

    private function getActionsForModule($moduleKey)
    {
        // Sub-menu CIF
        if ($moduleKey === 'cifs.inquiry')
            return ['EXPORT'];
        if ($moduleKey === 'cifs.create')
            return ['CREATE'];
        if ($moduleKey === 'cifs.update')
            return ['UPDATE'];
        if ($moduleKey === 'cifs.inactive')
            return ['INACTIVATE'];
        if ($moduleKey === 'cifs.block')
            return ['BLOCK'];
        if ($moduleKey === 'cifs.reactivate')
            return ['REACTIVATE'];
        if ($moduleKey === 'cifs.mutation')
            return ['MUTATION'];
        if ($moduleKey === 'mobile-access.index')
            return ['CREATE', 'UPDATE', 'DELETE', 'RESET_PIN', 'RESET_PASSWORD', 'TOGGLE_STATUS'];

        // Sub-menu Simpanan
        if ($moduleKey === 'savings.inquiry' || $moduleKey === 'savings.print-book' || $moduleKey === 'savings.print-slip')
            return ['EXPORT'];
        if ($moduleKey === 'savings.create')
            return ['CREATE'];
        if ($moduleKey === 'savings.deposit')
            return ['DEPOSIT'];
        if ($moduleKey === 'savings.withdrawal')
            return ['WITHDRAWAL'];
        if ($moduleKey === 'savings.transfer')
            return ['TRANSFER'];
        if ($moduleKey === 'savings.block')
            return ['BLOCK'];
        if ($moduleKey === 'savings.unblock')
            return ['UNBLOCK'];
        if ($moduleKey === 'savings.reversal')
            return ['REVERSAL'];
        if ($moduleKey === 'savings.close')
            return ['CLOSE'];
        if ($moduleKey === 'savings.dormant')
            return ['DORMANT'];
        if ($moduleKey === 'savings.reactivate')
            return ['REACTIVATE'];
        if ($moduleKey === 'savings.distribution')
            return ['DISTRIBUTE'];

        // Sub-menu Deposito
        if ($moduleKey === 'deposits.inquiry' || $moduleKey === 'deposits.print-bilyet' || $moduleKey === 'deposits.simulation')
            return ['EXPORT'];
        if ($moduleKey === 'deposits.placement')
            return ['CREATE'];
        if ($moduleKey === 'deposits.modification')
            return ['UPDATE'];
        if ($moduleKey === 'deposits.withdrawal')
            return ['CLOSE'];
        if ($moduleKey === 'deposit-bilyets')
            return ['CREATE', 'UPDATE', 'DELETE'];

        // Sub-menu Kredit
        if ($moduleKey === 'loans.inquiry' || $moduleKey === 'loans.simulation')
            return ['EXPORT'];
        if ($moduleKey === 'loans.insurance-claims')
            return ['CREATE', 'UPDATE', 'DELETE'];
        if ($moduleKey === 'loans.origination')
            return ['Originate'];
        if ($moduleKey === 'loans.disbursement')
            return ['Disbursement'];
        if ($moduleKey === 'loans.repayment')
            return ['Repayment'];
        if ($moduleKey === 'loans.settlement')
            return ['Settlement'];
        if ($moduleKey === 'loans.reversal')
            return ['Reversal'];
        if ($moduleKey === 'loans.documents')
            return ['UPLOAD', 'VERIFY', 'DELETE'];

        // Master Produk
        if (in_array($moduleKey, ['saving-products', 'deposit-products', 'loan-products'])) {
            return ['CREATE', 'UPDATE', 'DELETE'];
        }

        // Akuntansi
        if ($moduleKey === 'coas')
            return ['CREATE', 'UPDATE', 'DELETE'];
        if ($moduleKey === 'journals')
            return ['CREATE'];
        if ($moduleKey === 'ledger' || $moduleKey === 'trial-balance')
            return ['EXPORT'];
        if ($moduleKey === 'tax-settings')
            return ['UPDATE'];

        // Laporan & Log
        if ($moduleKey === 'reports.index' || $moduleKey === 'audit-logs')
            return ['EXPORT'];
            
        // Pengaturan
        if ($moduleKey === 'approvals.settings' || $moduleKey === 'menus')
            return ['UPDATE'];
        if ($moduleKey === 'approvals.inbox')
            return ['APPROVE', 'REJECT'];

        // Aset & Sewa
        if ($moduleKey === 'assets.create')
            return ['CREATE'];
        if ($moduleKey === 'assets.update')
            return ['UPDATE'];
        if ($moduleKey === 'assets.depreciation')
            return ['EXECUTE'];
        if ($moduleKey === 'assets.categories')
            return ['CREATE', 'UPDATE', 'DELETE'];
        if ($moduleKey === 'assets.inquiry')
            return ['UPDATE', 'DELETE'];
        if ($moduleKey === 'asset-rentals.index')
            return ['CREATE', 'UPDATE', 'DELETE'];
        if ($moduleKey === 'rekanan.index')
            return ['CREATE', 'UPDATE', 'DELETE'];

        // SHU
        if ($moduleKey === 'shu.master')
            return ['CREATE', 'UPDATE', 'DELETE'];
        if ($moduleKey === 'shu.distributions')
            return ['DISTRIBUTE'];
            
        // Laporan
        if ($moduleKey === 'reports.index')
            return ['EXPORT'];

        // Default: master data & manajemen
        return ['CREATE', 'UPDATE', 'DELETE'];
    }

    public function toggle($module, $action)
    {
        $current = $this->configs[$module][$action]['is_active'];
        $this->configs[$module][$action]['is_active'] = !$current;

        ApprovalConfig::updateOrCreate(
            ['module_key' => $module, 'action' => $action],
            ['is_active' => !$current]
        );

        $this->loadConfigs();
        session()->flash('success', "Status approval berhasil diperbarui.");
    }

    public function editModule($moduleKey, $moduleName)
    {
        $this->selectedModuleKey = $moduleKey;
        $this->selectedModuleName = $moduleName;
        $this->editingConfigs = [];

        $actions = $this->getActionsForModule($moduleKey);
        foreach ($actions as $action) {
            $this->editingConfigs[$action] = $this->configs[$moduleKey][$action] ?? ['is_active' => false, 'roles' => []];
        }

        $this->showEditModal = true;
    }

    public function saveModuleConfigs()
    {
        foreach ($this->editingConfigs as $action => $data) {
            ApprovalConfig::updateOrCreate(
                ['module_key' => $this->selectedModuleKey, 'action' => $action],
                [
                    'is_active' => $data['is_active'] ?? false,
                    'authorized_roles' => $data['roles'] ?? []
                ]
            );
        }

        $this->loadConfigs();
        $this->showEditModal = false;

        $this->logActivity('UPDATE', "Memperbarui konfigurasi governance untuk modul [{$this->selectedModuleKey}]");
        session()->flash('success', "Konfigurasi governance berhasil diperbarui.");
    }

    public $user, $role;

    public function render()
    {
        return view('livewire.settings.approval-settings')->layout('layouts.app');
    }
}
