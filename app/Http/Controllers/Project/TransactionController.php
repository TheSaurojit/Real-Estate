<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Project;
use App\Models\Transaction;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Display project transactions ledger
     */
    public function index(Request $request, Project $project): View
    {
        $query = $project->bookings()
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->select('transactions.*')
            ->with(['booking', 'bankAccount', 'creator'])
            ->latest('voucher_date');

        // Or query directly through transactions linked to project
        $query = Transaction::where('project_id', $project->id)
            ->with(['booking', 'bankAccount', 'creator'])
            ->latest('voucher_date')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('instrument_ref_no', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($bq) use ($search) {
                      $bq->where('customer_name', 'like', "%{$search}%")
                         ->orWhere('booking_code', 'like', "%{$search}%")
                         ->orWhere('unit_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'pending_cheques') {
                $query->where('instrument_status', 'pending_clearance');
            } elseif ($type === 'dishonored') {
                $query->where('instrument_status', 'dishonored');
            } else {
                $query->where('voucher_type', $type);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->input('to_date'));
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Project Financial Totals
        $stats = [
            'total_bank_received' => (float)Transaction::where('project_id', $project->id)
                ->where('voucher_type', 'money_receipt')
                ->whereIn('instrument_status', ['cleared', 'not_applicable'])
                ->sum('amount'),

            'total_cash_received' => (float)Transaction::where('project_id', $project->id)
                ->where('voucher_type', 'receipt_voucher')
                ->whereIn('instrument_status', ['cleared', 'not_applicable'])
                ->sum('amount'),

            'total_refunds' => (float)Transaction::where('project_id', $project->id)
                ->where('voucher_category', 'payment_refund')
                ->sum('amount'),

            'pending_cheques_count' => Transaction::where('project_id', $project->id)
                ->where('instrument_status', 'pending_clearance')
                ->count(),

            'dishonored_count' => Transaction::where('project_id', $project->id)
                ->where('instrument_status', 'dishonored')
                ->count(),
        ];

        return view('project.transactions.index', compact('project', 'transactions', 'stats'));
    }

    /**
     * Show the form for creating a new receipt / transaction
     */
    public function create(Request $request, Project $project): View
    {
        $companyId = $project->company_id;
        $selectedBooking = null;

        if ($request->filled('booking_id')) {
            $selectedBooking = $project->bookings()
                ->with(['customizations', 'saleAgreement', 'bankFinance', 'transactions'])
                ->findOrFail($request->input('booking_id'));
        }

        $bookings = $project->bookings()->where('status', '!=', 'cancelled')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where(function ($q) use ($project) {
                $q->where('project_id', $project->id)->orWhereNull('project_id');
            })
            ->get();

        $nextReceiptCode = $this->autoNumberService->peekNextNumber('receipt', $companyId);
        $nextPaymentCode = $this->autoNumberService->peekNextNumber('payment', $companyId);

        return view('project.transactions.create', compact(
            'project',
            'bookings',
            'bankAccounts',
            'selectedBooking',
            'nextReceiptCode',
            'nextPaymentCode'
        ));
    }

    /**
     * Store a newly created transaction (Money Receipt, Receipt Voucher, Payment)
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id'              => ['required', 'exists:bookings,id'],
            'voucher_type'            => ['required', 'in:money_receipt,receipt_voucher,payment_voucher,adjustment_voucher'],
            'voucher_date'            => ['required', 'date'],
            'transaction_mode'        => ['required', 'in:cash,cheque,neft,rtgs,dd,upi,bank_transfer'],
            'source_of_payment'       => ['required', 'in:self,through_loan_account'],
            'amount'                  => ['required', 'numeric', 'min:0.01'],
            'bank_account_id'         => ['nullable', 'exists:bank_accounts,id'],
            'instrument_ref_no'       => ['nullable', 'string', 'max:100'],
            'instrument_date'         => ['nullable', 'date'],
            'issuing_bank'            => ['nullable', 'string', 'max:150'],
            'issuing_branch'          => ['nullable', 'string', 'max:150'],
            'particulars'             => ['nullable', 'string', 'max:500'],
        ]);

        $companyId = $project->company_id;
        $booking = $project->bookings()->findOrFail($validated['booking_id']);

        $isTaxable = ($validated['voucher_type'] === 'money_receipt');
        $isPayment = ($validated['voucher_type'] === 'payment_voucher');
        $entitySequence = $isPayment ? 'payment' : 'receipt';
        $transactionCode = $this->autoNumberService->getNextNumber($entitySequence, $companyId, true);

        // Determine instrument status
        $instrumentStatus = 'not_applicable';
        if (in_array($validated['transaction_mode'], ['cheque', 'dd'])) {
            $instrumentStatus = 'pending_clearance';
        } elseif ($validated['transaction_mode'] !== 'cash') {
            $instrumentStatus = 'cleared';
        }

        $voucherCategory = 'receipt';
        if ($isPayment) {
            $voucherCategory = 'payment_refund';
        } elseif ($validated['voucher_type'] === 'adjustment_voucher') {
            $voucherCategory = 'adjustment';
        }

        $transaction = Transaction::create([
            'project_id'             => $project->id,
            'company_id'             => $companyId,
            'booking_id'             => $booking->id,
            'bank_account_id'        => $validated['bank_account_id'] ?? null,
            'transaction_code'       => $transactionCode,
            'voucher_date'           => $validated['voucher_date'],
            'voucher_category'       => $voucherCategory,
            'voucher_type'           => $validated['voucher_type'],
            'transaction_mode'       => $validated['transaction_mode'],
            'source_of_payment'      => $validated['source_of_payment'],
            'amount'                 => (float)$validated['amount'],
            'instrument_ref_no'      => $validated['instrument_ref_no'] ?? null,
            'instrument_date'        => $validated['instrument_date'] ?? null,
            'issuing_bank'           => $validated['issuing_bank'] ?? null,
            'issuing_branch'         => $validated['issuing_branch'] ?? null,
            'instrument_status'      => $instrumentStatus,
            'is_taxable_transaction' => $isTaxable,
            'particulars'            => $validated['particulars'] ?? null,
            'created_by'             => auth()->id(),
        ]);

        // If source is loan account and cleared, update disbursed_amount on bank_finances
        if ($validated['source_of_payment'] === 'through_loan_account' && $instrumentStatus === 'cleared') {
            $loan = $booking->bankFinance;
            if ($loan) {
                $loan->disbursed_amount = round((float)$loan->disbursed_amount + (float)$validated['amount'], 2);
                if ($loan->finance_status === 'applied' || $loan->finance_status === 'not_applicable') {
                    $loan->finance_status = 'disbursed';
                }
                $loan->save();
            }
        }

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Transaction recorded successfully! Voucher ID: {$transaction->transaction_code}");
    }

    /**
     * Show single transaction voucher details
     */
    public function show(Project $project, Transaction $transaction): View
    {
        $transaction->load(['booking.project', 'booking.company', 'bankAccount', 'creator']);
        return view('project.transactions.show', compact('project', 'transaction'));
    }

    /**
     * Update clearance or dishonor status for cheques/drafts
     */
    public function updateStatus(Request $request, Project $project, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'instrument_status'       => ['required', 'in:cleared,dishonored'],
            'dishonor_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'dishonor_date'           => ['nullable', 'date'],
            'dishonor_remarks'        => ['nullable', 'string', 'max:500'],
        ]);

        $transaction->instrument_status = $validated['instrument_status'];

        if ($validated['instrument_status'] === 'dishonored') {
            $transaction->dishonor_penalty_amount = (float)($validated['dishonor_penalty_amount'] ?? 500);
            $transaction->dishonor_date = $validated['dishonor_date'] ?? date('Y-m-d');
            $transaction->dishonor_remarks = $validated['dishonor_remarks'];
        }

        $transaction->save();

        $booking = $transaction->booking;
        $statusLabel = $validated['instrument_status'] === 'cleared' ? 'Cleared' : 'Dishonored';

        return redirect()->back()
            ->with('success', "Cheque / Instrument marked as {$statusLabel} for Voucher {$transaction->transaction_code}.");
    }

    /**
     * Show Cross-Ledger Rebalance Wizard
     */
    public function createAdjustment(Project $project, Booking $booking): View
    {
        $companyId = $project->company_id;
        $bankAccounts = BankAccount::where('company_id', $companyId)->get();

        return view('project.transactions.adjustment', compact('project', 'booking', 'bankAccounts'));
    }

    /**
     * Execute Cross-Ledger Rebalance (Bank Excess -> Cash Receipt)
     */
    public function storeAdjustment(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment_amount' => ['required', 'numeric', 'min:0.01'],
            'bank_account_id'   => ['required', 'exists:bank_accounts,id'],
            'adjustment_date'   => ['required', 'date'],
            'remarks'           => ['nullable', 'string', 'max:500'],
        ]);

        $companyId = $project->company_id;
        $amount = (float)$validated['adjustment_amount'];

        // 1. Create Payment / Refund Voucher from Bank
        $pmtCode = $this->autoNumberService->getNextNumber('payment', $companyId, true);
        Transaction::create([
            'project_id'             => $project->id,
            'company_id'             => $companyId,
            'booking_id'             => $booking->id,
            'bank_account_id'        => $validated['bank_account_id'],
            'transaction_code'       => $pmtCode,
            'voucher_date'           => $validated['adjustment_date'],
            'voucher_category'       => 'adjustment',
            'voucher_type'           => 'adjustment_voucher',
            'transaction_mode'       => 'bank_transfer',
            'source_of_payment'      => 'self',
            'amount'                 => $amount,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'particulars'            => 'Cross-ledger rebalance outflow: excess taxable banking collection adjusted to cash ledger. ' . ($validated['remarks'] ?? ''),
            'created_by'             => auth()->id(),
        ]);

        // 2. Create Receipt Voucher into Cash Account
        $rcdCode = $this->autoNumberService->getNextNumber('receipt', $companyId, true);
        Transaction::create([
            'project_id'             => $project->id,
            'company_id'             => $companyId,
            'booking_id'             => $booking->id,
            'bank_account_id'        => null, // Cash in hand
            'transaction_code'       => $rcdCode,
            'voucher_date'           => $validated['adjustment_date'],
            'voucher_category'       => 'adjustment',
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'source_of_payment'      => 'self',
            'amount'                 => $amount,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => false,
            'particulars'            => 'Cross-ledger rebalance inflow: credit received into cash account from bank rebalancing. ' . ($validated['remarks'] ?? ''),
            'created_by'             => auth()->id(),
        ]);

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Cross-Ledger Adjustment of ₹" . number_format($amount, 2) . " executed successfully! Outflow Voucher: {$pmtCode}, Cash Inflow Voucher: {$rcdCode}");
    }
}
