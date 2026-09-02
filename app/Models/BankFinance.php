<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankFinance extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'finance_status',
        'bank_name',
        'branch_name',
        'loan_account_no',
        'sanctioned_amount',
        'disbursed_amount',
        'sanction_date',
        'remarks',
    ];

    protected $casts = [
        'sanction_date'     => 'date',
        'sanctioned_amount' => 'decimal:2',
        'disbursed_amount'  => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
