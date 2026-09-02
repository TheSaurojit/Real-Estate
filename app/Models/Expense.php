<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'company_id',
        'supplier_id',
        'bank_account_id',
        'expense_code',
        'expense_date',
        'expense_category',
        'item_name',
        'description',
        'quantity',
        'unit_measure',
        'unit_rate',
        'sub_total',
        'tax_rate',
        'tax_amount',
        'gross_amount',
        'invoice_no',
        'invoice_date',
        'payment_mode',
        'payment_status',
        'payment_ref_no',
        'created_by',
    ];

    protected $casts = [
        'expense_date'   => 'date',
        'invoice_date'   => 'date',
        'quantity'       => 'decimal:2',
        'unit_rate'      => 'decimal:2',
        'sub_total'      => 'decimal:2',
        'tax_rate'       => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'gross_amount'   => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
