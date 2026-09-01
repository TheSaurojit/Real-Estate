<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'project_code',
        'name',
        'nick_name',
        'daag_no',
        'patta_no',
        'holding_no',
        'mouza',
        'pogonah',
        'full_address',
        'rera_category',
        'rera_reg_no',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Formatted display name for dropdowns & headers e.g. "Ramkrishna Apartment (SSI/PRJ1001)"
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->project_code})";
    }

    /**
     * Check if project is safely deletable (no linked bookings/records)
     */
    public function canBeDeleted(): bool
    {
        // When bookings table is created, check: return $this->bookings()->count() === 0;
        return $this->bankAccounts()->count() === 0;
    }
}
