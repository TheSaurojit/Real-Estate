<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'company_id',
        'booking_id',
        'bank_account_id',
        'transaction_code',
        'voucher_no',
        'voucher_date',
        'voucher_category',
        'voucher_type',
        'payment_category',
        'transaction_mode',
        'source_of_payment',
        'amount',
        'instrument_ref_no',
        'instrument_date',
        'issuing_bank',
        'issuing_branch',
        'instrument_status',
        'dishonor_penalty_amount',
        'dishonor_date',
        'dishonor_remarks',
        'is_taxable_transaction',
        'particulars',
        'created_by',
    ];

    protected $casts = [
        'voucher_date'            => 'date',
        'instrument_date'         => 'date',
        'dishonor_date'           => 'date',
        'amount'                  => 'decimal:2',
        'dishonor_penalty_amount' => 'decimal:2',
        'is_taxable_transaction'  => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for cleared / active credit transactions (excluding dishonored instruments)
     */
    public function scopeCleared(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('instrument_status', 'cleared')
              ->orWhere('instrument_status', 'not_applicable');
        });
    }

    /**
     * Scope for taxable banking transactions (Money Receipts)
     */
    public function scopeTaxable(Builder $query): Builder
    {
        return $query->where('is_taxable_transaction', true)
                     ->where('voucher_type', 'money_receipt');
    }

    /**
     * Scope for non-taxable cash transactions (Receipt Vouchers)
     */
    public function scopeCash(Builder $query): Builder
    {
        return $query->where('is_taxable_transaction', false)
                     ->where('voucher_type', 'receipt_voucher');
    }

    /**
     * Scope for payment refunds
     */
    public function scopeRefunds(Builder $query): Builder
    {
        return $query->where('voucher_category', 'payment_refund');
    }
}
