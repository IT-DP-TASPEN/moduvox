<?php

namespace App\Console\Commands;

use App\Enums\DepreciationBatchStatus;
use App\Enums\JournalState;
use App\Models\ApiJournal;
use App\Models\ApiLog;
use App\Models\PenyusutanBatch;
use App\Services\FinCloudApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleansingMockJournals extends Command
{
    protected $signature = 'journals:cleansing-mock
                            {--batch= : ID batch tertentu yang ingin di-cleansing}
                            {--periode= : Periode YYYYMM (misal: 202607)}
                            {--resend : Langsung kirim ulang ke dev server setelah reset}
                            {--full-reset : Hapus semua jurnal mock + reopen batch untuk approve ulang}
                            {--force : Skip konfirmasi}';

    protected $description = 'Cleansing jurnal API yang berisi response MOCK. Bisa reset ke DRAFT, resend, atau full-reset (hapus + reopen batch).';

    public function handle(): int
    {
        if ($this->option('full-reset')) {
            return $this->handleFullReset();
        }

        return $this->handleStandardCleansing();
    }

    /**
     * Full reset: Hapus semua jurnal + logs, reopen batch ke DRAFT.
     */
    private function handleFullReset(): int
    {
        // Find target batches
        $batchQuery = PenyusutanBatch::query();

        if ($batchId = $this->option('batch')) {
            $batchQuery->where('id', $batchId);
        }

        if ($periode = $this->option('periode')) {
            $batchQuery->where('periode_ym', $periode);
        }

        $batches = $batchQuery->get();

        if ($batches->isEmpty()) {
            $this->error('Tidak ditemukan batch yang sesuai filter.');
            return self::FAILURE;
        }

        $this->warn("========================================");
        $this->warn("  FULL RESET — HAPUS JURNAL + REOPEN");
        $this->warn("========================================");
        $this->newLine();

        foreach ($batches as $batch) {
            $journalCount = ApiJournal::where('batch_id', $batch->id)->count();
            $this->line("  Batch #{$batch->id} | Periode: {$batch->periode_label} ({$batch->periode_ym}) | Status: {$batch->status->value} | Jurnal: {$journalCount}");
        }

        $totalJournals = ApiJournal::whereIn('batch_id', $batches->pluck('id'))->count();
        $this->newLine();
        $this->warn("⚠️  Ini akan MENGHAPUS {$totalJournals} jurnal API + semua logs terkait,");
        $this->warn("   dan mereset status batch kembali ke DRAFT.");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Yakin lanjutkan FULL RESET?')) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }
        }

        DB::beginTransaction();
        try {
            foreach ($batches as $batch) {
                $journalIds = ApiJournal::where('batch_id', $batch->id)->pluck('id');

                // Delete logs first (FK constraint)
                $logsDeleted = ApiLog::whereIn('journal_id', $journalIds)->delete();

                // Delete journals
                $journalsDeleted = ApiJournal::where('batch_id', $batch->id)->delete();

                // Reopen batch to DRAFT
                $batch->update([
                    'status' => DepreciationBatchStatus::DRAFT->value,
                    'approved_by' => null,
                    'approved_at' => null,
                ]);

                $this->info("  ✅ Batch #{$batch->id} ({$batch->periode_ym}): {$journalsDeleted} jurnal & {$logsDeleted} logs dihapus, status → DRAFT");
            }

            DB::commit();
            $this->newLine();
            $this->info('🎉 Full reset selesai! Anda sekarang bisa approve ulang batch dari UI.');
            $this->info('   Pastikan FINCLOUD_MOCK_MODE=false di .env dan config sudah di-clear.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Gagal: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Standard cleansing: reset jurnal mock ke DRAFT, opsional resend.
     */
    private function handleStandardCleansing(): int
    {
        // Find mock journals (both SUCCESS/COMPLETED and DRAFT from previous cleansing)
        $query = ApiJournal::query()
            ->where(function ($q) {
                $q->where('response_body', 'like', '%MOCK_SUCCESS%')
                  ->orWhere('core_reff', 'like', 'JRN-%');
            })
            ->whereIn('state', [
                JournalState::SUCCESS->value,
                JournalState::COMPLETED->value,
            ]);

        if ($batchId = $this->option('batch')) {
            $query->where('batch_id', $batchId);
        }

        if ($periode = $this->option('periode')) {
            $batchIds = PenyusutanBatch::where('periode_ym', $periode)->pluck('id');
            if ($batchIds->isEmpty()) {
                $this->error("Tidak ditemukan batch untuk periode {$periode}.");
                return self::FAILURE;
            }
            $query->whereIn('batch_id', $batchIds);
            $this->info("Filter periode: {$periode} (batch IDs: {$batchIds->implode(', ')})");
        }

        $journals = $query->get();

        if ($journals->isEmpty()) {
            $this->info('✅ Tidak ada jurnal mock yang perlu di-cleansing.');
            $this->info('💡 Tip: Gunakan --full-reset untuk hapus jurnal + reopen batch.');
            return self::SUCCESS;
        }

        $this->warn("========================================");
        $this->warn("  CLEANSING MOCK JOURNALS");
        $this->warn("========================================");
        $this->newLine();

        $tableData = $journals->map(fn($j) => [
            $j->id,
            $j->reff_id,
            $j->batch_id,
            $j->state->value,
            $j->core_reff ?? '-',
            $j->updated_at?->format('d/m/Y H:i'),
        ])->toArray();

        $this->table(
            ['ID', 'Reff ID', 'Batch', 'State', 'Core Reff (Mock)', 'Last Updated'],
            $tableData
        );

        $this->newLine();
        $this->info("Total jurnal mock ditemukan: {$journals->count()}");

        if (!$this->option('force')) {
            if (!$this->confirm('Lanjutkan cleansing? Ini akan mereset state jurnal ke DRAFT dan menghapus mock response.')) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }
        }

        $this->info('🔄 Memulai cleansing...');
        $bar = $this->output->createProgressBar($journals->count());
        $resetCount = 0;

        DB::beginTransaction();
        try {
            foreach ($journals as $journal) {
                ApiLog::where('journal_id', $journal->id)
                    ->where(function ($q) {
                        $q->whereJsonContains('response_payload->description', 'MOCK_SUCCESS')
                          ->orWhereNull('response_payload');
                    })
                    ->delete();

                $journal->update([
                    'state' => JournalState::DRAFT->value,
                    'core_reff' => null,
                    'response_body' => null,
                    'retry_count' => 0,
                    'last_attempt_at' => null,
                ]);

                $resetCount++;
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Berhasil reset {$resetCount} jurnal ke DRAFT.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error("❌ Gagal cleansing: {$e->getMessage()}");
            return self::FAILURE;
        }

        // Optionally resend
        if ($this->option('resend')) {
            $mockMode = config('services.api.mock_mode');
            if ($mockMode) {
                $this->error('⚠️  FINCLOUD_MOCK_MODE masih true! Jalankan `php artisan config:clear` dulu.');
                return self::FAILURE;
            }

            $this->newLine();
            $this->info('🚀 Mengirim ulang jurnal ke dev server...');
            $this->info("   Endpoint: " . config('services.api.gl_endpoint'));
            $this->newLine();

            if (!$this->option('force') && !$this->confirm('Kirim semua jurnal yang sudah di-reset ke dev server sekarang?')) {
                $this->info('Resend dibatalkan. Jurnal tetap DRAFT, bisa dikirim manual.');
                return self::SUCCESS;
            }

            $bar = $this->output->createProgressBar($resetCount);
            $successCount = 0;
            $failCount = 0;

            $drafts = ApiJournal::whereIn('id', $journals->pluck('id'))
                ->where('state', JournalState::DRAFT->value)
                ->get();

            foreach ($drafts as $journal) {
                try {
                    FinCloudApiService::sendJournal($journal);
                    $journal->refresh();

                    if ($journal->state === JournalState::SUCCESS) {
                        $successCount++;
                    } else {
                        $failCount++;
                        $this->newLine();
                        $this->warn("  ⚠ {$journal->reff_id}: state = {$journal->state->value}");
                    }
                } catch (\Exception $e) {
                    $failCount++;
                    $this->newLine();
                    $this->error("  ✗ {$journal->reff_id}: {$e->getMessage()}");
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("📊 Hasil resend: {$successCount} berhasil, {$failCount} gagal.");

            if ($failCount > 0) {
                $this->warn("Jurnal gagal bisa di-retry manual lewat UI.");
            }
        }

        $this->newLine();
        $this->info('🎉 Cleansing selesai.');
        return self::SUCCESS;
    }
}
