<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCustomization;
use App\Models\Expense;
use App\Models\Project;
use App\Models\StockTransfer;
use App\Models\Transaction;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(protected ReportExportService $exportService)
    {
    }

    /**
     * Reports Hub Index
     */
    public function index(Request $request, Project $project): View
    {
        return view('project.reports.index', compact('project'));
    }

    /**
     * 2.3.1.1 Booking Report - General Information
     */
    public function bookingsGeneral(Request $request, Project $project): Response|View
    {
        $query = $project->bookings()
            ->with(['saleAgreement', 'bankFinance', 'saleDeed', 'creator'])
            ->latest('booking_date');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('booking_code', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('mobile_no', 'like', "%{$s}%")
                  ->orWhere('email_id', 'like', "%{$s}%")
                  ->orWhere('unit_no', 'like', "%{$s}%")
                  ->orWhere('block_name', 'like', "%{$s}%")
                  ->orWhere('pan_number', 'like', "%{$s}%")
                  ->orWhere('gstin', 'like', "%{$s}%")
                  ->orWhere('reference_source', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            if ($request->input('category') === 'landowner') {
                $query->where('is_landowner_allocation', true);
            } elseif ($request->input('category') === 'purchaser') {
                $query->where('is_landowner_allocation', false);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('booking_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('booking_date', '<=', $request->input('to_date'));
        }

        // Export to Excel
        if ($request->input('export') === 'excel') {
            $headers = [
                'Booking ID',
                'Booking Date',
                'Customer Name',
                'Contact No',
                'Email ID',
                'PAN',
                'GSTIN',
                'Category',
                'Block',
                'Floor',
                'Unit No',
                'Unit Type',
                'Parking Status & No',
                'Built-up Area (Sq.Ft.)',
                'Super Built-up Area (Sq.Ft.)',
                'Reference/Source',
                'Booking Status',
                'Gross Booking Value (₹)',
                'Sale Agreement Status',
                'Sale Agreement Value (₹)',
                'Sale Agreement Details',
                'Finance Status',
                'Finance Value (₹)',
                'Finance Details',
                'Sale Deed Status',
                'Sale Deed Value (₹)',
                'Sale Deed Details',
            ];

            $rows = [];
            foreach ($query->get() as $b) {
                $rows[] = [
                    $b->booking_code,
                    $b->booking_date ? $b->booking_date->format('d-m-Y') : '',
                    $b->customer_name,
                    $b->mobile_no,
                    $b->email_id,
                    $b->pan_number,
                    $b->gstin,
                    $b->is_landowner_allocation ? 'Landowner' : 'Purchaser',
                    $b->block_name,
                    $b->floor_no,
                    $b->unit_no,
                    $b->property_type,
                    $b->parking_type ? (ucfirst(str_replace('_', ' ', $b->parking_type)) . ($b->parking_no ? ' (' . $b->parking_no . ')' : '')) : 'N/A',
                    number_format($b->built_up_area, 2, '.', ''),
                    number_format($b->super_built_up_area, 2, '.', ''),
                    $b->reference_source,
                    strtoupper($b->status),
                    number_format($b->gross_booking_value > 0 ? $b->gross_booking_value : $b->total_booking_value, 2, '.', ''),
                    $b->saleAgreement?->agreement_status ?? 'Pending',
                    number_format($b->saleAgreement?->agreement_value ?? 0, 2, '.', ''),
                    $b->saleAgreement?->document_details ?? ($b->saleAgreement?->agreement_serial_no ?? 'N/A'),
                    $b->bankFinance?->finance_status ?? 'Not Applicable',
                    number_format($b->bankFinance?->sanctioned_amount ?? 0, 2, '.', ''),
                    $b->bankFinance ? ($b->bankFinance->bank_name . ($b->bankFinance->loan_account_no ? ' - ' . $b->bankFinance->loan_account_no : '')) : 'N/A',
                    $b->saleDeed?->status ?? 'Pending',
                    number_format($b->saleDeed?->sale_deed_value ?? 0, 2, '.', ''),
                    $b->saleDeed?->sale_deed_no ?? 'N/A',
                ];
            }

            return $this->exportService->streamCsv("{$project->project_code}_Bookings_General_Report_" . date('Y-m-d') . ".csv", $headers, $rows);
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('project.reports.bookings_general', compact('project', 'bookings'));
    }

    /**
     * 2.3.1.2 Booking Report - Financial Information (Live vs Cancelled)
     */
    public function bookingsFinancial(Request $request, Project $project): Response|View
    {
        $statusFilter = $request->input('status', 'live');

        $query = $project->bookings()
            ->with(['saleAgreement', 'bankFinance', 'customizations', 'transactions']);

        if ($statusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        } else {
            $query->where('status', '!=', 'cancelled');
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('booking_code', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('unit_no', 'like', "%{$s}%")
                  ->orWhere('block_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('booking_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('booking_date', '<=', $request->input('to_date'));
        }

        // Export to Excel
        if ($request->input('export') === 'excel') {
            $headers = [
                'Booking ID',
                'Booking Date',
                'Customer Name',
                'Category',
                'Block - Floor - Unit No',
                'Consideration Value (a)',
                'Addons (b)',
                'Dislodge (c)',
                'Supplement Chgs (d=b-c)',
                'Discount & Adjustments (e)',
                'Booking Value (f=(a+d)-e)',
                'Sale Agreement Value',
                'Taxable Value (g)',
                'Tax Name & Duty',
                'Tax Value (h)',
                'Gross Taxable Value (i=g+h)',
                'Final Booking Value (j=f+h)',
                'Bank Finance (k)',
                'Recd. From Loan A/C',
                'Adjustments Loan A/C',
                'Bal. Due Loan A/C',
                'Party Self-Taxable (i-k)',
                'Recd. From Self-Taxable A/C',
                'Adjustments Self-Taxable A/C',
                'Bal. Due Self-Taxable A/C',
                'Non-Taxable Value (L=j-i)',
                'Recd. From Non-Taxable A/C',
                'Adjustments Non-Taxable A/C',
                'Bal. Due Non-Taxable A/C',
                'Total Taxable Value',
                'Total Taxable Receipts',
                'Balance Due (Taxable)',
                'Total Non-Taxable Value',
                'Total Non-Taxable Receipts',
                'Balance Due (Non-Taxable)',
                'Total Receipts Cleared',
                'Net Balance Due',
            ];

            $rows = [];
            foreach ($query->get() as $b) {
                $consideration_a = (float)$b->consideration_value;
                $addons_b = (float)$b->customizations->where('job_type', 'addon')->sum('job_total');
                $dislodge_c = (float)$b->customizations->where('job_type', 'dislodge')->sum('job_total');
                $supplement_d = $addons_b - $dislodge_c;
                $discount_e = (float)$b->discount_applied + (float)$b->adjustments;
                $booking_value_f = ($consideration_a + $supplement_d) - $discount_e;
                $agreement_val = (float)($b->saleAgreement?->agreement_value ?? $consideration_a);
                $taxable_val_g = (float)($b->taxable_agreement_value ?: $consideration_a);
                $tax_name_duty = ($b->tax_name ?: 'GST') . ' (' . number_format($b->tax_rate, 2) . '%)';
                $tax_val_h = (float)$b->tax_amount;
                $gross_taxable_i = (float)$b->gross_taxable_value;
                $final_booking_j = (float)$b->final_booking_value ?: ($booking_value_f + $tax_val_h);
                $loan_k = (float)($b->bankFinance?->sanctioned_amount ?? 0);
                $loan_recd = (float)$b->loan_received;
                $loan_adj = (float)$b->loan_refunded;
                $loan_due = max(0, round($loan_k - $loan_recd, 2));
                $party_self_target = max(0, round($gross_taxable_i - $loan_k, 2));
                $self_tax_recd = (float)$b->taxable_self_received;
                $self_tax_adj = (float)$b->taxable_self_refunded;
                $self_tax_due = max(0, round($party_self_target - $self_tax_recd, 2));
                $non_tax_val_L = max(0, round($final_booking_j - $gross_taxable_i, 2));
                $non_tax_recd = (float)$b->total_cash_received;
                $non_tax_adj = (float)$b->cash_refunded;
                $non_tax_due = max(0, round($non_tax_val_L - $non_tax_recd, 2));
                $total_tax_val = $gross_taxable_i;
                $total_tax_recd = (float)$b->total_taxable_received;
                $bal_due_tax = (float)$b->taxable_due_balance;
                $total_nontax_val = (float)$b->gross_cash_value;
                $total_nontax_recd = (float)$b->total_cash_received;
                $bal_due_nontax = (float)$b->cash_due_balance;
                $total_receipt = (float)$b->total_receipts_cleared;
                $balance_due = (float)$b->total_outstanding_due;

                $rows[] = [
                    $b->booking_code,
                    $b->booking_date ? $b->booking_date->format('d-m-Y') : '',
                    $b->customer_name,
                    $b->is_landowner_allocation ? 'Landowner' : 'Purchaser',
                    trim("{$b->block_name} - {$b->floor_no} - {$b->unit_no}", ' -'),
                    number_format($consideration_a, 2, '.', ''),
                    number_format($addons_b, 2, '.', ''),
                    number_format($dislodge_c, 2, '.', ''),
                    number_format($supplement_d, 2, '.', ''),
                    number_format($discount_e, 2, '.', ''),
                    number_format($booking_value_f, 2, '.', ''),
                    number_format($agreement_val, 2, '.', ''),
                    number_format($taxable_val_g, 2, '.', ''),
                    $tax_name_duty,
                    number_format($tax_val_h, 2, '.', ''),
                    number_format($gross_taxable_i, 2, '.', ''),
                    number_format($final_booking_j, 2, '.', ''),
                    number_format($loan_k, 2, '.', ''),
                    number_format($loan_recd, 2, '.', ''),
                    number_format($loan_adj, 2, '.', ''),
                    number_format($loan_due, 2, '.', ''),
                    number_format($party_self_target, 2, '.', ''),
                    number_format($self_tax_recd, 2, '.', ''),
                    number_format($self_tax_adj, 2, '.', ''),
                    number_format($self_tax_due, 2, '.', ''),
                    number_format($non_tax_val_L, 2, '.', ''),
                    number_format($non_tax_recd, 2, '.', ''),
                    number_format($non_tax_adj, 2, '.', ''),
                    number_format($non_tax_due, 2, '.', ''),
                    number_format($total_tax_val, 2, '.', ''),
                    number_format($total_tax_recd, 2, '.', ''),
                    number_format($bal_due_tax, 2, '.', ''),
                    number_format($total_nontax_val, 2, '.', ''),
                    number_format($total_nontax_recd, 2, '.', ''),
                    number_format($bal_due_nontax, 2, '.', ''),
                    number_format($total_receipt, 2, '.', ''),
                    number_format($balance_due, 2, '.', ''),
                ];
            }

            $tabLabel = $statusFilter === 'cancelled' ? 'Cancelled' : 'Live';
            return $this->exportService->streamCsv("{$project->project_code}_Bookings_Financial_{$tabLabel}_Report_" . date('Y-m-d') . ".csv", $headers, $rows);
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('project.reports.bookings_financial', compact('project', 'bookings', 'statusFilter'));
    }

    /**
     * 2.3.2 Receipt Report
     */
    public function receipts(Request $request, Project $project): Response|View
    {
        $query = Transaction::where('project_id', $project->id)
            ->whereIn('voucher_type', ['money_receipt', 'receipt_voucher'])
            ->with(['booking', 'bankAccount'])
            ->latest('voucher_date');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('transaction_code', 'like', "%{$s}%")
                  ->orWhere('voucher_no', 'like', "%{$s}%")
                  ->orWhere('instrument_ref_no', 'like', "%{$s}%")
                  ->orWhereHas('booking', function ($bq) use ($s) {
                      $bq->where('customer_name', 'like', "%{$s}%")
                         ->orWhere('unit_no', 'like', "%{$s}%")
                         ->orWhere('block_name', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->input('voucher_type'));
        }

        if ($request->filled('instrument_status')) {
            $query->where('instrument_status', $request->input('instrument_status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->input('to_date'));
        }

        // Export to Excel
        if ($request->input('export') === 'excel') {
            $headers = [
                'Transaction ID',
                'Transaction Date',
                'Transaction Category',
                'Voucher No',
                'Customer Name',
                'Block - Floor - Unit No',
                'Amount (₹)',
                'Mode of Receipt',
                'Instrument Details',
                'Instrument Status',
                'Fine Imposed (₹)',
                'Credited A/C',
                'Remarks (Loan A/C / Direct)',
            ];

            $rows = [];
            foreach ($query->get() as $t) {
                $categoryLabel = $t->voucher_type === 'money_receipt'
                    ? 'Money Receipt (Taxable)'
                    : 'Receipt Voucher (Cash)';

                $unitInfo = $t->booking
                    ? trim("{$t->booking->block_name} - {$t->booking->floor_no} - {$t->booking->unit_no}", ' -')
                    : 'N/A';

                $sourceLabel = $t->source_of_payment === 'through_loan_account' ? 'Loan A/C' : 'Direct (Self)';
                $remarks = $sourceLabel . ($t->particulars ? ' - ' . $t->particulars : '');

                $rows[] = [
                    $t->transaction_code,
                    $t->voucher_date ? $t->voucher_date->format('d-m-Y') : '',
                    $categoryLabel,
                    $t->voucher_no ?? '',
                    $t->booking?->customer_name ?? 'N/A',
                    $unitInfo,
                    number_format($t->amount, 2, '.', ''),
                    strtoupper($t->transaction_mode),
                    $t->instrument_ref_no ? ($t->instrument_ref_no . ($t->instrument_date ? ' dt ' . $t->instrument_date->format('d-m-Y') : '')) : 'N/A',
                    ucfirst(str_replace('_', ' ', $t->instrument_status)),
                    number_format($t->dishonor_penalty_amount ?? 0, 2, '.', ''),
                    $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash in Hand',
                    $remarks,
                ];
            }

            return $this->exportService->streamCsv("{$project->project_code}_Receipts_Report_" . date('Y-m-d') . ".csv", $headers, $rows);
        }

        $receipts = $query->paginate(20)->withQueryString();

        return view('project.reports.receipts', compact('project', 'receipts'));
    }

    /**
     * 2.3.3 Refund / Payment Report
     */
    public function refunds(Request $request, Project $project): Response|View
    {
        $query = Transaction::where('project_id', $project->id)
            ->where(function ($q) {
                $q->where('voucher_type', 'payment_voucher')
                  ->orWhere('voucher_category', 'payment_refund');
            })
            ->with(['booking', 'bankAccount'])
            ->latest('voucher_date');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('transaction_code', 'like', "%{$s}%")
                  ->orWhere('voucher_no', 'like', "%{$s}%")
                  ->orWhere('instrument_ref_no', 'like', "%{$s}%")
                  ->orWhereHas('booking', function ($bq) use ($s) {
                      $bq->where('customer_name', 'like', "%{$s}%")
                         ->orWhere('unit_no', 'like', "%{$s}%")
                         ->orWhere('block_name', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('payment_category')) {
            $query->where('payment_category', $request->input('payment_category'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->input('to_date'));
        }

        // Export to Excel
        if ($request->input('export') === 'excel') {
            $headers = [
                'Transaction ID',
                'Transaction Date',
                'Transaction Category',
                'Voucher No',
                'Customer Name',
                'Block - Floor - Unit No',
                'Amount (₹)',
                'Mode of Payment',
                'Instrument Details',
                'Instrument Status',
                'Debited A/C',
                'Paid To (Loan A/C / Direct)',
                'Particulars / Remarks',
            ];

            $rows = [];
            foreach ($query->get() as $t) {
                $cat = ($t->payment_category ?? ($t->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable'
                    ? 'Taxable'
                    : 'Non-Taxable';

                $unitInfo = $t->booking
                    ? trim("{$t->booking->block_name} - {$t->booking->floor_no} - {$t->booking->unit_no}", ' -')
                    : 'N/A';

                $paidTo = $t->source_of_payment === 'through_loan_account' ? 'Loan A/C' : 'Direct Customer';

                $rows[] = [
                    $t->transaction_code,
                    $t->voucher_date ? $t->voucher_date->format('d-m-Y') : '',
                    $cat,
                    $t->voucher_no ?? '',
                    $t->booking?->customer_name ?? 'N/A',
                    $unitInfo,
                    number_format($t->amount, 2, '.', ''),
                    strtoupper($t->transaction_mode),
                    $t->instrument_ref_no ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $t->instrument_status)),
                    $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash in Hand',
                    $paidTo,
                    $t->particulars ?? '',
                ];
            }

            return $this->exportService->streamCsv("{$project->project_code}_Refunds_Report_" . date('Y-m-d') . ".csv", $headers, $rows);
        }

        $refunds = $query->paginate(20)->withQueryString();

        return view('project.reports.refunds', compact('project', 'refunds'));
    }

    /**
     * 2.3.5 Booking Customization Report (Itemized Job Sheets)
     */
    public function customizations(Request $request, Project $project): Response|View
    {
        $query = BookingCustomization::whereHas('booking', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })->with('booking')->latest('id');

        if ($request->filled('particular')) {
            $query->where('particular', $request->input('particular'));
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->input('job_type'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('particular', 'like', "%{$s}%")
                  ->orWhereHas('booking', function ($bq) use ($s) {
                      $bq->where('customer_name', 'like', "%{$s}%")
                         ->orWhere('booking_code', 'like', "%{$s}%")
                         ->orWhere('unit_no', 'like', "%{$s}%");
                  });
            });
        }

        // Export to Excel
        if ($request->input('export') === 'excel') {
            $headers = [
                'Booking ID',
                'Customer Name',
                'Block - Floor - Unit No',
                'Job Type',
                'Particulars',
                'Description',
                'Material Rate (₹)',
                'Labour Rate (₹)',
                'Quantity',
                'Unit',
                'Material Total (₹)',
                'Labour Total (₹)',
                'Item Total (₹)',
            ];

            $rows = [];
            foreach ($query->get() as $c) {
                $b = $c->booking;
                $unitInfo = $b ? trim("{$b->block_name} - {$b->floor_no} - {$b->unit_no}", ' -') : 'N/A';

                $rows[] = [
                    $b?->booking_code ?? 'N/A',
                    $b?->customer_name ?? 'N/A',
                    $unitInfo,
                    strtoupper($c->job_type),
                    $c->particular,
                    $c->description,
                    number_format($c->material_rate, 2, '.', ''),
                    number_format($c->labour_rate, 2, '.', ''),
                    number_format($c->quantity, 2, '.', ''),
                    $c->unit_measure ?? 'Units',
                    number_format($c->material_total, 2, '.', ''),
                    number_format($c->labour_total, 2, '.', ''),
                    number_format($c->job_total, 2, '.', ''),
                ];
            }

            return $this->exportService->streamCsv("{$project->project_code}_Booking_Customizations_Report_" . date('Y-m-d') . ".csv", $headers, $rows);
        }

        $customizations = $query->paginate(20)->withQueryString();
        $availableParticulars = BookingCustomization::PARTICULARS;

        return view('project.reports.customizations', compact('project', 'customizations', 'availableParticulars'));
    }

    /**
     * 2.3.6 Quick Summary - Booking (Single-Click 360° Financial Dashboard)
     */
    public function quickSummary(Request $request, Project $project): View
    {
        $projectBookings = $project->bookings()->latest('booking_date')->get();

        $selectedBooking = null;
        if ($request->filled('booking_id')) {
            $selectedBooking = $project->bookings()
                ->with(['saleAgreement', 'bankFinance', 'saleDeed', 'customizations', 'transactions.bankAccount'])
                ->find($request->input('booking_id'));
        }

        if (!$selectedBooking && $projectBookings->isNotEmpty()) {
            $selectedBooking = $projectBookings->first();
            $selectedBooking->load(['saleAgreement', 'bankFinance', 'saleDeed', 'customizations', 'transactions.bankAccount']);
        }

        // Transactions breakdown by stream
        $loanReceipts = collect();
        $partyTaxableReceipts = collect();
        $cashReceipts = collect();

        // 4-Stream Summary Reconciliation Table Data
        $financialSummary = [
            'booking_summary' => ['loan' => 0, 'taxable' => 0, 'cash' => 0, 'gross' => 0],
            'sales_receipt'   => ['loan' => 0, 'taxable' => 0, 'cash' => 0, 'gross' => 0],
            'payment_adj'     => ['loan' => 0, 'taxable' => 0, 'cash' => 0, 'gross' => 0],
            'net_sales'       => ['loan' => 0, 'taxable' => 0, 'cash' => 0, 'gross' => 0],
            'balance_due'     => ['loan' => 0, 'taxable' => 0, 'cash' => 0, 'gross' => 0],
        ];

        if ($selectedBooking) {
            $txs = $selectedBooking->transactions;

            // 1. Bank Finance receipts
            $loanReceipts = $txs->filter(function ($t) {
                return $t->source_of_payment === 'through_loan_account'
                    && $t->voucher_category !== 'payment_refund'
                    && $t->voucher_type !== 'payment_voucher';
            })->values();

            // 2. Party Taxable receipts
            $partyTaxableReceipts = $txs->filter(function ($t) {
                return $t->source_of_payment !== 'through_loan_account'
                    && $t->is_taxable_transaction
                    && $t->voucher_category !== 'payment_refund'
                    && $t->voucher_type !== 'payment_voucher';
            })->values();

            // 3. Non-Taxable / Cash receipts
            $cashReceipts = $txs->filter(function ($t) {
                return !$t->is_taxable_transaction
                    && $t->voucher_category !== 'payment_refund'
                    && $t->voucher_type !== 'payment_voucher';
            })->values();

            // 4. Reconciliation Table calculations
            $loanTarget = (float)($selectedBooking->bankFinance?->sanctioned_amount ?? 0);
            $grossTaxable = (float)$selectedBooking->gross_taxable_value;
            $partyTaxTarget = max(0, round($grossTaxable - $loanTarget, 2));
            $cashTarget = (float)$selectedBooking->gross_cash_value;
            $grossTotalBooking = (float)$selectedBooking->final_booking_value ?: (float)$selectedBooking->total_booking_value;

            $financialSummary['booking_summary'] = [
                'loan'    => $loanTarget,
                'taxable' => $partyTaxTarget,
                'cash'    => $cashTarget,
                'gross'   => $grossTotalBooking,
            ];

            // Sales-Receipt (Total collections per stream)
            $loanRecd = (float)$selectedBooking->loan_received;
            $partyTaxRecd = (float)$selectedBooking->taxable_self_received;
            $cashRecd = (float)$selectedBooking->total_cash_received;
            $grossRecd = $loanRecd + $partyTaxRecd + $cashRecd;

            $financialSummary['sales_receipt'] = [
                'loan'    => $loanRecd,
                'taxable' => $partyTaxRecd,
                'cash'    => $cashRecd,
                'gross'   => $grossRecd,
            ];

            // Payment Adjustments (Refunds & Outflows per stream)
            $loanAdj = (float)$selectedBooking->loan_refunded;
            $partyTaxAdj = (float)$selectedBooking->taxable_self_refunded;
            $cashAdj = (float)$selectedBooking->cash_refunded;
            $grossAdj = $loanAdj + $partyTaxAdj + $cashAdj;

            $financialSummary['payment_adj'] = [
                'loan'    => $loanAdj,
                'taxable' => $partyTaxAdj,
                'cash'    => $cashAdj,
                'gross'   => $grossAdj,
            ];

            // Net Sales-Receipt = Sales-Receipt - Payment Adjustments
            $netLoan = $loanRecd - $loanAdj;
            $netTax = $partyTaxRecd - $partyTaxAdj;
            $netCash = $cashRecd - $cashAdj;
            $netGross = $grossRecd - $grossAdj;

            $financialSummary['net_sales'] = [
                'loan'    => $netLoan,
                'taxable' => $netTax,
                'cash'    => $netCash,
                'gross'   => $netGross,
            ];

            // Balance Due = Booking Summary - Net Sales-Receipt
            $dueLoan = max(0, round($loanTarget - $netLoan, 2));
            $dueTax = max(0, round($partyTaxTarget - $netTax, 2));
            $dueCash = max(0, round($cashTarget - $netCash, 2));
            $dueGross = $dueLoan + $dueTax + $dueCash;

            $financialSummary['balance_due'] = [
                'loan'    => $dueLoan,
                'taxable' => $dueTax,
                'cash'    => $dueCash,
                'gross'   => $dueGross,
            ];
        }

        return view('project.reports.quick_summary', compact(
            'project',
            'projectBookings',
            'selectedBooking',
            'loanReceipts',
            'partyTaxableReceipts',
            'cashReceipts',
            'financialSummary'
        ));
    }

    /**
     * Report: Project Profitability & Cost vs Revenue Analysis
     */
    public function profitability(Request $request, Project $project): View
    {
        // 1. Revenue
        $activeBookings = $project->bookings()->where('status', '!=', 'cancelled')->get();
        $projectedRevenue = (float)$activeBookings->sum('total_booking_value');
        $realizedBank = (float)Transaction::where('project_id', $project->id)
            ->where('voucher_type', 'money_receipt')
            ->whereIn('instrument_status', ['cleared', 'not_applicable'])
            ->sum('amount');
        $realizedCash = (float)Transaction::where('project_id', $project->id)
            ->where('voucher_type', 'receipt_voucher')
            ->whereIn('instrument_status', ['cleared', 'not_applicable'])
            ->sum('amount');
        $totalRealizedInflow = $realizedBank + $realizedCash;

        // 2. Expenses
        $materialCost = (float)Expense::where('project_id', $project->id)->where('expense_category', 'material_purchase')->sum('gross_amount');
        $laborCost = (float)Expense::where('project_id', $project->id)->where('expense_category', 'labor_contractor')->sum('gross_amount');
        $overheadCost = (float)Expense::where('project_id', $project->id)->whereIn('expense_category', ['site_overheads', 'machinery_equipment', 'statutory_permits', 'administrative'])->sum('gross_amount');

        // Stock Transfer net impact (Inward transfers add cost, Outward transfers reduce cost)
        $inwardStock = (float)StockTransfer::where('destination_project_id', $project->id)->sum('total_transfer_value');
        $outwardStock = (float)StockTransfer::where('source_project_id', $project->id)->sum('total_transfer_value');
        $netStockImpact = $inwardStock - $outwardStock;

        $totalDirectExpenses = $materialCost + $laborCost + $overheadCost + $netStockImpact;

        // Profit Margins
        $projectedGrossProfit = $projectedRevenue - $totalDirectExpenses;
        $projectedProfitMargin = $projectedRevenue > 0 ? round(($projectedGrossProfit / $projectedRevenue) * 100, 2) : 0;

        $realizedCashFlowNet = $totalRealizedInflow - $totalDirectExpenses;

        return view('project.reports.profitability', compact(
            'project',
            'projectedRevenue',
            'realizedBank',
            'realizedCash',
            'totalRealizedInflow',
            'materialCost',
            'laborCost',
            'overheadCost',
            'inwardStock',
            'outwardStock',
            'netStockImpact',
            'totalDirectExpenses',
            'projectedGrossProfit',
            'projectedProfitMargin',
            'realizedCashFlowNet'
        ));
    }

    /**
     * Report: Unit Sales & Inventory Velocity Report
     */
    public function inventory(Request $request, Project $project): View
    {
        $bookings = $project->bookings()->get();

        $totalUnits = $bookings->count();
        $liveUnits = $bookings->where('status', 'live')->count();
        $agreementDone = $bookings->where('status', 'executed_agreement')->count();
        $deedDone = $bookings->where('status', 'registered_deed')->count();
        $cancelledUnits = $bookings->where('status', 'cancelled')->count();

        $totalSoldArea = (float)$bookings->where('status', '!=', 'cancelled')->sum('super_built_up_area');
        $totalSoldValue = (float)$bookings->where('status', '!=', 'cancelled')->sum('total_booking_value');
        $avgRatePerSqft = $totalSoldArea > 0 ? round($totalSoldValue / $totalSoldArea, 2) : 0;

        return view('project.reports.inventory', compact(
            'project',
            'bookings',
            'totalUnits',
            'liveUnits',
            'agreementDone',
            'deedDone',
            'cancelledUnits',
            'totalSoldArea',
            'totalSoldValue',
            'avgRatePerSqft'
        ));
    }

    /**
     * Report: Customer Dues & Aging Analysis
     */
    public function aging(Request $request, Project $project): View
    {
        $bookings = $project->bookings()
            ->where('status', '!=', 'cancelled')
            ->with(['transactions', 'customizations'])
            ->get();

        $now = Carbon::now();
        $buckets = [
            'under_30'  => 0,
            '30_to_60'  => 0,
            '60_to_90'  => 0,
            'above_90'  => 0,
        ];

        $agedBookings = [];

        foreach ($bookings as $b) {
            $due = $b->total_outstanding_due;
            if ($due > 0) {
                $days = $now->diffInDays($b->booking_date);
                if ($days <= 30) {
                    $buckets['under_30'] += $due;
                    $bucketName = '0 - 30 Days';
                } elseif ($days <= 60) {
                    $buckets['30_to_60'] += $due;
                    $bucketName = '31 - 60 Days';
                } elseif ($days <= 90) {
                    $buckets['60_to_90'] += $due;
                    $bucketName = '61 - 90 Days';
                } else {
                    $buckets['above_90'] += $due;
                    $bucketName = '90+ Days';
                }

                $agedBookings[] = [
                    'booking'     => $b,
                    'due'         => $due,
                    'taxable_due' => $b->taxable_due_balance,
                    'cash_due'    => $b->cash_due_balance,
                    'days'        => $days,
                    'bucket'      => $bucketName,
                ];
            }
        }

        // Bounced Cheques Defaulters
        $dishonoredTx = Transaction::where('project_id', $project->id)
            ->where('instrument_status', 'dishonored')
            ->with('booking')
            ->get();

        return view('project.reports.aging', compact('project', 'buckets', 'agedBookings', 'dishonoredTx'));
    }

    /**
     * Report: Banking Escrow & Cash Account Flow Report
     */
    public function banking(Request $request, Project $project): View
    {
        $bankAccounts = BankAccount::where('company_id', $project->company_id)->get();

        $accountSummaries = [];
        foreach ($bankAccounts as $acc) {
            $inflows = (float)Transaction::where('bank_account_id', $acc->id)
                ->where('voucher_category', '!=', 'payment_refund')
                ->whereIn('instrument_status', ['cleared', 'not_applicable'])
                ->sum('amount');

            $outflows = (float)Transaction::where('bank_account_id', $acc->id)
                ->where('voucher_category', 'payment_refund')
                ->sum('amount');

            $expenseOutflows = (float)Expense::where('bank_account_id', $acc->id)
                ->where('payment_status', 'paid')
                ->sum('gross_amount');

            $netBalance = $inflows - ($outflows + $expenseOutflows);

            $accountSummaries[] = [
                'account'          => $acc,
                'inflows'          => $inflows,
                'refund_outflows'  => $outflows,
                'expense_outflows' => $expenseOutflows,
                'net_flow'         => $netBalance,
            ];
        }

        // Cash Account Summary
        $cashInflows = (float)Transaction::where('project_id', $project->id)
            ->whereNull('bank_account_id')
            ->where('voucher_category', '!=', 'payment_refund')
            ->sum('amount');

        $cashExpenses = (float)Expense::where('project_id', $project->id)
            ->where('payment_mode', 'cash')
            ->sum('gross_amount');

        $cashRefunds = (float)Transaction::where('project_id', $project->id)
            ->whereNull('bank_account_id')
            ->where('voucher_category', 'payment_refund')
            ->sum('amount');

        $netCash = $cashInflows - ($cashExpenses + $cashRefunds);

        return view('project.reports.banking', compact('project', 'accountSummaries', 'cashInflows', 'cashExpenses', 'cashRefunds', 'netCash'));
    }
}
