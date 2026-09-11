<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'sale_deed_no',
        'executed_date',
        'sale_deed_value',
        'sub_registrar_office',
        'executed_in',
        'status',
        'remarks',
    ];

    protected $casts = [
        'executed_date'   => 'date',
        'sale_deed_value' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
