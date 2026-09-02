<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_code',
        'name',
        'contact_primary',
        'contact_secondary',
        'email',
        'address',
        'pan_number',
        'gstin',
        'logo_path',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function autoNumberSequences(): HasMany
    {
        return $this->hasMany(AutoNumberSequence::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
