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
        // 1. Suppliers / Material Vendors Master
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('supplier_code', 50)->unique();
            $table->string('name', 200);
            $table->string('contact_person', 150)->nullable();
            $table->string('mobile_no', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('gstin', 30)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('category', 100)->default('Building Materials'); // Cement, Steel, Electrical, Contractor, etc.
            $table->text('address')->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('ifsc_code', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Construction Site Expenses & Purchases
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();

            $table->string('expense_code', 50)->unique();
            $table->date('expense_date');
            $table->enum('expense_category', [
                'material_purchase',
                'labor_contractor',
                'site_overheads',
                'administrative',
                'machinery_equipment',
                'statutory_permits'
            ])->default('material_purchase');

            $table->string('item_name', 200);
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit_measure', 30)->default('LS'); // Bags, Tonnes, Sq.Ft, LS, Days, etc.
            $table->decimal('unit_rate', 15, 2)->default(0);
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('gross_amount', 15, 2);

            $table->string('invoice_no', 100)->nullable();
            $table->date('invoice_date')->nullable();
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'cheque', 'neft', 'rtgs', 'upi'])->default('bank_transfer');
            $table->enum('payment_status', ['paid', 'partial', 'pending'])->default('paid');
            $table->string('payment_ref_no', 100)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Inter-Project Stock / Material Transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('source_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('destination_project_id')->constrained('projects')->cascadeOnDelete();

            $table->string('transfer_code', 50)->unique();
            $table->date('transfer_date');
            $table->string('material_name', 200);
            $table->decimal('quantity', 12, 2);
            $table->string('unit_measure', 30)->default('Units');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_transfer_value', 15, 2);

            $table->string('vehicle_no', 50)->nullable();
            $table->string('challan_no', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('suppliers');
    }
};
