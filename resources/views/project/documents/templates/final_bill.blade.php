@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Invoice Header -->
    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
        <div>
            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">FINAL BILL & SETTLEMENT STATEMENT</h2>
            <p class="text-xs text-slate-500">Comprehensive Pre-Registration Financial Clearance Statement</p>
        </div>
        <div class="text-right text-xs font-mono">
            <div>Invoice No: <strong>INV/{{ $booking->booking_code }}</strong></div>
            <div>Date: <strong>{{ date('d M, Y') }}</strong></div>
        </div>
    </div>

    <!-- Customer & Unit Quick Strip -->
    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs grid grid-cols-2 sm:grid-cols-4 gap-2">
        <div>
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Purchaser</span>
            <span class="font-bold text-slate-900">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</span>
        </div>
        <div>
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Unit No</span>
            <span class="font-bold text-indigo-900 font-mono">{{ $booking->unit_no }}</span>
        </div>
        <div>
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Super Built-up Area</span>
            <span class="font-bold text-slate-800 font-mono">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.</span>
        </div>
        <div>
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Agreement Status</span>
            <span class="font-bold text-emerald-800 uppercase">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
        </div>
    </div>

    <!-- Master Invoice Line Items Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
        <table class="w-full">
            <thead class="bg-slate-100 font-bold uppercase text-slate-700 border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-4 text-left">Description / Particulars</th>
                    <th class="py-2.5 px-4 text-center">Rate / Basis</th>
                    <th class="py-2.5 px-4 text-right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                
                <!-- Base Unit Cost -->
                <tr>
                    <td class="py-2 px-4 font-sans font-semibold text-slate-800">
                        Base Apartment Consideration ({{ $booking->property_type }})
                    </td>
                    <td class="py-2 px-4 text-center text-slate-600">
                        {{ number_format($booking->super_built_up_area, 0) }} sqft @ ₹{{ number_format($booking->rate_per_sqft, 2) }}
                    </td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">
                        ₹{{ number_format($booking->unit_cost, 2) }}
                    </td>
                </tr>

                <!-- Infrastructure & Parking -->
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-700">Designated Car Parking Space ({{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }})</td>
                    <td class="py-2 px-4 text-center text-slate-500">Fixed</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format($booking->parking_cost, 2) }}</td>
                </tr>

                <tr>
                    <td class="py-2 px-4 font-sans text-slate-700">Transformer, Electrical & Amenities Development</td>
                    <td class="py-2 px-4 text-center text-slate-500">Fixed</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format((float)$booking->transformer_cost + (float)$booking->amenities_cost, 2) }}</td>
                </tr>

                @if($booking->discount_applied > 0)
                    <tr class="text-rose-600">
                        <td class="py-2 px-4 font-sans font-medium">Special Discount Deducted</td>
                        <td class="py-2 px-4 text-center font-sans">Negotiated</td>
                        <td class="py-2 px-4 text-right font-bold">-₹{{ number_format($booking->discount_applied, 2) }}</td>
                    </tr>
                @endif

                <!-- GST Tax -->
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-700">Goods and Services Tax (GST)</td>
                    <td class="py-2 px-4 text-center text-slate-500">{{ $booking->tax_rate }}%</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format($booking->tax_amount, 2) }}</td>
                </tr>

                <!-- Job Sheet Customizations Rollup -->
                <tr>
                    <td class="py-2 px-4 font-sans font-semibold text-indigo-900">
                        Customization Job Sheets (Net Add-ons & Dislodges)
                    </td>
                    <td class="py-2 px-4 text-center text-indigo-700">{{ $booking->customizations->count() }} Line Items</td>
                    <td class="py-2 px-4 text-right font-bold text-indigo-900">
                        {{ $booking->supplementary_value >= 0 ? '+' : '' }}₹{{ number_format($booking->supplementary_value, 2) }}
                    </td>
                </tr>

                <!-- Gross Total Value -->
                <tr class="bg-slate-100 font-bold font-sans text-slate-900">
                    <td colspan="2" class="py-2.5 px-4 text-sm uppercase">Total Gross Contract Value</td>
                    <td class="py-2.5 px-4 text-right font-mono text-sm">₹{{ number_format($booking->total_booking_value, 2) }}</td>
                </tr>

                <!-- Less Payments Received -->
                <tr class="text-emerald-700 font-sans">
                    <td colspan="2" class="py-2 px-4">Less: Total Cleared Money Receipts (Bank Escrow)</td>
                    <td class="py-2 px-4 text-right font-mono font-bold">-₹{{ number_format($booking->total_taxable_received, 2) }}</td>
                </tr>

                <tr class="text-amber-700 font-sans">
                    <td colspan="2" class="py-2 px-4">Less: Total Cleared Receipt Vouchers (Cash Accounts)</td>
                    <td class="py-2 px-4 text-right font-mono font-bold">-₹{{ number_format($booking->total_cash_received, 2) }}</td>
                </tr>

                @if($booking->total_dishonor_penalties > 0)
                    <tr class="text-rose-600 font-sans">
                        <td colspan="2" class="py-2 px-4">Add: Cheque Bounce Penalties Incurred</td>
                        <td class="py-2 px-4 text-right font-mono font-bold">+₹{{ number_format($booking->total_dishonor_penalties, 2) }}</td>
                    </tr>
                @endif

                <!-- Final Net Payable -->
                <tr class="bg-slate-900 text-white font-black font-sans text-sm">
                    <td colspan="2" class="py-3 px-4 uppercase text-emerald-300">
                        Final Balance Due for Registry & Possession Handover
                    </td>
                    <td class="py-3 px-4 text-right font-mono text-base text-emerald-400">
                        ₹{{ number_format($booking->total_outstanding_due, 2) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>
@endsection
