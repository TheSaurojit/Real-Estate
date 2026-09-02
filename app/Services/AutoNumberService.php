<?php

namespace App\Services;

use App\Models\AutoNumberSequence;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AutoNumberService
{
    /**
     * Entity mapping to Model and column for collision checking
     */
    protected static array $entityModelMap = [
        'user'         => [User::class, 'user_code'],
        'bank_account' => [BankAccount::class, 'account_code'],
        'project'      => [Project::class, 'project_code'],
    ];

    /**
     * Get default settings for each entity type if not yet created in database
     */
    public static function getDefaultSettings(string $entityType, ?Company $company = null): array
    {
        $companyCode = $company?->company_code ?? 'SSI';

        $defaults = [
            'bank_account' => ['prefix' => 'BANK-00', 'next_number' => 1, 'padding' => 1, 'description' => 'Bank Account Code'],
            'user'         => ['prefix' => $companyCode . '/USR-', 'next_number' => 1001, 'padding' => 0, 'description' => 'User ID / Employee Code (e.g. ' . $companyCode . '/USR-1001)'],
            'project'      => ['prefix' => $companyCode . '/PRJ-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Project Code'],
            'booking'      => ['prefix' => $companyCode . '/BK-',  'next_number' => 1001, 'padding' => 0, 'description' => 'Booking ID'],
            'receipt'      => ['prefix' => $companyCode . '/RCD',  'next_number' => 1001, 'padding' => 0, 'description' => 'Money Receipt / Transaction ID'],
            'payment'      => ['prefix' => $companyCode . '/PMT',  'next_number' => 1001, 'padding' => 0, 'description' => 'Payment Voucher ID'],
        ];

        return $defaults[$entityType] ?? ['prefix' => strtoupper($entityType) . '-', 'next_number' => 1, 'padding' => 3, 'description' => ucfirst($entityType)];
    }

    /**
     * Check if a generated code already exists in the database
     */
    public function codeExists(string $entityType, string $code): bool
    {
        if (isset(self::$entityModelMap[$entityType])) {
            [$modelClass, $column] = self::$entityModelMap[$entityType];
            if (class_exists($modelClass)) {
                return $modelClass::where($column, $code)->exists();
            }
        }
        return false;
    }

    /**
     * Generate next sequential code safely with database locking & collision prevention
     */
    public function getNextNumber(string $entityType, ?int $companyId = null, bool $increment = true): string
    {
        return DB::transaction(function () use ($entityType, $companyId, $increment) {
            $sequence = AutoNumberSequence::where('entity_type', $entityType)
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // If company-specific not found, check global sequence (company_id is null)
                $sequence = AutoNumberSequence::where('entity_type', $entityType)
                    ->whereNull('company_id')
                    ->lockForUpdate()
                    ->first();
            }

            if (!$sequence) {
                // Create with company-specific defaults
                $company = $companyId ? Company::find($companyId) : null;
                $defaults = self::getDefaultSettings($entityType, $company);
                $sequence = AutoNumberSequence::create([
                    'company_id'  => $companyId,
                    'entity_type' => $entityType,
                    'prefix'      => $defaults['prefix'],
                    'next_number' => $defaults['next_number'],
                    'padding'     => $defaults['padding'],
                    'description' => $defaults['description'],
                ]);
            }

            $currentNumber = $sequence->next_number;

            // Advance until an unused code is found to strictly prevent duplicate key collisions
            do {
                $paddedNumber = $sequence->padding > 0
                    ? str_pad((string)$currentNumber, $sequence->padding, '0', STR_PAD_LEFT)
                    : (string)$currentNumber;

                $generatedCode = $sequence->prefix . $paddedNumber;
                $exists = $this->codeExists($entityType, $generatedCode);

                if ($exists) {
                    $currentNumber++;
                }
            } while ($exists);

            if ($increment) {
                $sequence->update(['next_number' => $currentNumber + 1]);
            }

            return $generatedCode;
        });
    }

    /**
     * Preview next sequential code without incrementing
     */
    public function peekNextNumber(string $entityType, ?int $companyId = null): string
    {
        $sequence = AutoNumberSequence::where('entity_type', $entityType)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                      ->orWhereNull('company_id');
            })
            ->first();

        $company = $companyId ? Company::find($companyId) : null;
        $defaults = self::getDefaultSettings($entityType, $company);
        $prefix = $sequence?->prefix ?? $defaults['prefix'];
        $padding = $sequence?->padding ?? $defaults['padding'];
        $currentNumber = $sequence?->next_number ?? $defaults['next_number'];

        do {
            $padded = $padding > 0
                ? str_pad((string)$currentNumber, $padding, '0', STR_PAD_LEFT)
                : (string)$currentNumber;
            $candidate = $prefix . $padded;
            $exists = $this->codeExists($entityType, $candidate);
            if ($exists) {
                $currentNumber++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Update or create auto-number configuration
     */
    public function configureSequence(
        string $entityType,
        string $prefix,
        int $nextNumber,
        int $padding,
        ?int $companyId = null,
        ?string $description = null
    ): AutoNumberSequence {
        return AutoNumberSequence::updateOrCreate(
            [
                'entity_type' => $entityType,
                'company_id'  => $companyId,
            ],
            [
                'prefix'      => $prefix,
                'next_number' => $nextNumber,
                'padding'     => $padding,
                'description' => $description,
            ]
        );
    }
}
