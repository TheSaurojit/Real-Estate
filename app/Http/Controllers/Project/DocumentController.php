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

class DocumentController extends Controller
{
    /**
     * Map of available document templates matching Section 2.4 Print documents
     */
    public const DOCUMENT_TYPES = [
        // 1. Booking confirmation letter
        'booking_confirmation'   => [
            'number'   => 1,
            'title'    => 'Booking confirmation letter',
            'category' => 'allotment',
            'icon'     => 'fa-file-circle-check',
            'color'    => 'sky',
            'desc'     => 'Initial welcome & booking letter with flat dimensions, pricing breakups, and notes.',
        ],
        // 2. Property allotment Letter
        'allotment_letter'       => [
            'number'   => 2,
            'title'    => 'Property allotment Letter',
            'category' => 'allotment',
            'icon'     => 'fa-file-signature',
            'color'    => 'indigo',
            'desc'     => 'Official legal allotment letter specifying Unit No, Floor, and 45-day agreement execution clause.',
        ],
        // 3. Demand Letter
        'demand_letter'          => [
            'number'   => 3,
            'title'    => 'Demand Letter',
            'category' => 'billing',
            'icon'     => 'fa-envelope-open-text',
            'color'    => 'amber',
            'desc'     => 'Payment milestone notice with builder bank details and Party Self vs Loan a/c summary.',
        ],
        // 4. Booking customization & Final bill
        'final_bill'             => [
            'number'   => 4,
            'title'    => 'Booking customization & Final bill',
            'category' => 'billing',
            'icon'     => 'fa-file-invoice-dollar',
            'color'    => 'emerald',
            'desc'     => 'Annexure-I engineering job sheet plus master final bill breakdown and financial summary matrix.',
        ],
        // 5. No objection/no due certificate
        'noc_certificate'        => [
            'number'   => 5,
            'title'    => 'No objection/no due certificate',
            'category' => 'legal',
            'icon'     => 'fa-landmark',
            'color'    => 'teal',
            'desc'     => 'NOC certifying developer ownership, purchaser rights, electricity/water clearance, and zero outstanding dues.',
        ],
        // 6. Possession cum Key handover certificate
        'possession_certificate' => [
            'number'   => 6,
            'title'    => 'Possession cum Key handover certificate',
            'category' => 'legal',
            'icon'     => 'fa-key',
            'color'    => 'purple',
            'desc'     => 'Physical handover certificate with inspection satisfaction, key delivery, and purchaser acceptance signature.',
        ],
        // 7. Booking Summary
        'booking_summary'        => [
            'number'   => 7,
            'title'    => 'Booking Summary',
            'category' => 'billing',
            'icon'     => 'fa-chart-pie',
            'color'    => 'blue',
            'desc'     => 'Live status overview with Agreement/Bank/Deed milestones and 4-stream financial reconciliation matrix.',
        ],
        // 8. Receipts
        'receipts'               => [
            'number'   => 8,
            'title'    => 'Receipts',
            'category' => 'receipts',
            'icon'     => 'fa-receipt',
            'color'    => 'emerald',
            'desc'     => 'Money Receipts (Taxable Banking) and Receipt Vouchers (Non-Taxable Cash) by Transaction ID or batch.',
        ],
        // 9. Payments
        'payments'               => [
            'number'   => 9,
            'title'    => 'Payments',
            'category' => 'receipts',
            'icon'     => 'fa-hand-holding-dollar',
            'color'    => 'rose',
            'desc'     => 'Taxable and Non-taxable payment & refund vouchers by Transaction ID or batch outflow.',
        ],
        // 10. Proforma Invoice
        'proforma_invoice'       => [
            'number'   => 10,
            'title'    => 'Proforma Invoice',
            'category' => 'billing',
            'icon'     => 'fa-file-invoice',
            'color'    => 'cyan',
            'desc'     => 'Commercial tax invoice with SAC Code 995411, CGST/SGST split, output tax totals, and terms.',
        ],
        // 11. Booking closing summary
        'booking_closing_summary'=> [
            'number'   => 11,
            'title'    => 'Booking closing summary',
            'category' => 'billing',
            'icon'     => 'fa-folder-closed',
            'color'    => 'slate',
            'desc'     => 'File closing statement with brokerage details, loan vs party breakups, and triple approval signatures.',
        ],

        // Backwards-compatibility aliases
        'bank_noc'                   => ['alias_of' => 'noc_certificate', 'title' => 'No objection/no due certificate', 'category' => 'legal', 'icon' => 'fa-landmark', 'color' => 'teal', 'desc' => 'NOC certificate'],
        'possession_letter'          => ['alias_of' => 'possession_certificate', 'title' => 'Possession cum Key handover certificate', 'category' => 'legal', 'icon' => 'fa-key', 'color' => 'purple', 'desc' => 'Possession certificate'],
        'money_receipt'              => ['alias_of' => 'receipts', 'title' => 'Money Receipt (Taxable Banking)', 'category' => 'receipts', 'icon' => 'fa-building-columns', 'color' => 'emerald', 'desc' => 'Taxable Receipt'],
        'cash_receipt_voucher'       => ['alias_of' => 'receipts', 'title' => 'Receipt Voucher (Non-Taxable Cash)', 'category' => 'receipts', 'icon' => 'fa-money-bill-wave', 'color' => 'amber', 'desc' => 'Cash Receipt'],
        'payment_voucher'            => ['alias_of' => 'payments', 'title' => 'Payment & Refund Voucher', 'category' => 'receipts', 'icon' => 'fa-hand-holding-dollar', 'color' => 'rose', 'desc' => 'Payment Voucher'],
        'customization_jobsheet'     => ['alias_of' => 'final_bill', 'title' => 'Booking customization & Final bill', 'category' => 'technical', 'icon' => 'fa-sliders', 'color' => 'indigo', 'desc' => 'Customization Job Sheet'],
        'customer_account_statement' => ['alias_of' => 'booking_closing_summary', 'title' => 'Booking closing summary', 'category' => 'billing', 'icon' => 'fa-scale-balanced', 'color' => 'blue', 'desc' => 'Account Ledger'],
        'cancellation_deed'          => ['number' => 12, 'title' => 'Booking Cancellation & Settlement Deed', 'category' => 'legal', 'icon' => 'fa-ban', 'color' => 'rose', 'desc' => 'Cancellation deed recording penalty and refund payouts.'],
    ];

    /**
     * Central Document Hub: lists templates and allows printing for any booking
     */
    public function index(Request $request, Project $project): View
    {
        $bookings = $project->bookings()
            ->with(['company', 'saleAgreement', 'bankFinance', 'customizations', 'transactions'])
            ->latest('id')
            ->get();

        $selectedBooking = null;
        if ($request->filled('booking_id')) {
            $selectedBooking = $project->bookings()
                ->with(['company', 'saleAgreement', 'bankFinance', 'customizations', 'transactions.bankAccount'])
                ->find($request->input('booking_id'));
        }

        if (!$selectedBooking && $bookings->isNotEmpty()) {
            $selectedBooking = $bookings->first();
            $selectedBooking->load(['company', 'saleAgreement', 'bankFinance', 'customizations', 'transactions.bankAccount']);
        }

        $documentTypes = self::DOCUMENT_TYPES;

        $receiptTxns = $selectedBooking
            ? $selectedBooking->transactions->whereIn('voucher_type', ['money_receipt', 'receipt_voucher'])->sortByDesc('id')
            : collect();

        $paymentTxns = $selectedBooking
            ? $selectedBooking->transactions->filter(fn($t) => $t->voucher_type === 'payment_voucher' || $t->voucher_category === 'payment_refund')->sortByDesc('id')
            : collect();

        return view('project.documents.index', compact(
            'project',
            'bookings',
            'selectedBooking',
            'documentTypes',
            'receiptTxns',
            'paymentTxns'
        ));
    }

    /**
     * Show document without a specified booking (e.g. specimen blank template or default to project's first booking)
     */
    public function showBlankOrSpecimen(Request $request, Project $project, string $documentType): View|RedirectResponse
    {
        if (!array_key_exists($documentType, self::DOCUMENT_TYPES)) {
            abort(404, "Requested document template '{$documentType}' does not exist.");
        }

        // If the project already has a customer booking, default to it
        $firstBooking = $project->bookings()
            ->with(['company', 'project', 'saleAgreement', 'bankFinance', 'saleDeed', 'customizations', 'transactions.bankAccount', 'cancellationRefund'])
            ->latest('id')
            ->first();

        if ($firstBooking) {
            return $this->show($request, $project, $firstBooking, $documentType);
        }

        if ($request->boolean('specimen')) {
            return $this->renderSpecimenDocument($request, $project, $documentType);
        }

        return redirect()->route('project.documents.index', $project->id)
            ->with('warning', 'Document printing is disabled because no customer bookings exist for this project yet. Please create a customer booking first.');
    }

    /**
     * Render a specific dynamic print-ready document template
     */
    public function show(Request $request, Project $project, mixed $booking, string $documentType): View|RedirectResponse
    {
        if (!array_key_exists($documentType, self::DOCUMENT_TYPES)) {
            abort(404, "Requested document template '{$documentType}' does not exist.");
        }

        if (!($booking instanceof Booking)) {
            $bookingModel = is_numeric($booking) ? $project->bookings()->find($booking) : null;
            if (!$bookingModel) {
                return $this->showBlankOrSpecimen($request, $project, $documentType);
            }
            $booking = $bookingModel;
        }

        // Handle Aliases
        $meta = self::DOCUMENT_TYPES[$documentType];
        $templateView = $meta['alias_of'] ?? $documentType;

        $booking->load(['company', 'project', 'saleAgreement', 'bankFinance', 'saleDeed', 'customizations', 'transactions.bankAccount', 'cancellationRefund']);
        $company = $booking->company;

        $escrowAccount = BankAccount::where('company_id', $company->id)
            ->where(function ($q) use ($project) {
                $q->where('project_id', $project->id)->orWhereNull('project_id');
            })
            ->first();

        // 1. Core Financial Calculations
        $consideration = (float)$booking->consideration_value;
        $addons = (float)$booking->customizations->where('job_type', 'addon')->sum('job_total');
        $dislodges = (float)$booking->customizations->where('job_type', 'dislodge')->sum('job_total');
        $supplementary = $addons - $dislodges;
        $grossBookingValue = $consideration + $supplementary;
        $discountApplied = (float)$booking->discount_applied;
        $adjustments = (float)$booking->adjustments;
        $totalDiscount = $discountApplied + $adjustments;
        $netBookingValue = $grossBookingValue - $totalDiscount;
        $taxableValue = (float)($booking->taxable_agreement_value ?: $consideration);
        $taxRate = (float)($booking->tax_rate ?: 5.00);
        $taxAmount = (float)$booking->tax_amount;
        $grossTaxable = (float)$booking->gross_taxable_value;
        $grossCash = (float)$booking->gross_cash_value;
        $finalBookingValue = (float)$booking->final_booking_value ?: ($netBookingValue + $taxAmount);
        $amountInWords = AutoNumberService::numberToIndianWords($finalBookingValue);

        $loanSanctioned = (float)($booking->bankFinance?->sanctioned_amount ?? 0);
        $partyTaxableTarget = max(0, round($grossTaxable - $loanSanctioned, 2));
        $cashTarget = $grossCash;

        // 2. Receipts Mode Filtering (Item 8)
        $receiptMode = $request->input('mode', 'all_receipts');
        if ($documentType === 'money_receipt') {
            $receiptMode = 'all_money_receipts';
        } elseif ($documentType === 'cash_receipt_voucher') {
            $receiptMode = 'all_receipt_vouchers';
        }

        if ($request->filled('transaction_id')) {
            $receiptMode = 'single';
            $receiptTransactions = $booking->transactions->where('id', $request->input('transaction_id'));
        } elseif ($receiptMode === 'all_money_receipts') {
            $receiptTransactions = $booking->transactions->where('voucher_type', 'money_receipt');
        } elseif ($receiptMode === 'all_receipt_vouchers') {
            $receiptTransactions = $booking->transactions->where('voucher_type', 'receipt_voucher');
        } else {
            $receiptTransactions = $booking->transactions->whereIn('voucher_type', ['money_receipt', 'receipt_voucher']);
        }

        // 3. Payments Mode Filtering (Item 9)
        $paymentMode = $request->input('mode', 'all_payments');
        if ($request->filled('transaction_id')) {
            $paymentMode = 'single';
            $paymentTransactions = $booking->transactions->where('id', $request->input('transaction_id'));
        } elseif ($paymentMode === 'all_taxable') {
            $paymentTransactions = $booking->transactions->filter(fn($t) =>
                ($t->voucher_type === 'payment_voucher' || $t->voucher_category === 'payment_refund')
                && ($t->payment_category === 'taxable' || $t->is_taxable_transaction)
            );
        } elseif ($paymentMode === 'all_nontaxable') {
            $paymentTransactions = $booking->transactions->filter(fn($t) =>
                ($t->voucher_type === 'payment_voucher' || $t->voucher_category === 'payment_refund')
                && ($t->payment_category === 'non_taxable' || !$t->is_taxable_transaction)
            );
        } else {
            $paymentTransactions = $booking->transactions->filter(fn($t) =>
                $t->voucher_type === 'payment_voucher' || $t->voucher_category === 'payment_refund'
            );
        }

        // Specific single transaction if requested
        $transaction = null;
        if ($request->filled('transaction_id')) {
            $transaction = Transaction::where('booking_id', $booking->id)->find($request->input('transaction_id'));
        } elseif ($booking->transactions->isNotEmpty()) {
            $transaction = $booking->transactions->last();
        }

        // Map template view name
        $viewName = match ($templateView) {
            'noc_certificate'        => 'project.documents.templates.noc_certificate',
            'possession_certificate' => 'project.documents.templates.possession_letter',
            'booking_summary'        => 'project.documents.templates.booking_summary',
            'receipts'               => 'project.documents.templates.receipts_document',
            'payments'               => 'project.documents.templates.payments_document',
            'proforma_invoice'       => 'project.documents.templates.proforma_invoice',
            'booking_closing_summary'=> 'project.documents.templates.booking_closing_summary',
            default                  => "project.documents.templates.{$templateView}",
        };

        return view($viewName, compact(
            'project',
            'booking',
            'company',
            'escrowAccount',
            'documentType',
            'meta',
            'transaction',
            'consideration',
            'addons',
            'dislodges',
            'supplementary',
            'grossBookingValue',
            'discountApplied',
            'adjustments',
            'totalDiscount',
            'netBookingValue',
            'taxableValue',
            'taxRate',
            'taxAmount',
            'grossTaxable',
            'grossCash',
            'finalBookingValue',
            'amountInWords',
            'loanSanctioned',
            'partyTaxableTarget',
            'cashTarget',
            'receiptMode',
            'receiptTransactions',
            'paymentMode',
            'paymentTransactions'
        ));
    }

    /**
     * Render official document as a clean specimen/blank template when no customer booking exists
     */
    protected function renderSpecimenDocument(Request $request, Project $project, string $documentType): View
    {
        if (!array_key_exists($documentType, self::DOCUMENT_TYPES)) {
            abort(404, "Requested document template '{$documentType}' does not exist.");
        }

        $meta = self::DOCUMENT_TYPES[$documentType];
        $templateView = $meta['alias_of'] ?? $documentType;

        $company = $project->company;
        $escrowAccount = BankAccount::where('company_id', $company->id)
            ->where(function ($q) use ($project) {
                $q->where('project_id', $project->id)->orWhereNull('project_id');
            })
            ->first();

        // Construct in-memory Specimen Booking with blank/specimen placeholders
        $booking = new Booking([
            'project_id'              => $project->id,
            'company_id'              => $company->id,
            'booking_code'            => $project->project_code . '/SPECIMEN-001',
            'booking_date'            => now(),
            'block_name'              => 'Block A',
            'floor_no'                => '1st Floor',
            'unit_no'                 => 'Flat-101 (Specimen)',
            'built_up_area'           => 1000,
            'super_built_up_area'     => 1250,
            'property_type'           => 'Residential Flat',
            'parking_type'            => 'Covered Garage',
            'parking_no'              => 'Bay-01',
            'customer_salutation'     => 'Mr./Ms.',
            'customer_name'           => '[Customer Name / Specimen]',
            'guardian_relation'       => 'son_of',
            'guardian_name'           => '[Guardian Name]',
            'mobile_no'               => '+91-XXXXXXXXXX',
            'pan_number'              => 'ABCDE1234F',
            'rate_per_sqft'           => 3500.00,
            'unit_cost'               => 3500000.00,
            'parking_cost'            => 150000.00,
            'transformer_cost'        => 50000.00,
            'amenities_cost'          => 50000.00,
            'discount_applied'        => 0.00,
            'gross_total'             => 3750000.00,
            'consideration_value'     => 3750000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 187500.00,
            'supplementary_value'     => 0.00,
            'total_booking_value'     => 3937500.00,
            'taxable_agreement_value' => 3750000.00,
            'taxable_gst_value'       => 187500.00,
            'gross_taxable_value'     => 3937500.00,
            'gross_cash_value'        => 0.00,
            'final_booking_value'     => 3937500.00,
            'status'                  => 'live',
        ]);

        $booking->setRelation('company', $company);
        $booking->setRelation('project', $project);
        $booking->setRelation('customizations', collect());
        $booking->setRelation('transactions', collect());
        $booking->setRelation('saleAgreement', null);
        $booking->setRelation('bankFinance', null);
        $booking->setRelation('saleDeed', null);
        $booking->setRelation('cancellationRefund', null);

        $consideration = (float)$booking->consideration_value;
        $addons = 0.0;
        $dislodges = 0.0;
        $supplementary = 0.0;
        $grossBookingValue = $consideration;
        $discountApplied = 0.0;
        $adjustments = 0.0;
        $totalDiscount = 0.0;
        $netBookingValue = $consideration;
        $taxableValue = $consideration;
        $taxRate = 5.00;
        $taxAmount = (float)$booking->tax_amount;
        $grossTaxable = (float)$booking->gross_taxable_value;
        $grossCash = 0.0;
        $finalBookingValue = (float)$booking->final_booking_value;
        $amountInWords = AutoNumberService::numberToIndianWords($finalBookingValue);

        $loanSanctioned = 0.0;
        $partyTaxableTarget = $grossTaxable;
        $cashTarget = 0.0;

        $receiptMode = $request->input('mode', 'all_receipts');
        $receiptTransactions = collect();

        $paymentMode = $request->input('mode', 'all_payments');
        $paymentTransactions = collect();

        $transaction = null;
        $isSpecimen = true;

        $viewName = match ($templateView) {
            'noc_certificate'        => 'project.documents.templates.noc_certificate',
            'possession_certificate' => 'project.documents.templates.possession_letter',
            'booking_summary'        => 'project.documents.templates.booking_summary',
            'receipts'               => 'project.documents.templates.receipts_document',
            'payments'               => 'project.documents.templates.payments_document',
            'proforma_invoice'       => 'project.documents.templates.proforma_invoice',
            'booking_closing_summary'=> 'project.documents.templates.booking_closing_summary',
            default                  => "project.documents.templates.{$templateView}",
        };

        return view($viewName, compact(
            'project',
            'booking',
            'company',
            'escrowAccount',
            'documentType',
            'meta',
            'transaction',
            'consideration',
            'addons',
            'dislodges',
            'supplementary',
            'grossBookingValue',
            'discountApplied',
            'adjustments',
            'totalDiscount',
            'netBookingValue',
            'taxableValue',
            'taxRate',
            'taxAmount',
            'grossTaxable',
            'grossCash',
            'finalBookingValue',
            'amountInWords',
            'loanSanctioned',
            'partyTaxableTarget',
            'cashTarget',
            'receiptMode',
            'receiptTransactions',
            'paymentMode',
            'paymentTransactions',
            'isSpecimen'
        ));
    }
}
