<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'total_received_bank',
        'total_received_cash',
        'cancellation_fee',
        'refundable_to_bank_loan',
        'refunded_to_bank_loan',
        'refundable_to_party_taxable',
        'refunded_to_party_taxable',
        'refundable_to_party_cash',
        'refunded_to_party_cash',
        'status',
        'remarks',
    ];

    protected $casts = [
        'total_received_bank'         => 'decimal:2',
        'total_received_cash'         => 'decimal:2',
        'cancellation_fee'            => 'decimal:2',
        'refundable_to_bank_loan'     => 'decimal:2',
        'refunded_to_bank_loan'       => 'decimal:2',
        'refundable_to_party_taxable' => 'decimal:2',
        'refunded_to_party_taxable'   => 'decimal:2',
        'refundable_to_party_cash'    => 'decimal:2',
        'refunded_to_party_cash'      => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
