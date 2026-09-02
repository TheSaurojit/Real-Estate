@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Recipient Address -->
    <div class="space-y-1">
        <p class="font-bold text-slate-900">To,</p>
        <p class="font-bold text-slate-800">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
        @if($booking->guardian_name)
            <p class="text-xs text-slate-600">{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: {{ $booking->guardian_name }}</p>
        @endif
        @if($booking->address)
            <p class="text-xs text-slate-600">{{ $booking->address }}</p>
        @endif
        <p class="text-xs text-slate-600">Mobile: {{ $booking->mobile_no }} | Email: {{ $booking->email_id ?? 'N/A' }}</p>
    </div>

    <!-- Subject -->
    <div class="font-bold uppercase tracking-wide text-slate-900 text-sm border-b border-slate-200 pb-2">
        Subject: Booking Confirmation for Flat/Unit No. {{ $booking->unit_no }} in "{{ $project->name }}".
    </div>

    <!-- Body Text -->
    <p>
        Dear {{ $booking->customer_salutation }} {{ $booking->customer_name }},
    </p>

    <p>
        We are pleased to confirm your booking for the residential/commercial unit in our prestigious project <strong>"{{ $project->name }}"</strong> located at <strong>{{ $project->full_address }}</strong>. The specific particulars of the allotted unit and commercial terms are set forth below:
    </p>

    <!-- Property Specifications Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-slate-100 font-bold uppercase text-slate-700 border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-4 text-left">Property Specification</th>
                    <th class="py-2.5 px-4 text-left">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2 px-4 font-semibold text-slate-600">Unit / Flat Identification</td>
                    <td class="py-2 px-4 font-bold text-slate-900 font-mono">{{ $booking->unit_no }} (Block: {{ $booking->block_name ?? '-' }}, Floor: {{ $booking->floor_no ?? '-' }})</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-semibold text-slate-600">Property Configuration & Type</td>
                    <td class="py-2 px-4 text-slate-800">{{ $booking->property_type }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-semibold text-slate-600">Super Built-up Area</td>
                    <td class="py-2 px-4 font-bold text-slate-900 font-mono">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft. (Built-up: {{ number_format($booking->built_up_area, 2) }} Sq. Ft.)</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-semibold text-slate-600">Car Parking Allotment</td>
                    <td class="py-2 px-4 text-slate-800">{{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }} (Bay No: {{ $booking->parking_no ?? 'Allocated at Possession' }})</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Commercial & Consideration Summary -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <div class="bg-slate-100 px-4 py-2 font-bold uppercase text-xs text-slate-700 border-b border-slate-300">
            Agreed Commercial Consideration
        </div>
        <table class="w-full text-xs font-mono">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Unit Base Rate per Sq. Ft.</td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">₹{{ number_format($booking->rate_per_sqft, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Base Unit Consideration</td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">₹{{ number_format($booking->unit_cost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Parking & Infrastructure Charges</td>
                    <td class="py-2 px-4 text-right text-slate-800">+₹{{ number_format((float)$booking->parking_cost + (float)$booking->transformer_cost + (float)$booking->amenities_cost, 2) }}</td>
                </tr>
                @if($booking->discount_applied > 0)
                    <tr>
                        <td class="py-2 px-4 font-sans text-rose-600">Special Discount Deducted</td>
                        <td class="py-2 px-4 text-right font-bold text-rose-600">-₹{{ number_format($booking->discount_applied, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Applicable GST ({{ $booking->tax_rate }}%)</td>
                    <td class="py-2 px-4 text-right text-slate-800">+₹{{ number_format($booking->tax_amount, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold">
                    <td class="py-2.5 px-4 font-sans text-slate-900 text-sm">Total Contract Value (Before Customizations)</td>
                    <td class="py-2.5 px-4 text-right text-sm text-slate-900">₹{{ number_format($booking->total_booking_value, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Terms and Instructions -->
    <div class="text-xs text-slate-600 space-y-2">
        <p>
            1. All subsequent payments must be credited strictly via banking instruments / online NEFT / RTGS to our designated Escrow Bank Account.
        </p>
        <p>
            2. The formal Sale Agreement (Bayna-nama) will be executed on non-judicial stamp paper upon completion of the initial booking installment.
        </p>
    </div>

</div>
@endsection
