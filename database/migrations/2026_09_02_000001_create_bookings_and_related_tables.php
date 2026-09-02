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
        // 1. Main Bookings Table
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('booking_code', 50)->unique();
            $table->date('booking_date');
            $table->boolean('is_landowner_allocation')->default(false);

            // Property Specifications (Entered on the fly)
            $table->string('block_name')->nullable();
            $table->string('floor_no')->nullable();
            $table->string('unit_no', 50);
            $table->decimal('built_up_area', 10, 2)->default(0);
            $table->decimal('super_built_up_area', 10, 2)->default(0);
            $table->string('property_type', 100)->default('Residential Flat');
            $table->enum('parking_type', ['none', 'private_covered', 'private_open', 'shared'])->default('none');
            $table->string('parking_no', 50)->nullable();
            $table->string('reference_source', 100)->nullable();

            // Customer Information
            $table->string('customer_salutation', 20)->default('Mr.');
            $table->string('customer_name', 255);
            $table->enum('guardian_relation', ['son_of', 'daughter_of', 'wife_of', 'care_of'])->default('son_of');
            $table->string('guardian_name', 255)->nullable();
            $table->string('mobile_no', 50);
            $table->string('alt_mobile_no', 50)->nullable();
            $table->string('email_id', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('gstin', 30)->nullable();
            $table->enum('photo_id_type', ['aadhaar', 'passport', 'voter_id', 'driving_license', 'pan'])->default('aadhaar');
            $table->string('photo_id_no', 50)->nullable();

            // Pricing & Consideration Breakdown
            $table->decimal('rate_per_sqft', 12, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('parking_cost', 15, 2)->default(0);
            $table->decimal('transformer_cost', 15, 2)->default(0);
            $table->decimal('amenities_cost', 15, 2)->default(0);
            $table->decimal('gross_total', 15, 2)->default(0);
            $table->decimal('discount_applied', 15, 2)->default(0);
            $table->decimal('consideration_value', 15, 2)->default(0);
            $table->string('tax_name', 50)->default('GST - 5.00%');
            $table->decimal('tax_rate', 5, 2)->default(5.00);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('supplementary_value', 15, 2)->default(0); // Net rollup from Job Sheets (Addons - Dislodges)
            $table->decimal('total_booking_value', 15, 2)->default(0);

            // Dual Accounting Split (Calculated upon Booking / Sale Agreement)
            $table->decimal('taxable_agreement_value', 15, 2)->default(0);
            $table->decimal('taxable_gst_value', 15, 2)->default(0);
            $table->decimal('gross_taxable_value', 15, 2)->default(0);
            $table->decimal('gross_cash_value', 15, 2)->default(0);

            // Status & Lifecycle
            $table->enum('status', ['live', 'executed_agreement', 'registered_deed', 'cancelled', 'handed_over'])->default('live');
            $table->date('cancellation_date')->nullable();
            $table->decimal('cancellation_charge', 15, 2)->default(0);
            $table->text('cancellation_remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Customization Job Sheets (Add-ons vs Dislodges)
        Schema::create('booking_customizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->enum('job_type', ['addon', 'dislodge'])->default('addon');
            $table->string('particular', 255);
            $table->text('description')->nullable();
            $table->decimal('material_rate', 12, 2)->default(0);
            $table->decimal('labour_rate', 12, 2)->default(0);
            $table->decimal('schedule_rate', 12, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit_measure', 30)->default('LS');
            $table->decimal('job_total', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Sale Agreement (Bayna-nama) Details
        Schema::create('sale_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->enum('agreement_status', ['pending', 'drafted', 'executed', 'registered'])->default('pending');
            $table->string('agreement_serial_no', 100)->nullable();
            $table->date('execution_date')->nullable();
            $table->decimal('agreement_value', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(5.00);
            $table->decimal('tax_value', 15, 2)->default(0);
            $table->decimal('gross_taxable_value', 15, 2)->default(0);
            $table->decimal('cash_value', 15, 2)->default(0);
            $table->text('document_details')->nullable();
            $table->timestamps();
        });

        // 4. Bank Finance (Home Loan) Tracking
        Schema::create('bank_finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->enum('finance_status', ['not_applicable', 'applied', 'sanctioned', 'disbursed'])->default('not_applicable');
            $table->string('bank_name', 150)->nullable();
            $table->string('branch_name', 150)->nullable();
            $table->string('loan_account_no', 100)->nullable();
            $table->decimal('sanctioned_amount', 15, 2)->default(0);
            $table->decimal('disbursed_amount', 15, 2)->default(0);
            $table->date('sanction_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 5. Sale Deed & Registration Details
        Schema::create('sale_deeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->string('sale_deed_no', 100)->nullable();
            $table->date('executed_date')->nullable();
            $table->string('sub_registrar_office', 150)->nullable();
            $table->enum('status', ['pending', 'registered'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_deeds');
        Schema::dropIfExists('bank_finances');
        Schema::dropIfExists('sale_agreements');
        Schema::dropIfExists('booking_customizations');
        Schema::dropIfExists('bookings');
    }
};
