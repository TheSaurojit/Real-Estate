<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'company_id',
        'booking_code',
        'booking_date',
        'is_landowner_allocation',
        // Property Specs
        'block_name',
        'floor_no',
        'unit_no',
        'built_up_area',
        'super_built_up_area',
        'property_type',
        'parking_type',
        'parking_no',
        'reference_source',
        // Customer Info
        'customer_salutation',
        'customer_name',
        'guardian_relation',
        'guardian_name',
        'mobile_no',
        'alt_mobile_no',
        'email_id',
        'address',
        'pan_number',
        'gstin',
        'photo_id_type',
        'photo_id_no',
        // Pricing & Tax
        'rate_per_sqft',
        'unit_cost',
        'parking_cost',
        'transformer_cost',
        'amenities_cost',
        'gross_total',
        'discount_applied',
        'consideration_value',
        'tax_name',
        'tax_rate',
        'tax_amount',
        'supplementary_value',
        'total_booking_value',
        // Dual Ledger Split
        'taxable_agreement_value',
        'taxable_gst_value',
        'gross_taxable_value',
        'gross_cash_value',
        // Status & Lifecycle
        'status',
        'cancellation_date',
        'cancellation_charge',
        'cancellation_remarks',
        'created_by',
    ];

    protected $casts = [
        'booking_date'            => 'date',
        'is_landowner_allocation' => 'boolean',
        'built_up_area'           => 'decimal:2',
        'super_built_up_area'     => 'decimal:2',
        'rate_per_sqft'           => 'decimal:2',
        'unit_cost'               => 'decimal:2',
        'parking_cost'            => 'decimal:2',
        'transformer_cost'        => 'decimal:2',
        'amenities_cost'          => 'decimal:2',
        'gross_total'             => 'decimal:2',
        'discount_applied'        => 'decimal:2',
        'consideration_value'     => 'decimal:2',
        'tax_rate'                => 'decimal:2',
        'tax_amount'              => 'decimal:2',
        'supplementary_value'     => 'decimal:2',
        'total_booking_value'     => 'decimal:2',
        'taxable_agreement_value' => 'decimal:2',
        'taxable_gst_value'       => 'decimal:2',
        'gross_taxable_value'     => 'decimal:2',
        'gross_cash_value'        => 'decimal:2',
        'cancellation_date'       => 'date',
        'cancellation_charge'     => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customizations(): HasMany
    {
        return $this->hasMany(BookingCustomization::class);
    }

    public function saleAgreement(): HasOne
    {
        return $this->hasOne(SaleAgreement::class);
    }

    public function bankFinance(): HasOne
    {
        return $this->hasOne(BankFinance::class);
    }

    public function saleDeed(): HasOne
    {
        return $this->hasOne(SaleDeed::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cancellationRefund(): HasOne
    {
        return $this->hasOne(BookingCancellationRefund::class);
    }

    /**
     * Total cleared Taxable Banking Receipts (Money Receipts)
     */
    public function getTotalTaxableReceivedAttribute(): float
    {
        return (float)$this->transactions()
            ->where('is_taxable_transaction', true)
            ->where('voucher_type', 'money_receipt')
            ->whereIn('instrument_status', ['cleared', 'not_applicable'])
            ->sum('amount');
    }

    /**
     * Outstanding Taxable Due Balance
     */
    public function getTaxableDueBalanceAttribute(): float
    {
        return max(0, round((float)$this->gross_taxable_value - $this->total_taxable_received, 2));
    }

    /**
     * Total cleared Non-Taxable Cash Receipts (Receipt Vouchers)
     */
    public function getTotalCashReceivedAttribute(): float
    {
        return (float)$this->transactions()
            ->where('is_taxable_transaction', false)
            ->where('voucher_type', 'receipt_voucher')
            ->whereIn('instrument_status', ['cleared', 'not_applicable'])
            ->sum('amount');
    }

    /**
     * Outstanding Cash Due Balance
     */
    public function getCashDueBalanceAttribute(): float
    {
        return max(0, round((float)$this->gross_cash_value - $this->total_cash_received, 2));
    }

    /**
     * Total Bounced Cheque Penalties
     */
    public function getTotalDishonorPenaltiesAttribute(): float
    {
        return (float)$this->transactions()
            ->where('instrument_status', 'dishonored')
            ->sum('dishonor_penalty_amount');
    }

    /**
     * Total Net Outstanding Customer Due
     */
    public function getTotalOutstandingDueAttribute(): float
    {
        return round($this->taxable_due_balance + $this->cash_due_balance + $this->total_dishonor_penalties, 2);
    }

    /**
     * Total Payments/Refunds made to Customer/Bank
     */
    public function getTotalRefundedAttribute(): float
    {
        return (float)$this->transactions()
            ->where('voucher_category', 'payment_refund')
            ->sum('amount');
    }

    /**
     * Recalculates all pricing, consideration, supplementary job sheets, and dual-accounting splits
     */
    public function recalculateTotals(): self
    {
        // 1. Unit Cost = Rate * Super Built-up Area
        $this->unit_cost = round((float)$this->rate_per_sqft * (float)$this->super_built_up_area, 2);

        // 2. Gross Total = Unit Cost + Parking + Transformer + Amenities
        $this->gross_total = round(
            (float)$this->unit_cost +
            (float)$this->parking_cost +
            (float)$this->transformer_cost +
            (float)$this->amenities_cost,
            2
        );

        // 3. Consideration Value = Gross Total - Discount
        $this->consideration_value = max(0, round((float)$this->gross_total - (float)$this->discount_applied, 2));

        // 4. Base GST Tax Amount
        $this->tax_amount = round(((float)$this->consideration_value * (float)$this->tax_rate) / 100, 2);

        // 5. Supplementary Value from Customization Job Sheets (Addons - Dislodges)
        $addons = (float)$this->customizations()->where('job_type', 'addon')->sum('job_total');
        $dislodges = (float)$this->customizations()->where('job_type', 'dislodge')->sum('job_total');
        $this->supplementary_value = round($addons - $dislodges, 2);

        // 6. Total Booking Value
        $this->total_booking_value = round((float)$this->consideration_value + (float)$this->tax_amount + (float)$this->supplementary_value, 2);

        // 7. Dual Accounting Split
        $saleAgr = $this->saleAgreement()->first();
        if ($saleAgr && (float)$saleAgr->agreement_value > 0) {
            $agrValue = (float)$saleAgr->agreement_value;
            $taxRate = (float)$saleAgr->tax_rate;
            $taxValue = round(($agrValue * $taxRate) / 100, 2);
            $grossTaxable = round($agrValue + $taxValue, 2);
            $grossCash = max(0, round((float)$this->total_booking_value - $agrValue, 2));

            $this->taxable_agreement_value = $agrValue;
            $this->taxable_gst_value = $taxValue;
            $this->gross_taxable_value = $grossTaxable;
            $this->gross_cash_value = $grossCash;

            // Sync with saleAgreement model
            $saleAgr->update([
                'tax_value'           => $taxValue,
                'gross_taxable_value' => $grossTaxable,
                'cash_value'          => $grossCash,
            ]);
        } else {
            // Default split before formal Sale Agreement is executed
            $this->taxable_agreement_value = $this->consideration_value;
            $this->taxable_gst_value = $this->tax_amount;
            $this->gross_taxable_value = round((float)$this->consideration_value + (float)$this->tax_amount, 2);
            $this->gross_cash_value = (float)$this->supplementary_value;
        }

        $this->save();
        return $this;
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = [];
        if ($this->block_name) $parts[] = 'Block: ' . $this->block_name;
        if ($this->floor_no) $parts[] = 'Floor: ' . $this->floor_no;
        $parts[] = 'Unit/Flat No: ' . $this->unit_no;
        return implode(', ', $parts);
    }
}
