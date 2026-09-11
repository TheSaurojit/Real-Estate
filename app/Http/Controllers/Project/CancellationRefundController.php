<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCancellationRefund;
use App\Models\Project;
use App\Models\Transaction;
use App\Services\AutoNumberService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CancellationRefundController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Show Stage 2.1.6 Booking Cancellation & Refund Screen
     */
    public function show(Request $request, Project $project, ?Booking $booking = null): View
    {
        if (!$booking || !$booking->exists) {
            $bookingId = $request->input('booking_id');
            $booking = $bookingId 
                ? $project->bookings()->with(['customizations', 'saleAgreement', 'bankFinance', 'saleDeed', 'transactions'])->findOrFail($bookingId)
                : $project->bookings()->with(['customizations', 'saleAgreement', 'bankFinance', 'saleDeed', 'transactions'])->firstOrFail();
        } else {
            $booking->load(['customizations', 'saleAgreement', 'bankFinance', 'saleDeed', 'transactions']);
        }

        // Projects list for project selector
        $projects = Project::where('company_id', $project->company_id)->where('is_active', true)->get();
        // Bookings in current project for customer selector
        $projectBookings = $project->bookings()->latest('booking_date')->get();

        // 3-Account calculations
        // (a) Loan A/C
        $loanTotal = (float)($booking->bankFinance?->sanctioned_amount ?? 0);
        $loanReceived = (float)$booking->loan_received;
        $loanRefunded = (float)$booking->loan_refunded;

        // (b) Self (taxable) A/C
        $selfTaxableTotal = max(0, round((float)$booking->gross_taxable_value - $loanTotal, 2));
        $selfTaxableReceived = (float)$booking->taxable_self_received;
        $selfTaxableRefunded = (float)$booking->taxable_self_refunded;

        // (c) Total Taxable A/C
        $totalTaxableTotal = round($loanTotal + $selfTaxableTotal, 2);
        $totalTaxableReceived = round($loanReceived + $selfTaxableReceived, 2);
        $totalTaxableRefunded = round($loanRefunded + $selfTaxableRefunded, 2);

        // (d) Self (non-taxable / cash) A/C
        $selfCashTotal = max(0, round((float)$booking->final_booking_value - (float)$booking->gross_taxable_value, 2));
        $selfCashReceived = (float)$booking->cash_received;
        $selfCashRefunded = (float)$booking->cash_refunded;

        // (e) Gross Total
        $grossTotal = round($totalTaxableTotal + $selfCashTotal, 2);
        $grossReceived = round($totalTaxableReceived + $selfCashReceived, 2);
        $grossRefunded = round($totalTaxableRefunded + $selfCashRefunded, 2);

        // Refund Record
        $refund = $booking->cancellationRefund ?? new BookingCancellationRefund([
            'booking_id'                  => $booking->id,
            'loan_refund_instruction'    => $loanReceived,
            'taxable_refund_instruction' => $selfTaxableReceived,
            'cash_refund_instruction'    => $selfCashReceived,
            'status'                      => 'pending',
        ]);

        // Default instructions if not set or 0
        $loanInstruction = (float)($refund->loan_refund_instruction > 0 ? $refund->loan_refund_instruction : $loanReceived);
        $taxableInstruction = (float)($refund->taxable_refund_instruction > 0 ? $refund->taxable_refund_instruction : $selfTaxableReceived);
        $cashInstruction = (float)($refund->cash_refund_instruction > 0 ? $refund->cash_refund_instruction : $selfCashReceived);
        $totalTaxableInstruction = round($loanInstruction + $taxableInstruction, 2);
        $grossInstruction = round($totalTaxableInstruction + $cashInstruction, 2);

        // Calculate days gap
        $cancellationDate = $booking->cancellation_date ?? now();
        $daysGap = $booking->booking_date ? Carbon::parse($booking->booking_date)->diffInDays(Carbon::parse($cancellationDate)) : 0;

        $bankAccounts = BankAccount::where('company_id', $project->company_id)->get();

        return view('project.cancellations.index', compact(
            'project',
            'projects',
            'projectBookings',
            'booking',
            'refund',
            'bankAccounts',
            'daysGap',
            'loanTotal', 'loanReceived', 'loanInstruction', 'loanRefunded',
            'selfTaxableTotal', 'selfTaxableReceived', 'taxableInstruction', 'selfTaxableRefunded',
            'totalTaxableTotal', 'totalTaxableReceived', 'totalTaxableInstruction', 'totalTaxableRefunded',
            'selfCashTotal', 'selfCashReceived', 'cashInstruction', 'selfCashRefunded',
            'grossTotal', 'grossReceived', 'grossInstruction', 'grossRefunded'
        ));
    }

    /**
     * Update Booking Status (Live <-> Cancelled) with Safeguard Validations
     */
    public function updateStatus(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status'                     => ['required', 'in:live,cancelled'],
            'cancellation_date'          => ['nullable', 'date'],
            'cancellation_remarks'       => ['nullable', 'string', 'max:500'],
            'loan_refund_instruction'    => ['nullable', 'numeric', 'min:0'],
            'taxable_refund_instruction' => ['nullable', 'numeric', 'min:0'],
            'cash_refund_instruction'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $newStatus = $validated['status'];
        $totalRefunded = (float)$booking->total_refunded;

        // SCENARIO 2: Updating status from Cancelled to Live
        if ($newStatus === 'live' && $booking->status === 'cancelled') {
            if ($totalRefunded > 0) {
                return redirect()->back()->withErrors([
                    'status' => "Cannot change status to LIVE! A refund sum of ₹" . number_format($totalRefunded, 2) . " has already been executed for this booking. System safeguards prohibit activating a booking until all refund payout transactions are deleted or reversed."
                ])->withInput();
            }

            // Restore status safely
            $restoreStatus = 'live';
            if ($booking->saleDeed && in_array($booking->saleDeed->status, ['executed', 'registered'])) {
                $restoreStatus = 'registered_deed';
            } elseif ($booking->saleAgreement && $booking->saleAgreement->agreement_status === 'executed') {
                $restoreStatus = 'executed_agreement';
            }

            $booking->update([
                'status'                => $restoreStatus,
                'cancellation_date'     => null,
                'cancellation_days_gap' => null,
                'cancellation_charge'   => 0,
            ]);

            return redirect()->route('project.bookings.cancellation.show', [$project->id, $booking->id])
                ->with('success', "Booking status has been restored to LIVE successfully.");
        }

        // SCENARIO 1: Cancelling a booking (Live -> Cancelled)
        if ($newStatus === 'cancelled') {
            $cancelDate = $validated['cancellation_date'] ? Carbon::parse($validated['cancellation_date']) : now();
            $daysGap = $booking->booking_date ? Carbon::parse($booking->booking_date)->diffInDays($cancelDate) : 0;

            $loanInst = (float)($validated['loan_refund_instruction'] ?? $booking->loan_received);
            $taxInst = (float)($validated['taxable_refund_instruction'] ?? $booking->taxable_self_received);
            $cashInst = (float)($validated['cash_refund_instruction'] ?? $booking->cash_received);

            // Validate that refund instruction >= refunded amt
            if ($loanInst < $booking->loan_refunded) {
                throw ValidationException::withMessages([
                    'loan_refund_instruction' => "Loan refund instruction (₹" . number_format($loanInst, 2) . ") cannot be less than already refunded amount (₹" . number_format($booking->loan_refunded, 2) . ")."
                ]);
            }
            if ($taxInst < $booking->taxable_self_refunded) {
                throw ValidationException::withMessages([
                    'taxable_refund_instruction' => "Taxable refund instruction (₹" . number_format($taxInst, 2) . ") cannot be less than already refunded amount (₹" . number_format($booking->taxable_self_refunded, 2) . ")."
                ]);
            }
            if ($cashInst < $booking->cash_refunded) {
                throw ValidationException::withMessages([
                    'cash_refund_instruction' => "Cash refund instruction (₹" . number_format($cashInst, 2) . ") cannot be less than already refunded amount (₹" . number_format($booking->cash_refunded, 2) . ")."
                ]);
            }

            $chargeLoan = max(0, round($booking->loan_received - $loanInst, 2));
            $chargeTaxable = max(0, round($booking->taxable_self_received - $taxInst, 2));
            $chargeCash = max(0, round($booking->cash_received - $cashInst, 2));
            $totalCharge = round($chargeLoan + $chargeTaxable + $chargeCash, 2);

            $booking->update([
                'status'                => 'cancelled',
                'cancellation_date'     => $cancelDate->format('Y-m-d'),
                'cancellation_days_gap' => $daysGap,
                'cancellation_charge'   => $totalCharge,
                'cancellation_remarks'  => $validated['cancellation_remarks'] ?? $request->input('remarks') ?? $booking->cancellation_remarks,
            ]);

            $refund = $booking->cancellationRefund ?? new BookingCancellationRefund(['booking_id' => $booking->id]);
            $refund->fill([
                'total_received_bank'         => $booking->total_taxable_received,
                'total_received_cash'         => $booking->total_cash_received,
                'cancellation_fee'            => $totalCharge,
                'loan_refund_instruction'    => $loanInst,
                'taxable_refund_instruction' => $taxInst,
                'cash_refund_instruction'    => $cashInst,
                'cancellation_charge_loan'    => $chargeLoan,
                'cancellation_charge_taxable' => $chargeTaxable,
                'cancellation_charge_cash'    => $chargeCash,
                'refundable_to_bank_loan'     => $loanInst,
                'refundable_to_party_taxable' => $taxInst,
                'refundable_to_party_cash'    => $cashInst,
                'remarks'                     => $validated['cancellation_remarks'] ?? $request->input('remarks') ?? $refund->remarks,
            ]);
            $refund->save();

            return redirect()->route('project.bookings.cancellation.show', [$project->id, $booking->id])
                ->with('success', "Booking marked as CANCELLED. Gap: {$daysGap} days. Cancellation charge: ₹" . number_format($totalCharge, 2));
        }

        return redirect()->route('project.bookings.cancellation.show', [$project->id, $booking->id]);
    }

    /**
     * SCENARIO 3: Update Refund Instructions for a Cancelled Booking
     */
    public function updateInstructions(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'loan_refund_instruction'    => ['required', 'numeric', 'min:0'],
            'taxable_refund_instruction' => ['required', 'numeric', 'min:0'],
            'cash_refund_instruction'    => ['required', 'numeric', 'min:0'],
            'remarks'                     => ['nullable', 'string', 'max:500'],
        ]);

        $loanInst = (float)$validated['loan_refund_instruction'];
        $taxInst = (float)$validated['taxable_refund_instruction'];
        $cashInst = (float)$validated['cash_refund_instruction'];

        // Strict rule: Refund instruction shall be equal or higher than the Refunded amt. always
        if ($loanInst < $booking->loan_refunded) {
            return redirect()->back()->withErrors([
                'loan_refund_instruction' => "Loan refund instruction (₹" . number_format($loanInst, 2) . ") cannot be set lower than already refunded amount (₹" . number_format($booking->loan_refunded, 2) . ")."
            ])->withInput();
        }
        if ($taxInst < $booking->taxable_self_refunded) {
            return redirect()->back()->withErrors([
                'taxable_refund_instruction' => "Self (taxable) refund instruction (₹" . number_format($taxInst, 2) . ") cannot be set lower than already refunded amount (₹" . number_format($booking->taxable_self_refunded, 2) . ")."
            ])->withInput();
        }
        if ($cashInst < $booking->cash_refunded) {
            return redirect()->back()->withErrors([
                'cash_refund_instruction' => "Self (non-taxable) refund instruction (₹" . number_format($cashInst, 2) . ") cannot be set lower than already refunded amount (₹" . number_format($booking->cash_refunded, 2) . ")."
            ])->withInput();
        }

        $chargeLoan = max(0, round($booking->loan_received - $loanInst, 2));
        $chargeTaxable = max(0, round($booking->taxable_self_received - $taxInst, 2));
        $chargeCash = max(0, round($booking->cash_received - $cashInst, 2));
        $totalCharge = round($chargeLoan + $chargeTaxable + $chargeCash, 2);

        $booking->update([
            'cancellation_charge'  => $totalCharge,
            'cancellation_remarks' => $validated['remarks'] ?? $booking->cancellation_remarks,
        ]);

        $refund = $booking->cancellationRefund ?? new BookingCancellationRefund(['booking_id' => $booking->id]);
        $refund->fill([
            'loan_refund_instruction'    => $loanInst,
            'taxable_refund_instruction' => $taxInst,
            'cash_refund_instruction'    => $cashInst,
            'cancellation_charge_loan'    => $chargeLoan,
            'cancellation_charge_taxable' => $chargeTaxable,
            'cancellation_charge_cash'    => $chargeCash,
            'cancellation_fee'            => $totalCharge,
            'refundable_to_bank_loan'     => $loanInst,
            'refundable_to_party_taxable' => $taxInst,
            'refundable_to_party_cash'    => $cashInst,
            'remarks'                     => $validated['remarks'] ?? $refund->remarks,
        ]);
        $refund->save();

        return redirect()->route('project.bookings.cancellation.show', [$project->id, $booking->id])
            ->with('success', "Refund instructions and cancellation charges updated successfully.");
    }

    /**
     * Process / record cancellation refund payout
     */
    public function store(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'refund_target'   => ['required', 'in:bank_loan,party_taxable,party_cash'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'refund_date'     => ['required', 'date'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'payment_mode'    => ['required', 'in:cash,cheque,neft,rtgs,dd,bank_transfer'],
            'ref_no'          => ['nullable', 'string', 'max:100'],
            'remarks'         => ['nullable', 'string', 'max:500'],
        ]);

        $companyId = $project->company_id;
        $amount = (float)$validated['amount'];
        $pmtCode = $this->autoNumberService->getNextNumber('payment', $companyId, true);

        $isTaxable = ($validated['refund_target'] !== 'party_cash');
        $paymentCategory = $isTaxable ? 'taxable' : 'non_taxable';

        // Create Payment Voucher
        Transaction::create([
            'project_id'             => $project->id,
            'company_id'             => $companyId,
            'booking_id'             => $booking->id,
            'bank_account_id'        => $validated['bank_account_id'] ?? null,
            'transaction_code'       => $pmtCode,
            'voucher_no'             => $validated['ref_no'] ?? null,
            'voucher_date'           => $validated['refund_date'],
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'payment_category'       => $paymentCategory,
            'transaction_mode'       => $validated['payment_mode'],
            'source_of_payment'      => 'self',
            'amount'                 => $amount,
            'instrument_ref_no'      => $validated['ref_no'],
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => $isTaxable,
            'particulars'            => 'Cancellation refund payout: ' . ucfirst(str_replace('_', ' ', $validated['refund_target'])) . '. ' . ($validated['remarks'] ?? ''),
            'created_by'             => auth()->id(),
        ]);

        // Update refund tracking row
        $refund = $booking->cancellationRefund ?? new BookingCancellationRefund(['booking_id' => $booking->id]);

        $refund->total_received_bank = $booking->total_taxable_received;
        $refund->total_received_cash = $booking->total_cash_received;
        $refund->cancellation_fee = $booking->cancellation_charge;

        if ($validated['refund_target'] === 'bank_loan') {
            $refund->refunded_to_bank_loan = round((float)$refund->refunded_to_bank_loan + $amount, 2);
        } elseif ($validated['refund_target'] === 'party_taxable') {
            $refund->refunded_to_party_taxable = round((float)$refund->refunded_to_party_taxable + $amount, 2);
        } elseif ($validated['refund_target'] === 'party_cash') {
            $refund->refunded_to_party_cash = round((float)$refund->refunded_to_party_cash + $amount, 2);
        }

        $totalRefunded = (float)$refund->refunded_to_bank_loan + (float)$refund->refunded_to_party_taxable + (float)$refund->refunded_to_party_cash;
        $totalRefundable = (float)$refund->refundable_to_bank_loan + (float)$refund->refundable_to_party_taxable + (float)$refund->refundable_to_party_cash;

        if ($totalRefundable > 0 && $totalRefunded >= $totalRefundable) {
            $refund->status = 'completed';
        } else {
            $refund->status = 'partially_refunded';
        }

        $refund->save();

        if ($request->routeIs('project.bookings.cancellation-refund.store')) {
            return redirect()->route('project.bookings.show', [$project->id, $booking->id])
                ->with('success', "Cancellation refund payout of ₹" . number_format($amount, 2) . " processed successfully! Payment Voucher: {$pmtCode}");
        }

        return redirect()->route('project.bookings.cancellation.show', [$project->id, $booking->id])
            ->with('success', "Cancellation refund payout of ₹" . number_format($amount, 2) . " processed successfully! Payment Voucher: {$pmtCode}");
    }
}
