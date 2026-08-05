<?php

namespace App\Livewire\Deposits;

use Livewire\Component;
use App\Models\DepositProduct;
use App\Services\DepositOperationService;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;

class Simulation extends Component
{
    use LogsActivity, WithLogout;

    public $principal = 10000000;
    public $deposit_product_id;
    public $tenor = 1;
    public $interest_rate = 0;
    public $placement_date;
    public $interest_calculation_type = 'MONTHLY'; // MONTHLY or DAILY
    public $tax_rate = 0;
    public $results = null;

    protected $rules = [
        'principal' => 'required|numeric|min:0',
        'deposit_product_id' => 'required|exists:deposit_products,id',
        'tenor' => 'required|integer|min:1',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'placement_date' => 'required|date',
        'interest_calculation_type' => 'required|in:MONTHLY,DAILY',
        'tax_rate' => 'required|numeric|min:0|max:100',
    ];

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Simulasi Simpanan Berjangka');

        $defaultProduct = DepositProduct::where('is_active', true)->first();
        if ($defaultProduct) {
            $this->deposit_product_id = $defaultProduct->id;
            $this->tenor = $defaultProduct->min_term;
            $this->interest_rate = $defaultProduct->max_interest_rate;
            $this->tax_rate = $defaultProduct->tax_rate;
        }
        $this->placement_date = now()->format('Y-m-d');
    }

    public function updatedDepositProductId($id)
    {
        if ($id) {
            $product = DepositProduct::find($id);
            if ($product) {
                $this->interest_rate = $product->max_interest_rate;
                $this->tenor = $product->min_term;
                $this->tax_rate = $product->tax_rate;
                if ($this->principal < $product->min_amount) {
                    $this->principal = (float) $product->min_amount;
                }
            }
        }
    }

    public function calculate(DepositOperationService $service)
    {
        $this->validate();
        
        $this->results = $service->calculateSimulation(
            $this->principal,
            $this->deposit_product_id,
            $this->tenor,
            $this->interest_rate,
            $this->interest_calculation_type,
            $this->placement_date,
            $this->tax_rate
        );
    }

    public function render()
    {
        return view('livewire.deposits.simulation', [
            'products' => DepositProduct::where('is_active', true)->get()
        ]);
    }
}
