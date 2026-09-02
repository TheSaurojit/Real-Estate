<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankFinance;
use App\Models\Booking;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankFinanceController extends Controller
{
    /**
     * Update Bank Finance (Home Loan) details
     */
    public function update(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'finance_status'    => ['required', 'in:not_applicable,applied,sanctioned,disbursed'],
            'bank_name'         => ['nullable', 'string', 'max:150'],
            'branch_name'       => ['nullable', 'string', 'max:150'],
            'loan_account_no'   => ['nullable', 'string', 'max:100'],
            'sanctioned_amount' => ['nullable', 'numeric', 'min:0'],
            'disbursed_amount'  => ['nullable', 'numeric', 'min:0'],
            'sanction_date'     => ['nullable', 'date'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        $bankFinance = $booking->bankFinance ?? new BankFinance(['booking_id' => $booking->id]);

        $bankFinance->fill([
            'finance_status'    => $validated['finance_status'],
            'bank_name'         => $validated['bank_name'],
            'branch_name'       => $validated['branch_name'],
            'loan_account_no'   => $validated['loan_account_no'],
            'sanctioned_amount' => $validated['sanctioned_amount'] ?? 0,
            'disbursed_amount'  => $validated['disbursed_amount'] ?? 0,
            'sanction_date'     => $validated['sanction_date'],
            'remarks'           => $validated['remarks'],
        ]);
        $bankFinance->save();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', 'Bank Finance / Home Loan details updated successfully!');
    }
}
