@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Recipient & Reference Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-slate-200 pb-4 text-xs">
        <div class="space-y-1">
            <p class="font-bold text-slate-400 uppercase text-[10px]">To,</p>
            <p class="font-bold text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
            @if($booking->guardian_name)
                <p class="text-slate-600">{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: {{ $booking->guardian_name }}</p>
            @endif
            @if($booking->address)
                <p class="text-slate-600 max-w-sm">{{ $booking->address }}</p>
            @endif
        </div>
        <div class="text-left sm:text-right space-y-1">
            <p class="text-slate-600"><strong>Booking id :</strong> <span class="font-mono font-bold text-slate-900">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Date :</strong> {{ date('d F Y') }}</p>
        </div>
    </div>

    <!-- Subject & Greetings -->
    <div class="space-y-2">
        <div class="inline-block px-3 py-1 bg-amber-50 text-amber-900 font-bold uppercase text-xs tracking-wider rounded-md border border-amber-200">
            Subject : Payment of Installment / Disbursement Request
        </div>
        <p class="text-xs text-slate-600 font-bold">Greetings of the day! Dear sir/ma'am,</p>
    </div>

    <!-- Demand Call Notice -->
    <div class="bg-amber-50/50 border border-amber-200 rounded-xl p-4 text-xs text-slate-800 space-y-2 leading-relaxed">
        <p>
            With due respect, in reference to the below mentioned property, we are delighted to inform you that the construction of your unit is progressing/completed as per the scheduled specification and the registered deed of conveyance shall be executed shortly.
        </p>
        <p class="font-semibold text-slate-900">
            You are requested to release the balance installment / due amount of 
            <strong class="font-mono text-rose-700 font-bold text-sm">₹{{ number_format($booking->total_outstanding_due, 2) }}</strong>
            (<span class="italic font-sans text-slate-700">{{ \App\Services\AutoNumberService::numberToIndianWords($booking->total_outstanding_due) }}</span>) 
            as per the communicated payment schedule mentioned in the Sale Agreement.
        </p>
    </div>

    <!-- Property Information Card -->
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[11px] text-slate-700 border-b border-slate-200 flex items-center space-x-1.5">
            <i class="fa-solid fa-hotel text-amber-600 text-xs"></i>
            <span>Property Information</span>
        </div>
        <table class="w-full text-xs">
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold w-1/3">Project / Address :</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900">
                        {{ $project->name }}<br>
                        <span class="text-[11px] font-normal text-slate-600">
                            Dag No.: {{ $project->daag_no ?? '-' }} | Patta No.: {{ $project->patta_no ?? '-' }} [2nd RS]<br>
                            Holding No.: {{ $project->holding_no ?? '-' }}, {{ $project->full_address }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Unit Number :</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900 font-mono text-sm">
                        {{ $booking->unit_no }} (Block: {{ $booking->block_name ?? 'Block-I' }}, Floor: {{ $booking->floor_no ?? 'Ground Floor' }})
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">*Built-up & Super Built-up Area :</td>
                    <td class="py-2.5 px-4 font-mono text-slate-800">
                        Built-up: {{ number_format($booking->built_up_area, 2) }} Sq. Ft. | Super Built-up: {{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.*
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">RERA Reg. no. :</td>
                    <td class="py-2.5 px-4 font-mono text-emerald-800 font-bold">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Reg. Sale agreement details :</td>
                    <td class="py-2.5 px-4 text-slate-800">
                        {{ $booking->saleAgreement ? 'Executed on ' . ($booking->saleAgreement->agreement_date ? $booking->saleAgreement->agreement_date->format('d-m-Y') : 'Yes') . ' (Value: ₹' . number_format($booking->saleAgreement->agreement_value, 2) . ')' : 'Pending' }}
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Sanctioned Finance/Loan a/c :</td>
                    <td class="py-2.5 px-4 text-slate-800">
                        {{ $booking->bankFinance ? ('₹' . number_format($booking->bankFinance->sanctioned_amount, 2) . ' Sanctioned by ' . $booking->bankFinance->bank_name . ($booking->bankFinance->loan_account_no ? ' - A/C ' . $booking->bankFinance->loan_account_no : '')) : 'Self-Financed / Direct (No Loan A/C)' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Page Break for Clean 2-Page Print -->
    <div class="page-break pt-4"></div>

    <!-- Page 2: Financial Summary Table (Party Self vs Loan A/C) -->
    @php
        $loanTotal = (float)($booking->bankFinance?->sanctioned_amount ?? 0);
        $loanRecd = (float)$booking->loan_received;
        $loanDue = max(0, round($loanTotal - $loanRecd, 2));

        $partyTotal = max(0, round($consideration - $loanTotal, 2));
        $partyRecd = (float)$booking->taxable_self_received + (float)$booking->total_cash_received;
        $partyDue = max(0, round($partyTotal - $partyRecd, 2));

        $totalRecd = $loanRecd + $partyRecd;
        $totalDue = $loanDue + $partyDue;
    @endphp

    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <div class="bg-slate-800 text-white px-4 py-2.5 font-bold uppercase text-xs tracking-wider flex items-center justify-between">
            <span>Financial Summary :</span>
            <span class="text-[11px] text-slate-300 font-normal">#Consideration Amount Details</span>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-4 text-left">Particulars</th>
                    <th class="py-2.5 px-4 text-right">Total Amount (₹)</th>
                    <th class="py-2.5 px-4 text-right">Received Amount (₹)</th>
                    <th class="py-2.5 px-4 text-right">Balance Due (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-slate-800">Party (Self arrangement)</td>
                    <td class="py-2.5 px-4 text-right text-slate-800">₹{{ number_format($partyTotal, 2) }}</td>
                    <td class="py-2.5 px-4 text-right text-emerald-700">₹{{ number_format($partyRecd, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-rose-700">₹{{ number_format($partyDue, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans font-semibold text-purple-900">Loan a/c (Bank Finance)</td>
                    <td class="py-2.5 px-4 text-right text-slate-800">₹{{ number_format($loanTotal, 2) }}</td>
                    <td class="py-2.5 px-4 text-right text-emerald-700">₹{{ number_format($loanRecd, 2) }}</td>
                    <td class="py-2.5 px-4 text-right font-bold text-rose-700">₹{{ number_format($loanDue, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold border-t-2 border-slate-300">
                    <td class="py-3 px-4 font-sans text-slate-900">Total Consideration Value</td>
                    <td class="py-3 px-4 text-right text-slate-900">₹{{ number_format($consideration, 2) }}</td>
                    <td class="py-3 px-4 text-right text-emerald-800">₹{{ number_format($totalRecd, 2) }}</td>
                    <td class="py-3 px-4 text-right text-rose-800 text-sm">₹{{ number_format($totalDue, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="bg-slate-50 px-4 py-2 border-t border-slate-200 text-[10px] text-slate-500 flex items-center justify-between">
            <span>#GST value and technical customizations are computed separately.</span>
            <span>*More or Less / Approximately</span>
        </div>
    </div>

    <!-- Builder Bank Account Details for Cheque / NEFT / RTGS -->
    <div class="border border-slate-300 rounded-xl p-4 text-xs bg-white space-y-2">
        <p class="font-bold text-slate-900">
            Please note that the cheque / PO / NEFT / RTGS should be prepared / issued as per the bank account details mentioned below:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50 border border-slate-200 rounded-lg p-3 font-mono">
            <div>
                <span class="text-[10px] text-slate-500 font-sans block">Account Name:</span>
                <span class="font-bold text-slate-900">{{ $escrowAccount?->account_name ?? $company->name }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-sans block">Account Number:</span>
                <span class="font-bold text-slate-900">{{ $escrowAccount?->account_number ?? 'Contact Builder Office' }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-sans block">Bank & Branch:</span>
                <span class="text-slate-800">{{ $escrowAccount?->bank_name ?? 'Indian Bank' }} ({{ $escrowAccount?->branch ?? 'Main Branch' }})</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-sans block">IFSC Code:</span>
                <span class="font-bold text-indigo-700">{{ $escrowAccount?->ifsc_code ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Closing Remarks -->
    <div class="pt-2 text-xs text-slate-600 space-y-1">
        <p class="font-bold text-slate-800">Thank you for business with us !</p>
        <p>Assuring you of the best of our services at all times.</p>
        <p>For any clarification feel free to contact our team anytime!</p>
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
