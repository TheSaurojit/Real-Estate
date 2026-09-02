<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Project;
use App\Models\SaleAgreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleAgreementController extends Controller
{
    /**
     * Update/Execute Sale Agreement (Bayna-nama) and calculate Dual Accounting split
     */
    public function update(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'agreement_status'    => ['required', 'in:pending,drafted,executed,registered'],
            'agreement_serial_no' => ['nullable', 'string', 'max:100'],
            'execution_date'      => ['nullable', 'date'],
            'agreement_value'     => ['required', 'numeric', 'min:0'],
            'tax_rate'            => ['required', 'numeric', 'min:0', 'max:100'],
            'document_details'    => ['nullable', 'string', 'max:1000'],
        ]);

        $agrValue = (float)$validated['agreement_value'];
        $taxRate = (float)$validated['tax_rate'];
        $taxValue = round(($agrValue * $taxRate) / 100, 2);
        $grossTaxable = round($agrValue + $taxValue, 2);
        $cashValue = max(0, round((float)$booking->total_booking_value - $agrValue, 2));

        $saleAgreement = $booking->saleAgreement ?? new SaleAgreement(['booking_id' => $booking->id]);

        $saleAgreement->fill([
            'agreement_status'    => $validated['agreement_status'],
            'agreement_serial_no' => $validated['agreement_serial_no'],
            'execution_date'      => $validated['execution_date'],
            'agreement_value'     => $agrValue,
            'tax_rate'            => $taxRate,
            'tax_value'           => $taxValue,
            'gross_taxable_value' => $grossTaxable,
            'cash_value'          => $cashValue,
            'document_details'    => $validated['document_details'],
        ]);
        $saleAgreement->save();

        if ($validated['agreement_status'] === 'executed' && $booking->status === 'live') {
            $booking->status = 'executed_agreement';
        }

        // Recalculate booking dual accounting fields
        $booking->recalculateTotals();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', 'Sale Agreement (Bayna-nama) details & Dual Accounting split updated successfully!');
    }
}
