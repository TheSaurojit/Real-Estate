<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCancellationRefund;
use App\Models\Project;
use App\Models\Transaction;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CancellationRefundController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Show Cancellation Refund Settlement Interface
     */
    public function show(Project $project, Booking $booking): View
    {
        $refund = $booking->cancellationRefund ?? new BookingCancellationRefund([
            'booking_id'                  => $booking->id,
            'total_received_bank'         => $booking->total_taxable_received,
            'total_received_cash'         => $booking->total_cash_received,
            'cancellation_fee'            => $booking->cancellation_charge,
            'refundable_to_bank_loan'     => $booking->bankFinance?->disbursed_amount ?? 0,
            'refundable_to_party_taxable' => max(0, $booking->total_taxable_received - ($booking->bankFinance?->disbursed_amount ?? 0) - $booking->cancellation_charge),
            'refundable_to_party_cash'    => $booking->total_cash_received,
            'status'                      => 'pending',
        ]);

        $bankAccounts = BankAccount::where('company_id', $project->company_id)->get();

        return view('project.cancellations.refund', compact('project', 'booking', 'refund', 'bankAccounts'));
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

        // Create Payment Voucher
        Transaction::create([
            'project_id'             => $project->id,
            'company_id'             => $companyId,
            'booking_id'             => $booking->id,
            'bank_account_id'        => $validated['bank_account_id'] ?? null,
            'transaction_code'       => $pmtCode,
            'voucher_date'           => $validated['refund_date'],
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'transaction_mode'       => $validated['payment_mode'],
            'source_of_payment'      => 'self',
            'amount'                 => $amount,
            'instrument_ref_no'      => $validated['ref_no'],
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => $isTaxable,
            'particulars'            => 'Cancellation refund payout: ' . ucfirst(str_replace('_', ' ', $validated['refund_target'])) . '. ' . ($validated['remarks'] ?? ''),
            'created_by'             => auth()->id(),
        ]);

        // Update / create refund tracking row
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
        $totalRefundable = (float)$refund->total_received_bank + (float)$refund->total_received_cash - (float)$refund->cancellation_fee;

        if ($totalRefunded >= $totalRefundable) {
            $refund->status = 'completed';
        } else {
            $refund->status = 'partially_refunded';
        }

        $refund->save();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Cancellation refund payout of ₹" . number_format($amount, 2) . " processed successfully! Payment Voucher: {$pmtCode}");
    }
}
