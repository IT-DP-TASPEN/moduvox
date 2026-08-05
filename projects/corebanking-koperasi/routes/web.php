<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Users\Index as UserIndex;
use App\Livewire\Roles\Index as RoleIndex;
use App\Livewire\Profile\Manage as ProfileManage;
use App\Livewire\Companies\Index as CompanyIndex;
use App\Livewire\Branches\Index as BranchIndex;
use App\Livewire\AuditLogs\Index as AuditLogIndex;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', Login::class)->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/users', UserIndex::class)->name('users.index')->middleware('permission:users.view');
    Route::get('/roles', RoleIndex::class)->name('roles.index')->middleware('permission:roles.view');
    
    // Impersonate System
    Route::get('/impersonate/leave', function() {
        if (session()->has('original_impersonator_id')) {
            $originalId = session('original_impersonator_id');
            session()->forget('original_impersonator_id');
            Auth::loginUsingId($originalId);
            return redirect()->route('users.index')->with('success', 'Berhasil kembali ke akun (Admin) asli Anda.');
        }
        return redirect()->route('dashboard');
    })->name('impersonate.leave');
    
    // Approval System
    Route::get('/system/business-date', \App\Livewire\BusinessDate\Index::class)->name('system.business-date')->middleware('permission:system.business-date');
    Route::get('/approvals/settings', \App\Livewire\Settings\Index::class)->name('approvals.settings')->middleware('permission:manage.approvals');
    Route::get('/approvals/inbox', \App\Livewire\Approvals\Index::class)->name('approvals.inbox')->middleware('permission:view.approvals');
    
    Route::get('/profile', ProfileManage::class)->name('profile');
    Route::get('/companies', CompanyIndex::class)->name('companies.index')->middleware('permission:companies.view');
    Route::get('/branches', BranchIndex::class)->name('branches.index')->middleware('permission:branches.view');
    Route::get('/logs', AuditLogIndex::class)->name('audit.logs')->middleware('permission:logs.view');
    
    // CIFs / Layanan Anggota
    Route::get('/cifs/create', \App\Livewire\Cifs\Create::class)->name('cifs.create')->middleware('permission:cifs.create');
    Route::get('/cifs/update', \App\Livewire\Cifs\Update::class)->name('cifs.update')->middleware('permission:cifs.update');
    Route::get('/cifs/inactive', \App\Livewire\Cifs\Inactive::class)->name('cifs.inactive')->middleware('permission:cifs.inactive');
    Route::get('/cifs/block', \App\Livewire\Cifs\Block::class)->name('cifs.block')->middleware('permission:cifs.block');
    Route::get('/cifs/reactivate', \App\Livewire\Cifs\Reactivate::class)->name('cifs.reactivate')->middleware('permission:cifs.reactivate');
    Route::get('/cifs/inquiry', \App\Livewire\Cifs\Inquiry::class)->name('cifs.inquiry')->middleware('permission:cifs.inquiry');
    Route::get('/cifs/mutation', \App\Livewire\Cifs\Mutation::class)->name('cifs.mutation')->middleware('permission:cifs.mutation');

    // Layanan Simpanan
    Route::get('/savings/inquiry', \App\Livewire\Savings\Inquiry::class)->name('savings.inquiry')->middleware('permission:savings.inquiry');
    Route::get('/savings/inquiry/{id}', \App\Livewire\Savings\Inquiry::class)->name('savings.inquiry.detail')->middleware('permission:savings.inquiry');
    Route::get('/savings/create', \App\Livewire\Savings\Create::class)->name('savings.create')->middleware('permission:savings.create');
    Route::get('/savings/deposit', \App\Livewire\Savings\Deposit::class)->name('savings.deposit')->middleware('permission:savings.deposit');
    Route::get('/savings/withdrawal', \App\Livewire\Savings\Withdrawal::class)->name('savings.withdrawal')->middleware('permission:savings.withdrawal');
    Route::get('/savings/transfer', \App\Livewire\Savings\Transfer::class)->name('savings.transfer')->middleware('permission:savings.transfer');
    Route::get('/savings/reversal', \App\Livewire\Savings\Reversal::class)->name('savings.reversal')->middleware('permission:savings.reversal');
    Route::get('/savings/statement', \App\Livewire\Savings\Statement::class)->name('savings.statement')->middleware('permission:savings.statement');
    Route::get('/savings/print-book', \App\Livewire\Savings\PrintBook::class)->name('savings.print-book')->middleware('permission:savings.print-book');
    Route::get('/savings/print-slip', \App\Livewire\Savings\PrintSlip::class)->name('savings.print-slip')->middleware('permission:savings.print-slip');
    Route::get('/savings/block', \App\Livewire\Savings\Block::class)->name('savings.block')->middleware('permission:savings.block');
    Route::get('/savings/unblock', \App\Livewire\Savings\Unblock::class)->name('savings.unblock')->middleware('permission:savings.unblock');
    Route::get('/savings/close', \App\Livewire\Savings\Status\Close::class)->name('savings.close')->middleware('permission:savings.close');
    Route::get('/savings/dormant', \App\Livewire\Savings\Status\Dormant::class)->name('savings.dormant')->middleware('permission:savings.dormant');
    Route::get('/savings/reactivate', \App\Livewire\Savings\Status\Reactivate::class)->name('savings.reactivate')->middleware('permission:savings.reactivate');
    Route::get('/savings/distribution', \App\Livewire\Savings\Distribution::class)->name('savings.distribution')->middleware('permission:savings.distribution');


    // Layanan Deposito
    Route::get('/deposits/inquiry', \App\Livewire\Deposits\Inquiry::class)->name('deposits.inquiry')->middleware('permission:deposits.inquiry');
    Route::get('/deposits/placement', \App\Livewire\Deposits\Placement::class)->name('deposits.placement')->middleware('permission:deposits.placement');
    Route::get('/deposits/simulation', \App\Livewire\Deposits\Simulation::class)->name('deposits.simulation')->middleware('permission:deposits.simulation');
    Route::get('/deposits/withdrawal', \App\Livewire\Deposits\Withdrawal::class)->name('deposits.withdrawal')->middleware('permission:deposits.withdrawal');
    Route::get('/deposits/modification', \App\Livewire\Deposits\Modification::class)->name('deposits.modification')->middleware('permission:deposits.modification');
    Route::get('/deposits/interest-payment', \App\Livewire\Deposits\InterestPayment::class)->name('deposits.interest-payment')->middleware('permission:deposits.interest-payment');
    Route::get('/deposits/print-bilyet', \App\Livewire\Deposits\PrintBilyet::class)->name('deposits.print-bilyet')->middleware('permission:deposits.print-bilyet');

    // Layanan Pinjaman
    Route::get('/loans/inquiry', \App\Livewire\Loans\Inquiry::class)->name('loans.inquiry')->middleware('permission:loans.inquiry');
    Route::get('/loans/origination', \App\Livewire\Loans\Origination::class)->name('loans.origination')->middleware('permission:loans.origination');
    Route::get('/loans/edit/{approvalRequest?}', \App\Livewire\Loans\Edit::class)->name('loans.edit')->middleware('permission:loans.edit');
    Route::get('/loans/disbursement', \App\Livewire\Loans\Disbursement::class)->name('loans.disbursement')->middleware('permission:loans.disbursement');
    Route::get('/loans/repayment', \App\Livewire\Loans\Repayment::class)->name('loans.repayment')->middleware('permission:loans.repayment');
    Route::get('/loans/settlement', \App\Livewire\Loans\Settlement::class)->name('loans.settlement')->middleware('permission:loans.settlement');
    Route::get('/loans/reversal', \App\Livewire\Loans\Reversal::class)->name('loans.reversal')->middleware('permission:loans.reversal');
    Route::get('/loans/simulation', \App\Livewire\Loans\Simulation::class)->name('loans.simulation')->middleware('permission:loans.simulation');
    Route::get('/loans/documents', \App\Livewire\Loans\Documents::class)->name('loans.documents')->middleware('permission:loans.documents');
    Route::get('/loans/documents/{documentId}/view', [\App\Http\Controllers\LoanDocumentController::class, 'view'])->name('loans.documents.view');
    Route::get('/loans/documents/{documentId}/download', [\App\Http\Controllers\LoanDocumentController::class, 'download'])->name('loans.documents.download');
    Route::get('/loans/insurance-claims', \App\Livewire\Loans\InsuranceClaims::class)->name('loans.insurance-claims')->middleware('permission:loans.inquiry');
    Route::get('/insurance-products/{id}/rates', \App\Livewire\InsuranceProducts\RateManager::class)->name('insurance-products.rates')->middleware('permission:loan-products.view');

    // Inventaris Kantor & Sewa
    Route::get('/assets/inquiry', \App\Livewire\Assets\Inquiry::class)->name('assets.inquiry')->middleware('permission:assets.inquiry');
    Route::get('/assets/create', \App\Livewire\Assets\Create::class)->name('assets.create')->middleware('permission:assets.create');
    Route::get('/assets/update', \App\Livewire\Assets\Update::class)->name('assets.update')->middleware('permission:assets.update|assets.inquiry.update');
    Route::get('/assets/depreciation', \App\Livewire\Assets\Depreciation::class)->name('assets.depreciation')->middleware('permission:assets.depreciation');
    Route::get('/assets/categories', \App\Livewire\Assets\Categories::class)->name('assets.categories')->middleware('permission:assets.categories');
    Route::get('/rekanan', \App\Livewire\Rekanan\Index::class)->name('rekanan.index')->middleware('permission:rekanan.index');
    Route::get('/asset-rentals', \App\Livewire\AssetRentals\Index::class)->name('asset-rentals.index')->middleware('permission:asset-rentals.index');
    Route::get('/asset-rentals/payment-import', \App\Livewire\AssetRentals\PaymentImport::class)->name('asset-rentals.payment-import')->middleware('permission:asset-rentals.payment-import');

    // Mobile Banking Access Management
    Route::get('/mobile-access', \App\Livewire\MobileAccess\Index::class)->name('mobile-access.index')->middleware('permission:mobile-access.index');

    // Report System
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index')->middleware('permission:reports.view');
    Route::get('/tax-settings', \App\Livewire\TaxSettings\Index::class)->name('tax-settings.index')->middleware('permission:tax-settings.view');

    // Manajemen SHU
    Route::get('/shu/master', \App\Livewire\Shu\MasterShu\Index::class)->name('shu.master.index')->middleware('permission:shu.master.index');
    Route::get('/shu/transactions', \App\Livewire\Shu\Transactions\Index::class)->name('shu.transactions.index')->middleware('permission:shu.transactions.index');

    // Transaksi Antar Bank
    Route::get('/transfers/bank', \App\Livewire\Transfers\BankTransfer::class)->name('transfers.bank')->middleware('permission:transfers.bank');


    // Dynamic Module Routes
    try {
        if (Schema::hasTable('menus')) {
            $dynamicMenus = \App\Models\Menu::whereNotNull('route')
                ->where('is_active', true)
                ->get();

            foreach ($dynamicMenus as $menu) {
                if (in_array($menu->route, [
                    'dashboard', 'users.index', 'roles.index', 'profile', 
                    'companies.index', 'branches.index', 'audit.logs', 'menus.index'
                ])) continue;

                $moduleName = str_replace('.index', '', $menu->route);
                $className = 'App\\Livewire\\' . Str::studly($moduleName) . '\\Index';
                $path = app_path('Livewire/' . Str::studly($moduleName) . '/Index.php');
                
                if (file_exists($path)) {
                    Route::get('/' . $moduleName, $className)->name($menu->route);
                }
            }
        }
    } catch (\Throwable $e) {}
});
