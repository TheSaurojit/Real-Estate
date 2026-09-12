@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header Summary Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Booking Summary</h2>
                @if($booking->status === 'cancelled')
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">CANCELLED</span>
                @else
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">LIVE</span>
                @endif
            </div>
            <p class="text-xs font-semibold text-slate-700 mt-1">
                {{ $booking->customer_salutation }} {{ $booking->customer_name }} (<span class="font-mono text-indigo-700">{{ $booking->booking_code }}</span>)
            </p>
            <p class="text-[11px] text-slate-500">
                Unit {{ $booking->unit_no }}, {{ $booking->floor_no ?? 'Ground Floor' }}, {{ $booking->block_name ?? 'Block' }} — {{ $booking->property_type }}
            </p>
        </div>
        <div class="text-left sm:text-right text-xs space-y-1">
            <p class="text-slate-600"><strong>Booking date :</strong> {{ $booking->booking_date ? $booking->booking_date->format('d F Y') : date('d F Y') }}</p>
            <p class="text-slate-600"><strong>Report made on :</strong> {{ date('d F Y') }}</p>
        </div>
    </div>

    <!-- Milestones Status Table -->
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[11px] text-slate-700 border-b border-slate-200">
            Stage & Milestone Status
        </div>
        <table class="w-full text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 text-[10px] uppercase">
                <tr>
                    <th class="py-2 px-3 text-left">Particular</th>
                    <th class="py-2 px-3 text-left">Description</th>
                    <th class="py-2 px-3 text-right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <tr>
                    <td class="py-2.5 px-3 font-semibold text-slate-800">Sale Agreement</td>
                    <td class="py-2.5 px-3 text-slate-600">
                        {{ $booking->saleAgreement ? 'Executed on ' . ($booking->saleAgreement->agreement_date ? $booking->saleAgreement->agreement_date->format('d-m-Y') : 'Yes') : 'Pending / Not Executed' }}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-800">
                        ₹{{ number_format($booking->saleAgreement?->agreement_value ?? $consideration, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-3 font-semibold text-slate-800">Bank Finance</td>
                    <td class="py-2.5 px-3 text-slate-600">
                        {{ $booking->bankFinance ? ($booking->bankFinance->bank_name . ($booking->bankFinance->loan_account_no ? ' - A/C ' . $booking->bankFinance->loan_account_no : '')) : 'Direct / Self-Arranged' }}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-purple-900">
                        ₹{{ number_format($loanSanctioned, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-3 font-semibold text-slate-800">Sale Deed</td>
                    <td class="py-2.5 px-3 text-slate-600">
                        {{ $booking->saleDeed ? 'Executed on ' . ($booking->saleDeed->deed_date ? $booking->saleDeed->deed_date->format('d-m-Y') : 'Yes') : 'Pending / Not Executed' }}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-800">
                        ₹{{ number_format($booking->saleDeed?->sale_deed_value ?? 0, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Booking Value Breakups Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-800 text-white px-4 py-2 font-bold uppercase text-[11px] tracking-wider">
            Booking Value Breakups
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 text-xs font-mono divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
            <!-- Col 1 -->
            <table class="w-full">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Consideration value :</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-900">₹{{ number_format($consideration, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Add-on value :</td>
                        <td class="py-2 px-3 text-right text-emerald-700">+ ₹{{ number_format($addons, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Dislodged value :</td>
                        <td class="py-2 px-3 text-right text-rose-700">- ₹{{ number_format($dislodges, 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50 font-bold">
                        <td class="py-2 px-3 font-sans text-slate-700">Supplementary cost :</td>
                        <td class="py-2 px-3 text-right text-slate-900">₹{{ number_format($supplementary, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Total Booking value :</td>
                        <td class="py-2 px-3 text-right text-slate-900">₹{{ number_format($grossBookingValue, 2) }}</td>
                    </tr>
                    <tr class="text-rose-700">
                        <td class="py-2 px-3 font-sans">Discount / adjustment :</td>
                        <td class="py-2 px-3 text-right font-bold">- ₹{{ number_format($totalDiscount, 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50 font-bold">
                        <td class="py-2 px-3 font-sans text-slate-700">Net Booking value :</td>
                        <td class="py-2 px-3 text-right text-slate-900">₹{{ number_format($netBookingValue, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Col 2 -->
            <table class="w-full">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Taxable value :</td>
                        <td class="py-2 px-3 text-right text-sky-800">₹{{ number_format($taxableValue, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Tax name & duty :</td>
                        <td class="py-2 px-3 text-right font-sans text-slate-700">{{ $booking->tax_name ?: 'GST' }} ({{ number_format($taxRate, 2) }}%)</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Tax value :</td>
                        <td class="py-2 px-3 text-right text-sky-800">+ ₹{{ number_format($taxAmount, 2) }}</td>
                    </tr>
                    <tr class="bg-indigo-50 font-bold">
                        <td class="py-2 px-3 font-sans text-indigo-950">Final Booking value :</td>
                        <td class="py-2 px-3 text-right text-indigo-950 text-sm font-black">₹{{ number_format($finalBookingValue, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Total Taxable value :</td>
                        <td class="py-2 px-3 text-right text-sky-800">₹{{ number_format($grossTaxable, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-sans text-slate-600">Total Cash value :</td>
                        <td class="py-2 px-3 text-right text-amber-800">₹{{ number_format($grossCash, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4-Stream Financial Reconciliation Matrix Table -->
    @php
        $loanTarget = $loanSanctioned;
        $loanRecd = (float)$booking->loan_received;
        $loanAdj = (float)$booking->loan_refunded;
        $netLoan = $loanRecd - $loanAdj;
        $dueLoan = max(0, round($loanTarget - $netLoan, 2));

        $partyTaxTarget = $partyTaxableTarget;
        $partyTaxRecd = (float)$booking->taxable_self_received;
        $partyTaxAdj = (float)$booking->taxable_self_refunded;
        $netPartyTax = $partyTaxRecd - $partyTaxAdj;
        $duePartyTax = max(0, round($partyTaxTarget - $netPartyTax, 2));

        $cashTargetVal = $grossCash;
        $cashRecd = (float)$booking->total_cash_received;
        $cashAdj = (float)$booking->cash_refunded;
        $netCash = $cashRecd - $cashAdj;
        $dueCash = max(0, round($cashTargetVal - $netCash, 2));

        $totTarget = $loanTarget + $partyTaxTarget + $cashTargetVal;
        $totRecd = $loanRecd + $partyTaxRecd + $cashRecd;
        $totAdj = $loanAdj + $partyTaxAdj + $cashAdj;
        $totNet = $netLoan + $netPartyTax + $netCash;
        $totDue = $dueLoan + $duePartyTax + $dueCash;
    @endphp

    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-900 text-white px-4 py-2 font-bold uppercase text-[11px] tracking-wider">
            Multi-Stream Financial Reconciliation Table
        </div>
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-4">Particular</th>
                    <th class="py-2.5 px-3 text-right">Taxable (Loan A/C)</th>
                    <th class="py-2.5 px-3 text-right">Taxable (Party A/C)</th>
                    <th class="py-2.5 px-3 text-right">Non-Taxable A/C</th>
                    <th class="py-2.5 px-4 text-right font-black text-slate-900">Gross Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-slate-800">Booking Breakup :</td>
                    <td class="py-2.5 px-3 text-right text-purple-900">₹{{ number_format($loanTarget, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-sky-900">₹{{ number_format($partyTaxTarget, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-amber-900">₹{{ number_format($cashTargetVal, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">₹{{ number_format($totTarget, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-emerald-800">Gross Received :</td>
                    <td class="py-2.5 px-3 text-right text-emerald-700">₹{{ number_format($loanRecd, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-emerald-700">₹{{ number_format($partyTaxRecd, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-emerald-700">₹{{ number_format($cashRecd, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-emerald-800">₹{{ number_format($totRecd, 2) }}</td>
                </tr>
                <tr class="text-rose-700">
                    <td class="py-2.5 px-4 font-sans font-semibold">Adjustments / Refunds :</td>
                    <td class="py-2.5 px-3 text-right">-₹{{ number_format($loanAdj, 2) }}</td>
                    <td class="py-2.5 px-3 text-right">-₹{{ number_format($partyTaxAdj, 2) }}</td>
                    <td class="py-2.5 px-3 text-right">-₹{{ number_format($cashAdj, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold">-₹{{ number_format($totAdj, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold">
                    <td class="py-2.5 px-4 font-sans text-slate-700">Net Received :</td>
                    <td class="py-2.5 px-3 text-right text-purple-900">₹{{ number_format($netLoan, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-sky-900">₹{{ number_format($netPartyTax, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-amber-900">₹{{ number_format($netCash, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">₹{{ number_format($totNet, 2) }}</td>
                </tr>
                <tr class="bg-amber-50/70 font-bold border-t-2 border-slate-300">
                    <td class="py-3 px-4 font-sans text-amber-950">Balance Due :</td>
                    <td class="py-3 px-3 text-right text-purple-950">₹{{ number_format($dueLoan, 2) }}</td>
                    <td class="py-3 px-3 text-right text-sky-950">₹{{ number_format($duePartyTax, 2) }}</td>
                    <td class="py-3 px-3 text-right text-amber-950">₹{{ number_format($dueCash, 2) }}</td>
                    <td class="py-3 px-4 text-right text-rose-700 text-sm font-black">₹{{ number_format($totDue, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mandatory Settlement Note -->
    <div class="bg-rose-50/60 border border-rose-200 rounded-xl p-3.5 text-xs text-rose-950 leading-relaxed">
        <strong>Note :</strong> Kindly pay the balance due amount and obtain No Objection Certificate (NOC) from the builder before execution of the sale deed or the physical handover date (whichever is earlier). This is not negotiable in any circumstances.
    </div>

    <!-- Signatures -->
    <div class="pt-4 flex items-end justify-between text-xs text-slate-600">
        <div>
            <p><strong>Place :</strong> {{ $project->mouza ?? 'Silchar' }}</p>
            <p><strong>Date :</strong> {{ date('d F Y') }}</p>
        </div>
        <div class="text-right space-y-1">
            <p class="font-bold text-slate-900">For, {{ $company->name }}</p>
            <div class="h-10"></div>
            <p class="text-[11px] text-slate-400">Authorized Signatory</p>
        </div>
    </div>

</div>
@endsection
