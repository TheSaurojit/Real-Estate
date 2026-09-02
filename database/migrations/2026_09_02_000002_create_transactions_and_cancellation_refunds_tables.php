<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Transactions & Dual-Ledger Table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();

            $table->string('transaction_code', 50)->unique();
            $table->date('voucher_date');
            $table->enum('voucher_category', ['receipt', 'payment_refund', 'adjustment'])->default('receipt');
            $table->enum('voucher_type', [
                'money_receipt',     // Taxable Banking Receipt (Agreement + GST)
                'receipt_voucher',   // Non-Taxable Cash Receipt (Job Sheet / Cash Consideration)
                'payment_voucher',   // Payment / Refund Outflow
                'adjustment_voucher' // Cross-Ledger Rebalance
            ])->default('money_receipt');

            $table->enum('transaction_mode', [
                'cash',
                'cheque',
                'neft',
                'rtgs',
                'dd',
                'upi',
                'bank_transfer'
            ])->default('neft');

            $table->enum('source_of_payment', ['self', 'through_loan_account'])->default('self');
            $table->decimal('amount', 15, 2);

            // Instrument Details (For Cheque, DD, NEFT UTR)
            $table->string('instrument_ref_no', 100)->nullable();
            $table->date('instrument_date')->nullable();
            $table->string('issuing_bank', 150)->nullable();
            $table->string('issuing_branch', 150)->nullable();

            $table->enum('instrument_status', [
                'not_applicable',
                'pending_clearance',
                'cleared',
                'dishonored'
            ])->default('not_applicable');

            $table->decimal('dishonor_penalty_amount', 10, 2)->default(0);
            $table->date('dishonor_date')->nullable();
            $table->text('dishonor_remarks')->nullable();

            $table->boolean('is_taxable_transaction')->default(true);
            $table->text('particulars')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Booking Cancellation Split Refunds Table
        Schema::create('booking_cancellation_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->decimal('total_received_bank', 15, 2)->default(0);
            $table->decimal('total_received_cash', 15, 2)->default(0);
            $table->decimal('cancellation_fee', 15, 2)->default(0);
            $table->decimal('refundable_to_bank_loan', 15, 2)->default(0);
            $table->decimal('refunded_to_bank_loan', 15, 2)->default(0);
            $table->decimal('refundable_to_party_taxable', 15, 2)->default(0);
            $table->decimal('refunded_to_party_taxable', 15, 2)->default(0);
            $table->decimal('refundable_to_party_cash', 15, 2)->default(0);
            $table->decimal('refunded_to_party_cash', 15, 2)->default(0);
            $table->enum('status', ['pending', 'partially_refunded', 'completed'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_cancellation_refunds');
        Schema::dropIfExists('transactions');
    }
};
