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
            <p class="text-slate-600"><strong>Ref. no. :</strong> <span class="font-mono">{{ $booking->booking_code }}/POS</span></p>
            <p class="text-slate-600"><strong>Booking ID :</strong> <span class="font-mono font-bold">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Handover Date :</strong> {{ date('d F Y') }}</p>
        </div>
    </div>

    <!-- Subject & Greetings -->
    <div class="space-y-2">
        <div class="inline-block px-3 py-1 bg-purple-50 text-purple-900 font-bold uppercase text-xs tracking-wider rounded-md border border-purple-200">
            Subject : Possession cum Physical Handover Certificate
        </div>
        <p class="font-bold text-purple-900 text-base">Congratulations..!</p>
        <p class="text-xs text-slate-600 font-bold">Greetings of the day! Dear sir/ma'am,</p>
    </div>

    <!-- Handover Declaration & Inspection Satisfaction -->
    <div class="text-xs text-slate-700 space-y-3 leading-relaxed text-justify bg-slate-50 border border-slate-200 rounded-xl p-4">
        <p>
            It gives us immense pleasure to present this <strong>Handover cum Possession Certificate</strong> and hand over the keys of your property — 
            <strong>Unit No. {{ $booking->unit_no }}</strong> in <strong>"{{ $project->name }}"</strong> at <strong>{{ $booking->floor_no ?? 'Ground Floor' }}</strong> 
            of the building Block <strong>{{ $booking->block_name ?? 'Block-I' }}</strong> on <strong>{{ date('d F Y') }}</strong>. 
            Sale Deed status: <strong>{{ $booking->saleDeed ? 'Executed on ' . ($booking->saleDeed->deed_date ? $booking->saleDeed->deed_date->format('d-m-Y') : 'Yes') : 'Pending / Under Execution' }}</strong> 
            as on this physical handover date.
        </p>

        <p>
            We are happy to note that you have accepted peaceful and vacant possession of property bearing unit no. <strong>{{ $booking->unit_no }}</strong> in <strong>{{ $project->name }}</strong> on the date of your signature on this acceptance. In accordance with the provisions of verbal communication and the information mentioned in the booking form, after undertaking a thorough and complete inspection of the unit and other amenities / facilities provided and after being satisfied that the construction is in accordance with the terms and conditions of the builder-buyer agreement, with respect to the area measurement, workmanship of construction, standard of materials used, amenities, fixtures, fittings and finishing thereof and that you have no grievances/complaints of any kind whatsoever and that you waive your rights in this regard.
        </p>

        <p>
            You hereby commit and confirm that with the acceptance of possession, you have no claims, disputes, differences or demands against the Builder & Developer. You also agree to sign all documents, papers, forms, etc., as may be necessary for your unit and for the purpose of formation of the association of owners.
        </p>

        <p class="font-semibold text-slate-800">
            We shall be available during the handover period to assist you throughout the possession of your home. You shall also have the right to use {{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }} parking space ({{ $booking->parking_no ?? 'Allocated' }}).
        </p>
    </div>

    <!-- Property Information Table -->
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
        <table class="w-full text-xs">
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="py-2 px-4 text-slate-500 font-semibold w-1/3">Project / Address :</td>
                    <td class="py-2 px-4 text-slate-800 font-bold">{{ $project->name }} — {{ $project->full_address }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 text-slate-500 font-semibold">Unit & Floor :</td>
                    <td class="py-2 px-4 font-mono font-bold text-purple-900">{{ $booking->unit_no }} ({{ $booking->floor_no ?? 'Floor' }}, Block: {{ $booking->block_name ?? 'Block' }})</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 text-slate-500 font-semibold">*Built-up & Super Built-up Area :</td>
                    <td class="py-2 px-4 font-mono text-slate-800">{{ number_format($booking->built_up_area, 2) }} Sq. Ft. (Built-up) | {{ number_format($booking->super_built_up_area, 2) }} Sq. Ft. (Super Built-up)*</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 text-slate-500 font-semibold">RERA Reg. no. :</td>
                    <td class="py-2 px-4 font-mono text-emerald-800 font-bold">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Page Break for Clean 2-Page Print -->
    <div class="page-break pt-4"></div>

    <!-- Page 2: Financial Breakdown & Settlement Status -->
    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-800 text-white px-4 py-2 font-bold uppercase text-[11px] tracking-wider flex items-center justify-between">
            <span>Booking Financial Summary & Settlement Status</span>
            <span class="font-mono text-amber-300">{{ $booking->booking_code }}</span>
        </div>
        <table class="w-full text-xs font-mono">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Consideration value :</td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">₹{{ number_format($consideration, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Addons value (+) :</td>
                    <td class="py-2 px-4 text-right text-emerald-700">+ ₹{{ number_format($addons, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Dislodges value (-) :</td>
                    <td class="py-2 px-4 text-right text-rose-700">- ₹{{ number_format($dislodges, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold">
                    <td class="py-2 px-4 font-sans text-slate-700">Net total value (Gross Booking) :</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format($grossBookingValue, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Tax name & duty ({{ $booking->tax_name ?: 'GST' }} - {{ number_format($taxRate, 2) }}%) :</td>
                    <td class="py-2 px-4 text-right text-sky-800">+ ₹{{ number_format($taxAmount, 2) }}</td>
                </tr>
                @if($totalDiscount > 0)
                    <tr class="text-rose-700">
                        <td class="py-2 px-4 font-sans font-semibold">Discount / adjustments :</td>
                        <td class="py-2 px-4 text-right font-bold">- ₹{{ number_format($totalDiscount, 2) }}</td>
                    </tr>
                @endif
                <tr class="bg-purple-50/60 font-bold border-t-2 border-slate-300">
                    <td class="py-2.5 px-4 font-sans text-purple-950 text-sm">Final Booking value :</td>
                    <td class="py-2.5 px-4 text-right text-purple-950 font-black text-sm">₹{{ number_format($finalBookingValue, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Settlement Breakup Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="border border-slate-200 rounded-xl p-3.5 space-y-1 bg-slate-50 font-mono">
            <span class="font-bold text-slate-700 font-sans block text-[11px] uppercase tracking-wider mb-1">Party Self Arrangement :</span>
            <div class="flex justify-between"><span>Taxable Target:</span> <span>₹{{ number_format($partyTaxableTarget, 2) }}</span></div>
            <div class="flex justify-between"><span>Cash Target:</span> <span>₹{{ number_format($grossCash, 2) }}</span></div>
            <div class="flex justify-between text-emerald-700 font-bold"><span>#Received:</span> <span>₹{{ number_format($booking->taxable_self_received + $booking->total_cash_received, 2) }}</span></div>
            <div class="flex justify-between text-rose-700 font-bold border-t border-slate-200 pt-1"><span>Bal. Due:</span> <span>₹{{ number_format($booking->taxable_due_balance + $booking->cash_due_balance, 2) }}</span></div>
        </div>

        <div class="border border-slate-200 rounded-xl p-3.5 space-y-1 bg-slate-50 font-mono">
            <span class="font-bold text-slate-700 font-sans block text-[11px] uppercase tracking-wider mb-1">Bank / Finance Details :</span>
            <div class="flex justify-between"><span>Sanctioned Amount:</span> <span>₹{{ number_format($loanSanctioned, 2) }}</span></div>
            <div class="flex justify-between text-slate-600 font-sans text-[11px]"><span>Bank:</span> <span>{{ $booking->bankFinance?->bank_name ?? 'N/A' }}</span></div>
            <div class="flex justify-between text-emerald-700 font-bold"><span>#Received:</span> <span>₹{{ number_format($booking->loan_received, 2) }}</span></div>
            <div class="flex justify-between text-rose-700 font-bold border-t border-slate-200 pt-1"><span>Bal. Due:</span> <span>₹{{ number_format(max(0, $loanSanctioned - $booking->loan_received), 2) }}</span></div>
        </div>
    </div>

    <!-- Purchaser Acceptance Block -->
    <div class="border-2 border-slate-300 rounded-xl p-4 text-xs bg-white space-y-3">
        <p class="font-bold uppercase tracking-wider text-slate-900 text-[11px] text-center">Acceptance / Acknowledgement</p>
        <p class="text-slate-700 leading-relaxed text-justify">
            I/We, <strong>{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong>, have received the peaceful and physical possession of my own flat/unit 
            (Unit No: <strong>{{ $booking->unit_no }}</strong>) along with all keys, and irrevocably and unconditionally accept and confirm the content thereof.
        </p>

        <div class="pt-6 flex items-end justify-between text-xs text-slate-700">
            <div class="space-y-1">
                <div class="w-48 border-b border-slate-400 pb-1"></div>
                <p class="font-bold text-slate-900 text-[11px]">Purchaser / Allottee Signature</p>
                <p class="text-[10px] text-slate-500">{{ $booking->customer_name }}</p>
            </div>
            <div class="text-right space-y-1">
                <div class="w-48 border-b border-slate-400 pb-1 ml-auto"></div>
                <p class="font-bold text-slate-900 text-[11px]">For, {{ $company->name }}</p>
                <p class="text-[10px] text-slate-400">Authorized Signatory</p>
            </div>
        </div>
    </div>

    <div class="pt-1 text-center text-[10px] text-slate-400 italic">
        We wish you and your family a wonderful new beginning in your new home!
    </div>

</div>
@endsection
