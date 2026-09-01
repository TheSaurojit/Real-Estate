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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->string('user_code')->nullable()->unique()->after('company_id');
            $table->string('designation')->nullable()->after('name');
            $table->string('mobile')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'admin', 'manager', 'accountant', 'site_engineer'])->default('admin')->after('mobile');
            $table->json('assigned_project_ids')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('assigned_project_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'company_id',
                'user_code',
                'designation',
                'mobile',
                'role',
                'assigned_project_ids',
                'is_active',
            ]);
        });
    }
};
