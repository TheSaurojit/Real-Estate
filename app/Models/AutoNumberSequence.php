<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoNumberSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'entity_type',
        'prefix',
        'next_number',
        'padding',
        'description',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the formatted preview of the next ID without incrementing
     */
    public function getPreviewAttribute(): string
    {
        return $this->prefix . str_pad($this->next_number, $this->padding, '0', STR_PAD_LEFT);
    }
}
