<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCustomization extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'job_type',
        'particular',
        'description',
        'material_rate',
        'labour_rate',
        'schedule_rate',
        'quantity',
        'unit_measure',
        'job_total',
    ];

    protected $casts = [
        'material_rate' => 'decimal:2',
        'labour_rate'   => 'decimal:2',
        'schedule_rate' => 'decimal:2',
        'quantity'      => 'decimal:2',
        'job_total'     => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Compute total for this job sheet item
     */
    public function computeTotal(): float
    {
        $rateSum = (float)$this->material_rate + (float)$this->labour_rate + (float)$this->schedule_rate;
        return round($rateSum * (float)$this->quantity, 2);
    }
}
