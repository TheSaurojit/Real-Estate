<?php

namespace App\Services;

use App\Models\AutoNumberSequence;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class AutoNumberService
{
    /**
     * Get default settings for each entity type if not yet created in database
     */
    public static function getDefaultSettings(string $entityType): array
    {
        $defaults = [
            'bank_account' => ['prefix' => 'BANK-00', 'next_number' => 1, 'padding' => 1, 'description' => 'Bank Account Code'],
            'user'         => ['prefix' => 'User-',   'next_number' => 1001, 'padding' => 0, 'description' => 'User ID / Employee Code'],
            'project'      => ['prefix' => 'PRJ-00',  'next_number' => 1, 'padding' => 1, 'description' => 'Project Code'],
            'booking'      => ['prefix' => 'SSI/BK-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Booking ID'],
            'receipt'      => ['prefix' => 'SSI/RCD', 'next_number' => 1001, 'padding' => 0, 'description' => 'Money Receipt / Transaction ID'],
            'payment'      => ['prefix' => 'SSI/PMT', 'next_number' => 1001, 'padding' => 0, 'description' => 'Payment Voucher ID'],
        ];

        return $defaults[$entityType] ?? ['prefix' => strtoupper($entityType) . '-', 'next_number' => 1, 'padding' => 3, 'description' => ucfirst($entityType)];
    }

    /**
     * Generate next sequential code safely with database locking
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
                // Create with defaults
                $defaults = self::getDefaultSettings($entityType);
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
            $paddedNumber = $sequence->padding > 0
                ? str_pad((string)$currentNumber, $sequence->padding, '0', STR_PAD_LEFT)
                : (string)$currentNumber;

            $generatedCode = $sequence->prefix . $paddedNumber;

            if ($increment) {
                $sequence->increment('next_number');
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

        if (!$sequence) {
            $defaults = self::getDefaultSettings($entityType);
            $padded = $defaults['padding'] > 0
                ? str_pad((string)$defaults['next_number'], $defaults['padding'], '0', STR_PAD_LEFT)
                : (string)$defaults['next_number'];
            return $defaults['prefix'] . $padded;
        }

        $padded = $sequence->padding > 0
            ? str_pad((string)$sequence->next_number, $sequence->padding, '0', STR_PAD_LEFT)
            : (string)$sequence->next_number;

        return $sequence->prefix . $padded;
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
