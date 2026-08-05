<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            if (!Schema::hasColumn('loan_products', 'admin_rate')) {
                Schema::table('loan_products', function (Blueprint $table) {
                    $table->decimal('admin_rate', 5, 2)->default(0)->after('provision_rate');
                });

                if (Schema::hasColumn('loan_products', 'admin_fee')) {
                    DB::table('loan_products')->update([
                        'admin_rate' => DB::raw('admin_fee'),
                    ]);
                }
            }
        } else {
            Schema::table('loan_products', function (Blueprint $table) {
                if (Schema::hasColumn('loan_products', 'admin_fee')) {
                    $table->renameColumn('admin_fee', 'admin_rate');
                }
                if (Schema::hasColumn('loan_products', 'insurance_product_id')) {
                    $table->dropForeign(['insurance_product_id']);
                    $table->dropColumn('insurance_product_id');
                }
                if (Schema::hasColumn('loan_products', 'notary_fee')) {
                    $table->dropColumn('notary_fee');
                }
                if (Schema::hasColumn('loan_products', 'insurance_rate')) {
                    $table->dropColumn('insurance_rate');
                }
                if (Schema::hasColumn('loan_products', 'notary_revenue_coa_id')) {
                    $table->dropForeign(['notary_revenue_coa_id']);
                    $table->dropColumn('notary_revenue_coa_id');
                }
            });
        }

        Schema::table('loan_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_accounts', 'insurance_product_id')) {
                $table->foreignId('insurance_product_id')->nullable()->after('marketing_id')->constrained('insurance_products')->nullOnDelete();
            }
            if (!Schema::hasColumn('loan_accounts', 'insurance_rate')) {
                $table->decimal('insurance_rate', 5, 2)->default(0)->after('insurance_product_id');
            }
            if (Schema::hasColumn('loan_accounts', 'notary_fee')) {
                $table->dropColumn('notary_fee');
            }
        });

        Schema::table('loan_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('loan_transactions', 'amount_notary_fee')) {
                $table->dropColumn('amount_notary_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_transactions', function (Blueprint $table) {
            $table->decimal('amount_notary_fee', 20, 2)->default(0)->after('amount_insurance_fee');
        });

        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropForeign(['insurance_product_id']);
            $table->dropColumn(['insurance_product_id', 'insurance_rate']);
            $table->decimal('notary_fee', 20, 2)->nullable()->default(0)->after('insurance_fee');
        });

        Schema::table('loan_products', function (Blueprint $table) {
            $table->renameColumn('admin_rate', 'admin_fee');
            $table->decimal('notary_fee', 20, 2)->default(0);
            $table->decimal('insurance_rate', 5, 2)->default(0);
            $table->foreignId('insurance_product_id')->nullable()->constrained('insurance_products')->nullOnDelete();
            $table->foreignId('notary_revenue_coa_id')->nullable()->constrained('coas');
        });
    }
};
