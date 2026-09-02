<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'source_project_id',
        'destination_project_id',
        'transfer_code',
        'transfer_date',
        'material_name',
        'quantity',
        'unit_measure',
        'unit_cost',
        'total_transfer_value',
        'vehicle_no',
        'challan_no',
        'remarks',
        'transferred_by',
    ];

    protected $casts = [
        'transfer_date'        => 'date',
        'quantity'             => 'decimal:2',
        'unit_cost'            => 'decimal:2',
        'total_transfer_value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sourceProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'source_project_id');
    }

    public function destinationProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'destination_project_id');
    }

    public function transferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
