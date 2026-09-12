@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header & Client/Property Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b border-slate-200 pb-4 text-xs">
        <div class="space-y-1">
            <p class="font-bold text-slate-400 uppercase text-[10px]">Party Name & Address :</p>
            <p class="font-bold text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
            @if($booking->guardian_name)
                <p class="text-slate-600">{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: {{ $booking->guardian_name }}</p>
            @endif
            @if($booking->address)
                <p class="text-slate-600">{{ $booking->address }}</p>
            @endif
            <p class="text-slate-600">Contact no. : <span class="font-mono">{{ $booking->mobile_no }}</span></p>
            <p class="text-slate-600">Email id : {{ $booking->email_id ?? 'Not available' }}</p>
            <p class="text-slate-600 font-mono">Party PAN : <strong>{{ $booking->pan_number ?? 'Not available' }}</strong> | GSTIN : <strong>{{ $booking->gstin ?? 'Not available' }}</strong></p>
        </div>

        <div class="space-y-1 text-left md:text-right">
            <p class="font-bold text-slate-400 uppercase text-[10px]">Property & Booking Details :</p>
            <p class="font-bold text-slate-900">{{ $project->name }}</p>
            <p class="text-slate-600 text-[11px]">{{ $project->full_address }}</p>
            <p class="text-slate-600"><strong>Booking ID :</strong> <span class="font-mono font-bold text-indigo-700">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Booking Date :</strong> {{ $booking->booking_date ? $booking->booking_date->format('d F Y') : date('d F Y') }}</p>
            <p class="text-slate-600"><strong>Unit :</strong> <span class="font-bold font-mono">{{ $booking->unit_no }}</span> ({{ $booking->block_name ?? 'Block-I' }} / {{ $booking->floor_no ?? '1st Floor' }})</p>
            <p class="text-slate-600 font-mono text-[11px]">Super Built-up: {{ number_format($booking->super_built_up_area, 2) }} Sq. Ft. (Built-up: {{ number_format($booking->built_up_area, 2) }})</p>
            <p class="text-slate-600 font-mono text-[11px]">RERA Reg. no. : <strong class="text-emerald-700">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</strong></p>
        </div>
    </div>

    <!-- PART 1: Booking Customization (Annexure-I) -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center space-x-1.5">
                <i class="fa-solid fa-toolbox text-violet-600"></i>
                <span>Booking Customization (Annexure-I)</span>
            </h3>
            <span class="text-[11px] text-slate-500 font-medium">{{ $booking->customizations->count() }} itemized job sheets</span>
        </div>

        <div class="border border-slate-300 rounded-xl overflow-hidden">
            <table class="w-full text-[11px] text-left">
                <thead class="bg-slate-100 font-bold uppercase text-slate-600 border-b border-slate-300 text-[10px]">
                    <tr>
                        <th class="py-2.5 px-2 text-center">Sl.</th>
                        <th class="py-2.5 px-2 text-center">Ops</th>
                        <th class="py-2.5 px-3">Particulars / Trade</th>
                        <th class="py-2.5 px-3">Job Description</th>
                        <th class="py-2.5 px-2 text-right">Mat. Rate</th>
                        <th class="py-2.5 px-2 text-right">Lab. Rate</th>
                        <th class="py-2.5 px-2 text-right">Total Rate</th>
                        <th class="py-2.5 px-2 text-center">Quantity</th>
                        <th class="py-2.5 px-3 text-right">Total Cost (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($booking->customizations as $idx => $c)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2 px-2 text-center text-slate-500 font-sans">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2 text-center">
                                @if($c->job_type === 'addon')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">ADD</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800">DIS</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 font-sans font-semibold text-slate-800">{{ $c->particular }}</td>
                            <td class="py-2 px-3 font-sans text-slate-600">{{ $c->description }}</td>
                            <td class="py-2 px-2 text-right text-slate-700">₹{{ number_format($c->material_rate, 2) }}</td>
                            <td class="py-2 px-2 text-right text-slate-700">₹{{ number_format($c->labour_rate, 2) }}</td>
                            <td class="py-2 px-2 text-right font-semibold text-slate-800">₹{{ number_format($c->material_rate + $c->labour_rate, 2) }}</td>
                            <td class="py-2 px-2 text-center font-bold text-slate-800 font-sans">{{ number_format($c->quantity, 2) }} {{ $c->unit_measure ?? 'Units' }}</td>
                            <td class="py-2 px-3 text-right font-bold {{ $c->job_type === 'addon' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $c->job_type === 'addon' ? '+' : '-' }}₹{{ number_format($c->job_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-400 font-sans">No customization job sheets recorded. Standard specifications apply.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-300 font-mono text-xs text-slate-800">
                    <tr>
                        <td colspan="4" class="py-2 px-4 font-sans text-slate-700">Customizations Rollup Summary:</td>
                        <td colspan="2" class="py-2 px-2 text-right font-sans text-emerald-700">Addons: +₹{{ number_format($addons, 2) }}</td>
                        <td colspan="2" class="py-2 px-2 text-right font-sans text-rose-700">Dislodges: -₹{{ number_format($dislodges, 2) }}</td>
                        <td class="py-2 px-3 text-right text-indigo-900 font-black">Net: ₹{{ number_format($supplementary, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Page Break for Clean Multi-Page Print -->
    <div class="page-break pt-4"></div>

    <!-- PART 2: Master Final Bill Statement -->
    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-900 text-white px-4 py-2.5 font-bold uppercase text-xs tracking-wider flex items-center justify-between">
            <span>Final Bill Statement</span>
            <span class="font-mono text-amber-300">Net Contract Value</span>
        </div>
        <table class="w-full text-xs font-mono">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Consideration value :</td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">₹{{ number_format($consideration, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Total Addons (+) :</td>
                    <td class="py-2 px-4 text-right text-emerald-700 font-semibold">+ ₹{{ number_format($addons, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Total Dislodges (-) :</td>
                    <td class="py-2 px-4 text-right text-rose-700 font-semibold">- ₹{{ number_format($dislodges, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold">
                    <td class="py-2 px-4 font-sans text-slate-700">Supplementary cost (Net Job Sheets) :</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format($supplementary, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Gross Booking value :</td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">₹{{ number_format($grossBookingValue, 2) }}</td>
                </tr>
                @if($totalDiscount > 0)
                    <tr class="text-rose-700">
                        <td class="py-2 px-4 font-sans font-semibold">Discounts & Adjustments :</td>
                        <td class="py-2 px-4 text-right font-bold">- ₹{{ number_format($totalDiscount, 2) }}</td>
                    </tr>
                @endif
                <tr class="bg-slate-50 font-bold">
                    <td class="py-2 px-4 font-sans text-slate-700">Net Booking value :</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format($netBookingValue, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Taxable value :</td>
                    <td class="py-2 px-4 text-right text-sky-800">₹{{ number_format($taxableValue, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Tax Name & Duty ({{ $booking->tax_name ?: 'GST' }} - {{ number_format($taxRate, 2) }}%) :</td>
                    <td class="py-2 px-4 text-right text-sky-800 font-semibold">+ ₹{{ number_format($taxAmount, 2) }}</td>
                </tr>
                <tr class="bg-indigo-50/70 font-bold border-t-2 border-slate-300">
                    <td class="py-3 px-4 font-sans text-indigo-950 text-sm">Final Booking value :</td>
                    <td class="py-3 px-4 text-right text-indigo-950 text-base font-black">₹{{ number_format($finalBookingValue, 2) }}</td>
                </tr>
                <tr class="bg-slate-100 font-sans">
                    <td class="py-2.5 px-4 font-bold text-slate-700">Total amount in words :</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900 text-xs italic">{{ $amountInWords }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Milestone Context Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs bg-slate-50 border border-slate-200 rounded-xl p-3.5">
        <div>
            <span class="font-bold text-slate-700 block mb-0.5">Sale Agreement Details :</span>
            <span class="text-slate-600">
                Current Sale agreement status: 
                <strong>{{ $booking->saleAgreement ? 'Executed on ' . ($booking->saleAgreement->agreement_date ? $booking->saleAgreement->agreement_date->format('d-m-Y') : 'Yes') : 'Pending' }}</strong>
            </span>
        </div>
        <div>
            <span class="font-bold text-slate-700 block mb-0.5">Finance / Loan Details :</span>
            <span class="text-slate-600">
                {{ $booking->bankFinance ? ('₹' . number_format($booking->bankFinance->sanctioned_amount, 2) . ' Sanctioned by ' . $booking->bankFinance->bank_name . ($booking->bankFinance->loan_account_no ? ' - ref ' . $booking->bankFinance->loan_account_no : '')) : 'Direct / Self-Arranged' }}
            </span>
        </div>
    </div>

    <!-- PART 3: Financial Summary Matrix Table -->
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
        <div class="bg-slate-800 text-white px-4 py-2 font-bold uppercase text-[11px] tracking-wider">
            Financial Summary Matrix (Taxable vs Cash Streams)
        </div>
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-4">Particulars</th>
                    <th class="py-2.5 px-3 text-right">Loan A/C (₹)</th>
                    <th class="py-2.5 px-3 text-right">Party (Taxable) (₹)</th>
                    <th class="py-2.5 px-3 text-right">Party (Cash) (₹)</th>
                    <th class="py-2.5 px-4 text-right font-black text-slate-900">Gross Total (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-slate-800">Total Amount :</td>
                    <td class="py-2.5 px-3 text-right text-purple-900">₹{{ number_format($loanTarget, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-sky-900">₹{{ number_format($partyTaxTarget, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-amber-900">₹{{ number_format($cashTargetVal, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">₹{{ number_format($grossTarget, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-emerald-800">Received Amount :</td>
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

    <!-- Terms & Conditions -->
    <div class="border border-slate-200 rounded-xl p-4 text-xs text-slate-600 space-y-1">
        <p class="font-bold uppercase tracking-wide text-slate-800 text-[11px] mb-1">Terms & Conditions :</p>
        <ol class="list-decimal list-inside space-y-0.5">
            <li>All payments are subjected to realization.</li>
            <li>E-copy of instrument, digital acknowledgment.</li>
            <li>All disputes are subjected to local jurisdiction only.</li>
        </ol>
        <p class="pt-1 font-bold text-slate-800">Thank you for business with us !</p>
    </div>

    <!-- Signatures Block -->
    <div class="pt-6 flex items-end justify-between text-xs text-slate-700">
        <div class="space-y-1">
            <div class="w-48 border-b border-slate-400 pb-1"></div>
            <p class="font-bold uppercase tracking-wider text-[11px] text-slate-900">Acknowledged by me,</p>
            <p class="text-slate-600">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
        </div>
        <div class="text-right space-y-1">
            <div class="w-48 border-b border-slate-400 pb-1 ml-auto"></div>
            <p class="font-bold uppercase tracking-wider text-[11px] text-slate-900">For, {{ $company->name }}</p>
            <p class="text-slate-400 text-[10px]">Authorized Signatory</p>
        </div>
    </div>

</div>
@endsection
