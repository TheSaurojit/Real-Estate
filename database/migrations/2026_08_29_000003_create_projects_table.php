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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->string('nick_name', 10);
            $table->string('daag_no')->nullable();
            $table->string('patta_no')->nullable();
            $table->string('holding_no')->nullable();
            $table->string('mouza')->nullable();
            $table->string('pogonah')->nullable();
            $table->text('full_address')->nullable();
            $table->enum('rera_category', ['unregistered', 'exempted', 'registered'])->default('unregistered');
            $table->string('rera_reg_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
