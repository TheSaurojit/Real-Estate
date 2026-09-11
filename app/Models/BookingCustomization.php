<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCustomization extends Model
{
    use HasFactory;

    public const PARTICULARS = [
        'Civil & Masonry works',
        'Electrical works',
        'Plumbing & Sanitation works',
        'Tile, marble & granite works',
        'Door, doorframe & windows',
        'Grills & Railings',
        'Putty, primer & finishing jobs',
        'Processing fees, Legal & documentation charges',
        'Cleaning, demolishing & preparation jobs',
        'Others',
    ];

    protected $fillable = [
        'booking_id',
        'job_type',
        'particular',
        'description',
        'material_rate',
        'labour_rate',
        'schedule_rate',
        'quantity',
        'material_total',
        'labour_total',
        'unit_measure',
        'job_total',
    ];

    protected $casts = [
        'material_rate'  => 'decimal:2',
        'labour_rate'    => 'decimal:2',
        'schedule_rate'  => 'decimal:2',
        'quantity'       => 'decimal:2',
        'material_total' => 'decimal:2',
        'labour_total'   => 'decimal:2',
        'job_total'      => 'decimal:2',
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
        $mat = round((float)$this->material_rate * (float)$this->quantity, 2);
        $lab = round((float)$this->labour_rate * (float)$this->quantity, 2);
        $sch = round((float)$this->schedule_rate * (float)$this->quantity, 2);
        return round($mat + $lab + $sch, 2);
    }
}
