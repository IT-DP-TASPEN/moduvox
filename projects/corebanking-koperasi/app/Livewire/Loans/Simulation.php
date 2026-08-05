<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\LoanProduct;
use App\Services\LoanOperationService;
use App\Traits\LogsActivity;

class Simulation extends Component
{
    use LogsActivity;

    // Form Inputs
    public $loan_product_id;
    public $principal_amount = 10000000;
    public $tenor = 12;
    public $interest_rate = 12.00;
    public $calculation_method = 'FLAT';
    
    public $results = null;

    protected $rules = [
        'loan_product_id' => 'required',
        'principal_amount' => 'required|numeric|min:100000',
        'tenor' => 'required|integer|min:1',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'calculation_method' => 'required|in:FLAT,EFFECTIVE,ANNUITY'
    ];

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Simulasi Pinjaman');

        $firstProduct = LoanProduct::where('is_active', true)->first();
        if ($firstProduct) {
            $this->loan_product_id = $firstProduct->id;
            $this->interest_rate = $firstProduct->interest_rate_min;
            $this->calculation_method = $firstProduct->calculation_method;
        }
    }

    public function updatedLoanProductId()
    {
        $product = LoanProduct::find($this->loan_product_id);
        if ($product) {
            $this->interest_rate = $product->interest_rate_min;
            $this->calculation_method = $product->calculation_method;
        }
    }

    public function calculate(LoanOperationService $service)
    {
        $this->validate();

        $schedule = $service->simulateSchedules(
            (float) $this->principal_amount,
            (float) $this->interest_rate,
            (int) $this->tenor,
            $this->calculation_method
        );

        $totalInterest = array_sum(array_column($schedule, 'interest_amount'));
        $totalPrincipal = (float) $this->principal_amount;

        $this->results = [
            'product_name' => LoanProduct::find($this->loan_product_id)->name,
            'principal' => $totalPrincipal,
            'tenor' => $this->tenor,
            'rate' => $this->interest_rate,
            'method' => $this->calculation_method,
            'total_interest' => $totalInterest,
            'total_payment' => $totalPrincipal + $totalInterest,
            'monthly_payment' => $schedule[0]['total_amount'] ?? 0,
            'schedule' => $schedule
        ];
    }

    public function render()
    {
        return view('livewire.loans.simulation', [
            'products' => LoanProduct::where('is_active', true)->get()
        ]);
    }
}
