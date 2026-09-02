<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Map of available document templates
     */
    public const DOCUMENT_TYPES = [
        'booking_confirmation'       => ['title' => 'Booking Confirmation Letter', 'category' => 'allotment', 'icon' => 'fa-file-circle-check', 'color' => 'sky', 'desc' => 'Initial welcome & booking letter with flat dimensions and payment schedule.'],
        'allotment_letter'           => ['title' => 'Property Allotment Letter', 'category' => 'allotment', 'icon' => 'fa-file-signature', 'color' => 'indigo', 'desc' => 'Official legal allotment certificate specifying Unit No, Floor, and Parking Bay.'],
        'demand_letter'              => ['title' => 'Demand Notice / Milestone Call Letter', 'category' => 'billing', 'icon' => 'fa-envelope-open-text', 'color' => 'amber', 'desc' => 'Payment milestone notice with bank escrow transfer instructions.'],
        'final_bill'                 => ['title' => 'Final Bill & Settlement Statement', 'category' => 'billing', 'icon' => 'fa-file-invoice-dollar', 'color' => 'emerald', 'desc' => 'Comprehensive master invoice of consideration, GST, job sheets, and balance due.'],
        'bank_noc'                   => ['title' => 'Bank Loan No Objection Certificate (NOC)', 'category' => 'legal', 'icon' => 'fa-landmark', 'color' => 'teal', 'desc' => 'NOC addressed to customer home loan lending bank with developer escrow account.'],
        'possession_letter'          => ['title' => 'Possession & Key Handover Certificate', 'category' => 'legal', 'icon' => 'fa-key', 'color' => 'purple', 'desc' => 'Handover protocol certifying completion, meter numbers, and key delivery.'],
        'money_receipt'              => ['title' => 'Money Receipt (Taxable Banking / GST)', 'category' => 'receipts', 'icon' => 'fa-building-columns', 'color' => 'emerald', 'desc' => 'Official tax invoice receipt with GST breakdown and banking UTR details.'],
        'cash_receipt_voucher'       => ['title' => 'Receipt Voucher (Non-Taxable Cash)', 'category' => 'receipts', 'icon' => 'fa-money-bill-wave', 'color' => 'amber', 'desc' => 'Cash ledger voucher for customization job sheets and cash split.'],
        'payment_voucher'            => ['title' => 'Payment & Refund Outflow Voucher', 'category' => 'receipts', 'icon' => 'fa-hand-holding-dollar', 'color' => 'rose', 'desc' => 'Outward disbursement voucher for customer refunds and bank repayments.'],
        'customization_jobsheet'     => ['title' => 'Technical Customization Job Sheet', 'category' => 'technical', 'icon' => 'fa-sliders', 'color' => 'indigo', 'desc' => 'Itemized engineering job sheet for Add-on and Dislodge alterations.'],
        'customer_account_statement' => ['title' => 'Customer 360° Account Ledger Statement', 'category' => 'billing', 'icon' => 'fa-scale-balanced', 'color' => 'blue', 'desc' => 'Complete chronological statement of debits, credits, and live balance.'],
        'cancellation_deed'          => ['title' => 'Booking Cancellation & Settlement Deed', 'category' => 'legal', 'icon' => 'fa-ban', 'color' => 'rose', 'desc' => 'Legal deed recording booking cancellation, penalty fee, and refund payouts.'],
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
                ->with(['company', 'saleAgreement', 'bankFinance', 'customizations', 'transactions'])
                ->find($request->input('booking_id'));
        }

        $documentTypes = self::DOCUMENT_TYPES;

        return view('project.documents.index', compact('project', 'bookings', 'selectedBooking', 'documentTypes'));
    }

    /**
     * Render a specific dynamic print-ready document template
     */
    public function show(Request $request, Project $project, Booking $booking, string $documentType): View
    {
        if (!array_key_exists($documentType, self::DOCUMENT_TYPES)) {
            abort(404, "Requested document template '{$documentType}' does not exist.");
        }

        $booking->load(['company', 'project', 'saleAgreement', 'bankFinance', 'saleDeed', 'customizations', 'transactions.bankAccount', 'cancellationRefund']);
        $company = $booking->company;

        $escrowAccount = BankAccount::where('company_id', $company->id)
            ->where(function ($q) use ($project) {
                $q->where('project_id', $project->id)->orWhereNull('project_id');
            })
            ->first();

        $meta = self::DOCUMENT_TYPES[$documentType];

        // Specific transaction if passed (e.g. for single receipt print)
        $transaction = null;
        if ($request->filled('transaction_id')) {
            $transaction = Transaction::where('booking_id', $booking->id)->find($request->input('transaction_id'));
        } elseif ($booking->transactions->isNotEmpty()) {
            $transaction = $booking->transactions->last();
        }

        return view("project.documents.templates.{$documentType}", compact(
            'project',
            'booking',
            'company',
            'escrowAccount',
            'documentType',
            'meta',
            'transaction'
        ));
    }
}
