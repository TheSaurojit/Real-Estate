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
        Schema::create('auto_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('entity_type'); // bank_account, user, project, booking, receipt_money, receipt_voucher, payment_voucher
            $table->string('prefix');      // e.g. BANK-00, User-, PRJ-00, SSI/PRJ-, SSI/RCD, SSI/PMT
            $table->unsignedBigInteger('next_number')->default(1); // e.g. 1, 1001
            $table->unsignedTinyInteger('padding')->default(3);    // digits padding e.g. 3 => 001
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_number_sequences');
    }
};
