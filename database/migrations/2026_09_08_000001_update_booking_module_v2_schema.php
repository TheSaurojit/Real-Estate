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
        // 1. Update bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('parking_type', 50)->default('no_parking')->change();
            $table->decimal('adjustments', 15, 2)->default(0)->after('supplementary_value');
            $table->decimal('gross_booking_value', 15, 2)->default(0)->after('adjustments');
            $table->decimal('final_booking_value', 15, 2)->default(0)->after('gross_booking_value');
            $table->decimal('party_self_taxable', 15, 2)->default(0)->after('gross_taxable_value');
            $table->decimal('party_non_taxable_cash', 15, 2)->default(0)->after('party_self_taxable');
            $table->integer('cancellation_days_gap')->nullable()->after('cancellation_date');
        });

        // 2. Update booking_customizations table
        Schema::table('booking_customizations', function (Blueprint $table) {
            $table->decimal('material_total', 15, 2)->default(0)->after('quantity');
            $table->decimal('labour_total', 15, 2)->default(0)->after('material_total');
        });

        // 3. Update sale_deeds table
        Schema::table('sale_deeds', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
            $table->decimal('sale_deed_value', 15, 2)->default(0)->after('executed_date');
            $table->string('executed_in', 255)->nullable()->after('sub_registrar_office');
        });

        // 4. Update booking_cancellation_refunds table
        Schema::table('booking_cancellation_refunds', function (Blueprint $table) {
            $table->decimal('loan_refund_instruction', 15, 2)->default(0)->after('cancellation_fee');
            $table->decimal('taxable_refund_instruction', 15, 2)->default(0)->after('loan_refund_instruction');
            $table->decimal('cash_refund_instruction', 15, 2)->default(0)->after('taxable_refund_instruction');
            $table->decimal('cancellation_charge_loan', 15, 2)->default(0)->after('cash_refund_instruction');
            $table->decimal('cancellation_charge_taxable', 15, 2)->default(0)->after('cancellation_charge_loan');
            $table->decimal('cancellation_charge_cash', 15, 2)->default(0)->after('cancellation_charge_taxable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_cancellation_refunds', function (Blueprint $table) {
            $table->dropColumn([
                'loan_refund_instruction',
                'taxable_refund_instruction',
                'cash_refund_instruction',
                'cancellation_charge_loan',
                'cancellation_charge_taxable',
                'cancellation_charge_cash',
            ]);
        });

        Schema::table('sale_deeds', function (Blueprint $table) {
            $table->dropColumn(['sale_deed_value', 'executed_in']);
        });

        Schema::table('booking_customizations', function (Blueprint $table) {
            $table->dropColumn(['material_total', 'labour_total']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'adjustments',
                'gross_booking_value',
                'final_booking_value',
                'party_self_taxable',
                'party_non_taxable_cash',
                'cancellation_days_gap',
            ]);
        });
    }
};
