<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'project_id',
        'account_code',
        'account_type',
        'account_nick_name',
        'account_name',
        'account_number',
        'bank_name',
        'branch',
        'ifsc_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Readonly formatted summary as specified in PDF specification
     */
    public function getFormattedDetailsAttribute(): string
    {
        return "Account name : {$this->account_name}\n"
            . "Account number : {$this->account_number}\n"
            . "Bank name : {$this->bank_name}\n"
            . "Branch : {$this->branch}\n"
            . "IFSC : {$this->ifsc_code}";
    }

    /**
     * Check if account can be safely deleted (must not have any transactions)
     */
    public function canBeDeleted(): bool
    {
        // When transactions table is created in next phases, check $this->transactions()->count() === 0
        return true;
    }
}
