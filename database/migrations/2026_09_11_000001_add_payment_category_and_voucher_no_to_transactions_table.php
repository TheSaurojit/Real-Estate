<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('voucher_no', 100)->nullable()->after('transaction_code');
            $table->enum('payment_category', ['taxable', 'non_taxable'])->nullable()->after('voucher_type');
        });

        // Backfill payment_category for existing transactions
        DB::table('transactions')
            ->where('voucher_type', 'money_receipt')
            ->update(['payment_category' => 'taxable']);

        DB::table('transactions')
            ->where('voucher_type', 'receipt_voucher')
            ->update(['payment_category' => 'non_taxable']);

        DB::table('transactions')
            ->where('voucher_type', 'payment_voucher')
            ->update([
                'payment_category' => DB::raw("CASE WHEN is_taxable_transaction = 1 THEN 'taxable' ELSE 'non_taxable' END")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['voucher_no', 'payment_category']);
        });
    }
};
