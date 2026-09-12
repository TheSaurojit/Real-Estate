@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b-2 border-slate-900 pb-4">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">FILE CLOSING STATEMENT</h2>
                @if($booking->status === 'cancelled')
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">CANCELLED</span>
                @else
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">LIVE / CLOSED</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Booking Closing Summary Audit Record</p>
        </div>
        <div class="text-left sm:text-right text-xs font-mono space-y-1">
            <p class="text-slate-800 font-bold">Booking Code : <span class="text-indigo-700">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600">Report made on : {{ date('d F Y') }}</p>
            <p class="text-slate-600">Unit ID : {{ $booking->unit_no }} ({{ $booking->block_name ?? 'Block-I' }})</p>
        </div>
    </div>

    <!-- Customer & Property Specifications Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <!-- Customer Info -->
        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Customer Information :</span>
            <p class="font-bold text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
            @if($booking->guardian_name)
                <p class="text-slate-600">{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: {{ $booking->guardian_name }}</p>
            @endif
            @if($booking->address)
                <p class="text-slate-600 leading-relaxed">{{ $booking->address }}</p>
            @endif
            <p class="text-slate-700 font-mono">
                Contact: <strong>{{ $booking->mobile_no }}</strong> | Email: {{ $booking->email_id ?? 'N/A' }}
            </p>
            <p class="text-slate-700 font-mono">
                PAN: <strong>{{ $booking->pan_number ?? 'N/A' }}</strong> | GSTIN: <strong>{{ $booking->gstin ?? 'N/A' }}</strong>
            </p>
        </div>

        <!-- Property Info -->
        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Property Information :</span>
            <p class="font-bold text-slate-900">{{ $project->name }}</p>
            <p class="text-slate-600 text-[11px] leading-relaxed">
                Dag: {{ $project->daag_no ?? '-' }} | Patta: {{ $project->patta_no ?? '-' }} | Holding: {{ $project->holding_no ?? '-' }}<br>
                {{ $project->full_address }}
            </p>
            <p class="text-slate-700">
                <strong>Floor & Unit :</strong> {{ $booking->floor_no ?? 'Floor' }} — Unit <span class="font-mono font-bold">{{ $booking->unit_no }}</span>
            </p>
            <p class="text-slate-600 font-mono text-[11px]">
                Built-up: {{ number_format($booking->built_up_area, 2) }} Sq. Ft. | Super Built-up: {{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.*
            </p>
            <p class="text-slate-600 font-mono text-[11px]">
                RERA Reg. no. : <strong class="text-emerald-700">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</strong>
            </p>
        </div>
    </div>

    <!-- Pricing Breakups & Brokerage Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-mono">
        <!-- Base Pricing -->
        <div class="md:col-span-2 border border-slate-300 rounded-xl overflow-hidden bg-white">
            <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[10px] text-slate-700 border-b border-slate-300 font-sans">
                Property Pricing & Breakups
            </div>
            <div class="grid grid-cols-2 p-3 gap-x-4 gap-y-1">
                <div class="flex justify-between"><span class="font-sans text-slate-600">Rate per sq. ft. :</span> <span>₹{{ number_format($booking->rate_per_sqft, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Unit cost :</span> <span>₹{{ number_format($booking->unit_cost, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Parking cost :</span> <span>₹{{ number_format($booking->parking_cost, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Transformer cost :</span> <span>₹{{ number_format($booking->transformer_cost, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Amenities charges :</span> <span>₹{{ number_format($booking->amenities_cost, 2) }}</span></div>
                <div class="flex justify-between font-bold text-slate-900"><span class="font-sans">Gross booking val :</span> <span>₹{{ number_format($booking->gross_total, 2) }}</span></div>
                <div class="flex justify-between text-rose-700"><span class="font-sans">Discount / adj. :</span> <span>-₹{{ number_format($totalDiscount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-amber-900"><span class="font-sans">Consideration val :</span> <span>₹{{ number_format($consideration, 2) }}</span></div>
            </div>
        </div>

        <!-- Brokerage & Parking Details -->
        <div class="border border-slate-300 rounded-xl p-3.5 bg-slate-50 space-y-2 text-xs">
            <span class="font-bold uppercase tracking-wider text-slate-700 text-[10px] block font-sans">Reference & Parking :</span>
            <div>
                <span class="text-slate-500 text-[11px] block font-sans">Parking Allocated :</span>
                <span class="font-bold text-slate-800">{{ $booking->parking_type !== 'none' ? 'YES (' . ucfirst(str_replace('_', ' ', $booking->parking_type)) . ')' : 'NO' }}</span>
                <span class="text-slate-500 text-[11px] block font-sans pt-1">Bay No: {{ $booking->parking_no ?? 'Pending' }}</span>
            </div>
            <div class="border-t border-slate-200 pt-1.5">
                <span class="text-slate-500 text-[11px] block font-sans">Booking Source :</span>
                <span class="font-semibold text-slate-800">{{ $booking->reference_source ?: 'Self / Direct' }}</span>
            </div>
        </div>
    </div>

    <!-- Page Break for Clean Multi-Page Print -->
    <div class="page-break pt-4"></div>

    <!-- Full Booking Financial Audit Grid -->
    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white text-xs font-mono">
        <div class="bg-slate-800 text-white px-4 py-2 font-bold uppercase text-[11px] tracking-wider font-sans flex items-center justify-between">
            <span>Booking Details & Taxable Inflows</span>
            <span class="text-amber-300">File Closing Summary</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
            <div class="p-4 space-y-1.5">
                <div class="flex justify-between"><span class="font-sans text-slate-600">Total Addons (+) :</span> <span class="text-emerald-700">+₹{{ number_format($addons, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Total Dislodges (-) :</span> <span class="text-rose-700">-₹{{ number_format($dislodges, 2) }}</span></div>
                <div class="flex justify-between font-bold text-slate-900"><span class="font-sans">Supplementary cost :</span> <span>₹{{ number_format($supplementary, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Gross Booking value :</span> <span>₹{{ number_format($grossBookingValue, 2) }}</span></div>
                <div class="flex justify-between text-rose-700"><span class="font-sans">Discount / adj. :</span> <span>-₹{{ number_format($totalDiscount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-slate-900"><span class="font-sans">Net Booking value :</span> <span>₹{{ number_format($netBookingValue, 2) }}</span></div>
            </div>

            <div class="p-4 space-y-1.5">
                <div class="flex justify-between"><span class="font-sans text-slate-600">Taxable value :</span> <span class="text-sky-800">₹{{ number_format($taxableValue, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Tax Name & Duty :</span> <span class="font-sans text-slate-700">{{ $booking->tax_name ?: 'GST' }} ({{ number_format($taxRate, 2) }}%)</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Collected Tax value :</span> <span class="text-sky-800">+₹{{ number_format($taxAmount, 2) }}</span></div>
                <div class="flex justify-between font-black text-indigo-950 bg-indigo-50/70 p-1 rounded"><span class="font-sans">Final Booking value :</span> <span class="text-sm">₹{{ number_format($finalBookingValue, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Financed value (Loan) :</span> <span class="text-purple-900">₹{{ number_format($loanSanctioned, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Taxable payable (Party) :</span> <span class="text-sky-900">₹{{ number_format($partyTaxableTarget, 2) }}</span></div>
                <div class="flex justify-between"><span class="font-sans text-slate-600">Cash payable (Party) :</span> <span class="text-amber-900">₹{{ number_format($grossCash, 2) }}</span></div>
            </div>
        </div>
    </div>

    <!-- Receipt Summary Reconciliation Table -->
    @php
        $loanTarget = $loanSanctioned;
        $loanRecd = (float)$booking->loan_received;
        $loanDue = max(0, round($loanTarget - $loanRecd, 2));

        $partyTaxTarget = $partyTaxableTarget;
        $partyTaxRecd = (float)$booking->taxable_self_received;
        $partyTaxDue = max(0, round($partyTaxTarget - $partyTaxRecd, 2));

        $cashTargetVal = $grossCash;
        $cashRecd = (float)$booking->total_cash_received;
        $cashDue = max(0, round($cashTargetVal - $cashRecd, 2));

        $grossTarget = $loanTarget + $partyTaxTarget + $cashTargetVal;
        $grossRecd = $loanRecd + $partyTaxRecd + $cashRecd;
        $grossDue = $loanDue + $partyTaxDue + $cashDue;
    @endphp

    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-900 text-white px-4 py-2 font-bold uppercase text-[11px] tracking-wider">
            Receipt Summary Matrix
        </div>
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-4">Stream</th>
                    <th class="py-2.5 px-3 text-right">Loan A/C (₹)</th>
                    <th class="py-2.5 px-3 text-right">Taxable (Party A/C) (₹)</th>
                    <th class="py-2.5 px-3 text-right">By Cash/Non-Taxable (₹)</th>
                    <th class="py-2.5 px-4 text-right font-black text-slate-900">Gross Total (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-slate-800">Final Target :</td>
                    <td class="py-2.5 px-3 text-right text-purple-900">₹{{ number_format($loanTarget, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-sky-900">₹{{ number_format($partyTaxTarget, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-amber-900">₹{{ number_format($cashTargetVal, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">₹{{ number_format($grossTarget, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-emerald-800">Net Received :</td>
                    <td class="py-2.5 px-3 text-right text-emerald-700">₹{{ number_format($loanRecd, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-emerald-700">₹{{ number_format($partyTaxRecd, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-emerald-700">₹{{ number_format($cashRecd, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-emerald-800">₹{{ number_format($grossRecd, 2) }}</td>
                </tr>
                <tr class="bg-amber-50/70 font-bold border-t-2 border-slate-300">
                    <td class="py-3 px-4 font-sans text-amber-950">Balance Due :</td>
                    <td class="py-3 px-3 text-right text-purple-950">₹{{ number_format($loanDue, 2) }}</td>
                    <td class="py-3 px-3 text-right text-sky-950">₹{{ number_format($partyTaxDue, 2) }}</td>
                    <td class="py-3 px-3 text-right text-amber-950">₹{{ number_format($cashDue, 2) }}</td>
                    <td class="py-3 px-4 text-right text-rose-700 text-sm font-black">₹{{ number_format($grossDue, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Remarks & Notes -->
    @if($booking->particulars)
        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 text-xs text-slate-700">
            <strong>Remarks & Notes :</strong> {{ $booking->particulars }}
        </div>
    @endif

    <!-- Triple Approval Signatures Block -->
    <div class="pt-8 grid grid-cols-3 gap-4 text-xs text-slate-600 text-center">
        <div class="space-y-1">
            <div class="w-36 border-b border-slate-400 pb-1 mx-auto"></div>
            <p class="font-bold uppercase tracking-wider text-[11px] text-slate-900">Prepared by</p>
            <p class="text-[10px] text-slate-400">Accounts Executive</p>
        </div>
        <div class="space-y-1">
            <div class="w-36 border-b border-slate-400 pb-1 mx-auto"></div>
            <p class="font-bold uppercase tracking-wider text-[11px] text-slate-900">Checked by</p>
            <p class="text-[10px] text-slate-400">Finance Manager</p>
        </div>
        <div class="space-y-1">
            <div class="w-36 border-b border-slate-400 pb-1 mx-auto"></div>
            <p class="font-bold uppercase tracking-wider text-[11px] text-slate-900">Approved by</p>
            <p class="text-[10px] text-slate-400">Managing Director / Partner</p>
        </div>
    </div>

</div>
@endsection
