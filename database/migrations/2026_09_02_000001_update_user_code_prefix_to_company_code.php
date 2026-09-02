<?php

use App\Models\AutoNumberSequence;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update user auto number sequences to use company_code/USR-
        $userSequences = AutoNumberSequence::with('company')
            ->where('entity_type', 'user')
            ->get();

        foreach ($userSequences as $seq) {
            $companyCode = $seq->company?->company_code ?? 'SSI';
            if ($seq->prefix === 'User-') {
                $seq->update([
                    'prefix'      => $companyCode . '/USR-',
                    'description' => 'Staff Users (e.g. ' . $companyCode . '/USR-1001)',
                ]);
            }
        }

        // Update existing users with User-XXXX to {company_code}/USR-XXXX
        $users = User::with('company')->where('user_code', 'LIKE', 'User-%')->get();
        foreach ($users as $user) {
            $companyCode = $user->company?->company_code ?? 'SSI';
            $newCode = str_replace('User-', $companyCode . '/USR-', $user->user_code);
            $user->update(['user_code' => $newCode]);
        }
    }

    public function down(): void
    {
        AutoNumberSequence::where('entity_type', 'user')->where('prefix', 'LIKE', '%/USR-')->update(['prefix' => 'User-']);
    }
};
