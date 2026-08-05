<?php

namespace Tests\Feature;

use App\Livewire\AssetRentals\Index as AssetRentalsIndex;
use App\Models\ActivityLog;
use App\Models\ApprovalConfig;
use App\Models\ApprovalRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetRental;
use App\Models\Branch;
use App\Models\City;
use App\Models\Cif;
use App\Models\Coa;
use App\Models\Company;
use App\Models\District;
use App\Models\Province;
use App\Models\Rekanan;
use App\Models\SavingAccount;
use App\Models\SavingDistribution;
use App\Models\SavingProduct;
use App\Models\SavingTransaction;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\SavingDistributionService;
use App\Traits\ApprovesActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionalGovernanceTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithBranch(): User
    {
        $company = Company::create(['company_name' => 'PT Test']);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'Cabang A']);

        return User::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_core_routes_are_protected_by_permission_middleware(): void
    {
        $this->assertContains('permission:users.view', app('router')->getRoutes()->getByName('users.index')->gatherMiddleware());
        $this->assertContains('permission:roles.view', app('router')->getRoutes()->getByName('roles.index')->gatherMiddleware());
        $this->assertContains('permission:manage.approvals', app('router')->getRoutes()->getByName('approvals.settings')->gatherMiddleware());
        $this->assertContains('permission:view.approvals', app('router')->getRoutes()->getByName('approvals.inbox')->gatherMiddleware());
        $this->assertContains('permission:logs.view', app('router')->getRoutes()->getByName('audit.logs')->gatherMiddleware());
        $this->assertContains('permission:mobile-access.index', app('router')->getRoutes()->getByName('mobile-access.index')->gatherMiddleware());
    }

    public function test_approval_interceptor_supports_hyphen_and_underscore_module_key_aliases(): void
    {
        $user = $this->createUserWithBranch();
        $this->actingAs($user);

        ApprovalConfig::create([
            'module_key' => 'saving-products',
            'action' => 'CREATE',
            'is_active' => true,
            'authorized_roles' => [],
        ]);

        $dummy = new class {
            use ApprovesActions;

            public function dispatch($event): void
            {
            }
        };

        $status = $dummy->interceptAction('saving_products', 'CREATE', ['name' => 'X']);

        $this->assertSame('PENDING', $status);
        $this->assertDatabaseHas('approval_requests', [
            'module_key' => 'saving_products',
            'action' => 'CREATE',
            'status' => 'PENDING',
            'requested_by' => $user->id,
        ]);
    }

    public function test_asset_rental_contract_creation_can_be_routed_to_approval_and_logged(): void
    {
        $user = $this->createUserWithBranch();
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Kendaraan']);
        $asset = Asset::create([
            'asset_code' => 'AST-001',
            'name' => 'Mobil Operasional',
            'asset_category_id' => $category->id,
            'branch_id' => $user->branch_id,
            'purchase_date' => now()->toDateString(),
            'purchase_price' => 100000000,
            'salvage_value' => 0,
            'useful_life_months' => 60,
            'depreciation_method' => 'NOMINAL',
            'depreciation_nominal' => 1000000,
            'current_book_value' => 100000000,
            'condition' => 'GOOD',
            'status' => 'ACTIVE',
            'created_by' => $user->id,
        ]);

        $rekanan = Rekanan::create([
            'rekanan_code' => 'RKN-001',
            'name' => 'PT Mitra Sewa',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        ApprovalConfig::create([
            'module_key' => 'asset-rentals.index',
            'action' => 'CREATE',
            'is_active' => true,
            'authorized_roles' => [],
        ]);

        Livewire::test(AssetRentalsIndex::class)
            ->set('asset_id', $asset->id)
            ->set('rekanan_id', $rekanan->id)
            ->set('branch_id', $user->branch_id)
            ->set('rental_start_date', now()->toDateString())
            ->set('rental_end_date', now()->addMonths(3)->toDateString())
            ->set('monthly_rate', 2500000)
            ->set('payment_due_day', 5)
            ->call('saveContract')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('asset_rentals', 0);
        $this->assertDatabaseHas('approval_requests', [
            'module_key' => 'asset-rentals.index',
            'action' => 'CREATE',
            'status' => 'PENDING',
            'requested_by' => $user->id,
        ]);
        $this->assertTrue(ActivityLog::where('action', 'CREATE_REQUEST')->exists());
        $this->assertTrue(ApprovalRequest::where('module_key', 'asset-rentals.index')->exists());
    }

    public function test_rented_asset_depreciation_follows_active_contract_duration(): void
    {
        $user = $this->createUserWithBranch();
        $category = AssetCategory::create(['name' => 'Kendaraan']);
        $asset = Asset::create([
            'asset_code' => 'AST-RENT-001',
            'name' => 'Mobil Sewa',
            'asset_category_id' => $category->id,
            'branch_id' => $user->branch_id,
            'purchase_date' => now()->toDateString(),
            'purchase_price' => 120000000,
            'salvage_value' => 0,
            'useful_life_months' => 60,
            'depreciation_method' => 'STRAIGHT_LINE',
            'depreciation_nominal' => 1000000,
            'current_book_value' => 120000000,
            'condition' => 'GOOD',
            'status' => 'RENTED',
            'created_by' => $user->id,
        ]);
        $rekanan = Rekanan::create([
            'rekanan_code' => 'RKN-RENT-001',
            'name' => 'PT Mitra Sewa',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        AssetRental::create([
            'contract_no' => 'KSW-TEST-001',
            'asset_id' => $asset->id,
            'rekanan_id' => $rekanan->id,
            'branch_id' => $user->branch_id,
            'rental_start_date' => '2026-01-15',
            'rental_end_date' => '2026-04-15',
            'monthly_rate' => 2500000,
            'payment_due_day' => 5,
            'status' => 'ACTIVE',
            'created_by' => $user->id,
        ]);

        $this->assertSame(3, $asset->activeRentalDepreciationMonths());
        $this->assertSame(40000000.0, $asset->calculateDepreciation());
    }

    public function test_saving_distribution_is_idempotent_by_distribution_number(): void
    {
        $user = $this->createUserWithBranch();
        $this->actingAs($user);

        $province = Province::create(['nama' => 'DKI Jakarta']);
        $city = City::create(['province_id' => $province->id, 'nama' => 'Jakarta Pusat', 'dati2' => 'KOTA']);
        $district = District::create(['province_id' => $province->id, 'regency_id' => $city->id, 'nama' => 'Gambir']);
        $subdistrict = Subdistrict::create([
            'province_id' => $province->id,
            'regency_id' => $city->id,
            'district_id' => $district->id,
            'nama' => 'Cideng',
        ]);

        $cashCoa = Coa::create(['coa_code' => '1001', 'name' => 'Kas', 'type' => 'ASSET', 'is_cash' => true]);
        $liabilityCoa = Coa::create(['coa_code' => '2001', 'name' => 'Tabungan', 'type' => 'LIABILITY']);

        $product = SavingProduct::create([
            'product_code' => 'S01',
            'name' => 'Simpanan Test',
            'liability_coa_id' => $liabilityCoa->id,
            'default_cash_coa_id' => $cashCoa->id,
            'created_by' => $user->id,
        ]);

        $cif = Cif::create([
            'cif_no' => 'CIF001',
            'nik' => '3171010101010001',
            'name' => 'Nasabah Test',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'MALE',
            'mother_maiden_name' => 'Ibu Test',
            'address' => 'Jl Test',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'district_id' => $district->id,
            'subdistrict_id' => $subdistrict->id,
            'phone' => '08123456789',
            'branch_id' => $user->branch_id,
            'created_by' => $user->id,
        ]);

        $account = SavingAccount::create([
            'account_no' => '104000100000000001',
            'cif_id' => $cif->id,
            'saving_product_id' => $product->id,
            'branch_id' => $user->branch_id,
            'balance' => 0,
            'status' => 'ACTIVE',
            'created_by' => $user->id,
        ]);

        $payload = [
            'distribution_no' => 'DIST-TEST-0001',
            'distribution_type' => 'CREDIT',
            'channel' => 'CASH',
            'saving_product_id' => $product->id,
            'description' => 'Distribusi test',
            'effective_date' => '2026-02-18',
            'items' => [
                ['account_no' => $account->account_no, 'amount' => 10000, 'note' => 'Test'],
            ],
        ];

        app(SavingDistributionService::class)->executeDistribution($payload);
        app(SavingDistributionService::class)->executeDistribution($payload);

        $legacyPayload = $payload;
        unset($legacyPayload['distribution_no']);

        app(SavingDistributionService::class)->executeDistribution($legacyPayload);

        $this->assertSame('10000.00', $account->fresh()->balance);
        $this->assertSame(1, SavingDistribution::where('distribution_no', 'DIST-TEST-0001')->count());
        $this->assertSame(1, SavingTransaction::where('reference_no', 'DIST-TEST-0001-' . $account->account_no)->count());
    }

    public function test_saving_distribution_rejects_duplicate_accounts_before_insert(): void
    {
        $user = $this->createUserWithBranch();
        $this->actingAs($user);

        $cashCoa = Coa::create(['coa_code' => '1001', 'name' => 'Kas', 'type' => 'ASSET', 'is_cash' => true]);
        $liabilityCoa = Coa::create(['coa_code' => '2001', 'name' => 'Tabungan', 'type' => 'LIABILITY']);

        $product = SavingProduct::create([
            'product_code' => 'S01',
            'name' => 'Simpanan Test',
            'liability_coa_id' => $liabilityCoa->id,
            'default_cash_coa_id' => $cashCoa->id,
            'created_by' => $user->id,
        ]);

        $payload = [
            'distribution_no' => 'DIST-TEST-DUP',
            'distribution_type' => 'CREDIT',
            'channel' => 'CASH',
            'saving_product_id' => $product->id,
            'description' => 'Distribusi test',
            'effective_date' => '2026-02-18',
            'items' => [
                ['account_no' => '104000100000000001', 'amount' => 10000, 'note' => 'Test'],
                ['account_no' => '104000100000000001', 'amount' => 5000, 'note' => 'Test kedua'],
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File CSV mengandung rekening duplikat: 104000100000000001 (2x).');

        app(SavingDistributionService::class)->executeDistribution($payload);
    }
}
