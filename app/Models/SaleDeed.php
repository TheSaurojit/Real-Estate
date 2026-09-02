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
        'sub_registrar_office',
        'status',
        'remarks',
    ];

    protected $casts = [
        'executed_date' => 'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
