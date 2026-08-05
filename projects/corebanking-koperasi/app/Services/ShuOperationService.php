<?php

namespace App\Services;

use App\Models\ShuDistribution;
use App\Models\ShuDistributionDetail;
use App\Models\MasterShu;
use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ShuOperationService
{
    public function __construct(
        private readonly JournalService $journalService
    ) {}

    /**
     * Execute SHU distribution and create journal entries.
     *
     * @param array $data
     * @return ShuDistribution
     * @throws Exception
     */
    public function executeDistribution(array $data): ShuDistribution
    {
        return DB::transaction(function () use ($data) {
            // Distribusi SHU dibayar dari laba tahun lalu; laba tahun berjalan dihitung otomatis di laporan.
            $shuCoa = Coa::where('coa_code', '315000')->first();
            if (!$shuCoa) {
                throw new Exception("COA SHU LABA TAHUN LALU (315000) tidak ditemukan.");
            }

            // Create main distribution record
            $distribution = ShuDistribution::create([
                'periode'    => $data['periode'],
                'total_laba' => $data['total_laba'],
                'status'     => 'DISTRIBUTED',
            ]);

            $journalEntries = [];
            $totalDistributed = 0;

            // Prepare list of saving account updates
            $disbursements = [];

            foreach ($data['details'] as $detail) {
                // Save detail per-kriteria
                ShuDistributionDetail::create([
                    'shu_distribution_id' => $distribution->id,
                    'kriteria'            => $detail['kriteria'],
                    'persentase'          => $detail['persentase'],
                    'total_shu'           => $detail['shu'],
                    'jumlah_orang'        => $detail['jumlah_orang'],
                    'nominal_per_orang'   => $detail['per_orang'],
                ]);

                if ($detail['jumlah_orang'] > 0) {
                    $masterShus = MasterShu::with('savingAccount.product')
                        ->where('kriteria', $detail['kriteria'])
                        ->whereNotNull('saving_account_id')
                        ->get();

                    foreach ($masterShus as $masterShu) {
                        $savingAccount = $masterShu->savingAccount;
                        if (!$savingAccount) continue;

                        $product = $savingAccount->product;
                        if (!$product || !$product->liability_coa_id) {
                            throw new Exception("Produk simpanan untuk rekening {$savingAccount->account_no} tidak memiliki COA kewajiban yang diatur.");
                        }

                        $nominalPerOrang = round((float) $detail['per_orang'], 2);
                        if ($nominalPerOrang <= 0) continue;

                        $totalDistributed += $nominalPerOrang;

                        // Group journal entries by product liability COA to keep journal entries concise
                        $coaId = $product->liability_coa_id;
                        if (!isset($journalEntries[$coaId])) {
                            $journalEntries[$coaId] = 0;
                        }
                        $journalEntries[$coaId] += $nominalPerOrang;

                        $disbursements[] = [
                            'account' => $savingAccount,
                            'amount' => $nominalPerOrang,
                            'coa_id' => $coaId
                        ];
                    }
                }
            }

            if ($totalDistributed <= 0) {
                throw new Exception("Tidak ada nominal SHU yang didepositokan ke anggota.");
            }

            // Round total to 2 decimals
            $totalDistributed = round($totalDistributed, 2);

            // Construct journal lines
            $entries = [];
            // Debit: SHU/Laba Tahun Lalu
            $entries[] = [
                'coa_id' => $shuCoa->id,
                'debit'  => $totalDistributed,
                'credit' => 0
            ];

            // Credit: Product Liabilities
            foreach ($journalEntries as $coaId => $creditAmount) {
                $entries[] = [
                    'coa_id' => $coaId,
                    'debit'  => 0,
                    'credit' => round($creditAmount, 2)
                ];
            }

            // Post System Journal
            $branchId = Auth::user()->branch_id ?? 1;
            $journal = $this->journalService->createSystemJournal(
                branchId: $branchId,
                prefix: 'SHU',
                description: "Distribusi SHU Periode " . $data['periode'] . " - Total Laba: " . number_format($data['total_laba'], 2, ',', '.'),
                entries: $entries
            );

            // Perform balance updates and record saving transactions
            foreach ($disbursements as $disb) {
                $account = $disb['account'];
                $amount = $disb['amount'];

                $newBalance = $account->balance + $amount;
                $referenceNo = 'SHU-' . $distribution->id . '-' . $account->id . '-' . time();

                SavingTransaction::create([
                    'transaction_no'     => $referenceNo,
                    'saving_account_id'  => $account->id,
                    'transaction_date'   => now()->toDateString(),
                    'type'               => 'DEPOSIT', // Use 'DEPOSIT' so it shows up cleanly as increase in mutasi
                    'channel'            => 'INTERNAL',
                    'amount'             => $amount,
                    'balance_after'      => $newBalance,
                    'journal_id'         => $journal->id,
                    'reference_no'       => $journal->reference_no,
                    'description'        => 'Pembagian SHU Periode ' . $data['periode'],
                    'created_by'         => Auth::id() ?? \App\Models\User::getSystemUserId(),
                ]);

                // Update balance on the account
                $account->balance = $newBalance;
                $account->save();
            }

            return $distribution;
        });
    }
}
