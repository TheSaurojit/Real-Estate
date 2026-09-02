<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'agreement_status',
        'agreement_serial_no',
        'execution_date',
        'agreement_value',
        'tax_rate',
        'tax_value',
        'gross_taxable_value',
        'cash_value',
        'document_details',
    ];

    protected $casts = [
        'execution_date'      => 'date',
        'agreement_value'     => 'decimal:2',
        'tax_rate'            => 'decimal:2',
        'tax_value'           => 'decimal:2',
        'gross_taxable_value' => 'decimal:2',
        'cash_value'          => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
