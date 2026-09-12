@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Invoice Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b-2 border-slate-900 pb-4">
        <div>
            <h2 class="text-2xl font-black uppercase tracking-tight text-slate-900">PROFORMA INVOICE</h2>
            <p class="text-xs text-slate-500 mt-0.5">Commercial Tax Pre-Invoice for Flat/Unit Allotment</p>
        </div>
        <div class="text-left sm:text-right text-xs font-mono space-y-1">
            <p class="text-slate-800 font-bold">
                Invoice no. : <span class="text-cyan-700">PI/{{ date('y-m') }}/{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span>
            </p>
            <p class="text-slate-600">Invoice date : {{ date('d F Y') }}</p>
            <p class="text-slate-600">Booking id : {{ $booking->booking_code }}</p>
            <p class="text-slate-600">Booking date : {{ $booking->booking_date ? $booking->booking_date->format('d F Y') : '-' }}</p>
        </div>
    </div>

    <!-- Client & Project Info Dual Box -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <!-- Bill To -->
        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Bill To :</span>
            <p class="font-bold text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
            @if($booking->guardian_name)
                <p class="text-slate-600">{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: {{ $booking->guardian_name }}</p>
            @endif
            @if($booking->address)
                <p class="text-slate-600 leading-relaxed">{{ $booking->address }}</p>
            @endif
            <p class="text-slate-700 pt-1 font-mono">
                PAN: <strong>{{ $booking->pan_number ?? 'Not available' }}</strong> | GSTIN: <strong>{{ $booking->gstin ?? 'Not available' }}</strong>
            </p>
        </div>

        <!-- Project & Property Details -->
        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Property Specifications :</span>
            <p class="font-bold text-slate-900">{{ $project->name }}</p>
            <p class="text-slate-600 text-[11px] leading-relaxed">
                Dag No.: {{ $project->daag_no ?? '-' }} | Patta No.: {{ $project->patta_no ?? '-' }} [2nd RS]<br>
                Holding No.: {{ $project->holding_no ?? '-' }}, {{ $project->full_address }}
            </p>
            <p class="text-slate-700 pt-1">
                <strong>Unit :</strong> <span class="font-mono font-bold">{{ $booking->unit_no }}</span> ({{ $booking->block_name ?? 'Block' }}, Floor: {{ $booking->floor_no ?? 'Floor' }})
            </p>
            <p class="text-slate-600 font-mono text-[11px]">
                Super Built-up Area: {{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.* | RERA: <strong>{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</strong>
            </p>
        </div>
    </div>

    <!-- Commercial Items Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                <tr>
                    <th class="py-3 px-4">Description of Service</th>
                    <th class="py-3 px-3 text-center">HSN / SAC Code</th>
                    <th class="py-3 px-3 text-center">Taxation Details</th>
                    <th class="py-3 px-4 text-right">Taxable Amount (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                <tr>
                    <td class="py-3 px-4">
                        <span class="font-bold text-slate-900 block">Residential Flat / Apartment Allotment</span>
                        <span class="text-[11px] text-slate-500">Unit No: {{ $booking->unit_no }} in {{ $project->name }} with {{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }}</span>
                    </td>
                    <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">995411</td>
                    <td class="py-3 px-3 text-center font-semibold text-slate-700">{{ $booking->tax_name ?: 'GST' }} ({{ number_format($taxRate, 2) }}%)</td>
                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                        ₹{{ number_format($consideration + $supplementary, 2) }}
                    </td>
                </tr>
            </tbody>
            <tfoot class="bg-slate-50 font-mono text-xs divide-y divide-slate-200 border-t-2 border-slate-300">
                @php
                    $cgstRate = $taxRate / 2;
                    $sgstRate = $taxRate / 2;
                    $cgstAmount = $taxAmount / 2;
                    $sgstAmount = $taxAmount / 2;
                @endphp
                <tr>
                    <td colspan="3" class="py-2 px-4 font-sans font-semibold text-slate-700 text-right">Output Tax (CGST @ {{ number_format($cgstRate, 2) }}%) :</td>
                    <td class="py-2 px-4 text-right text-sky-900">₹{{ number_format($cgstAmount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="py-2 px-4 font-sans font-semibold text-slate-700 text-right">Output Tax (SGST @ {{ number_format($sgstRate, 2) }}%) :</td>
                    <td class="py-2 px-4 text-right text-sky-900">₹{{ number_format($sgstAmount, 2) }}</td>
                </tr>
                <tr class="bg-sky-50/70 font-bold">
                    <td colspan="3" class="py-2 px-4 font-sans text-sky-950 text-right">Output Tax (Total GST) :</td>
                    <td class="py-2 px-4 text-right text-sky-950">₹{{ number_format($taxAmount, 2) }}</td>
                </tr>
                <tr class="bg-indigo-50/80 font-bold text-sm">
                    <td colspan="3" class="py-3 px-4 font-sans text-indigo-950 text-right">Gross Total Invoice Amount :</td>
                    <td class="py-3 px-4 text-right text-indigo-950 font-black text-base">₹{{ number_format($finalBookingValue, 2) }}</td>
                </tr>
                <tr class="bg-slate-100 font-sans text-[11px]">
                    <td colspan="4" class="py-2.5 px-4 text-right italic font-bold text-slate-800">
                        Amount in words: {{ $amountInWords }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Terms & Conditions -->
    <div class="border border-slate-200 rounded-xl p-4 text-xs text-slate-600 space-y-1">
        <div class="flex items-center justify-between font-bold text-slate-700 mb-1">
            <span class="uppercase tracking-wide text-[11px]">Terms & Conditions :</span>
            <span class="font-mono text-[10px]">E. & O. E.</span>
        </div>
        <ol class="list-decimal list-inside space-y-0.5 text-[11px]">
            <li>All payments are subjected to realization.</li>
            <li>E-copy of instrument, digital signature not required.</li>
            <li>All disputes are subjected to local jurisdiction only.</li>
        </ol>
        <p class="pt-1 font-bold text-slate-800 text-[11px]">Thank you for business with us !</p>
    </div>

    <!-- Signatures -->
    <div class="pt-6 flex items-end justify-between text-xs text-slate-600">
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
