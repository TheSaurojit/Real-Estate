@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Recipient Address -->
    <div class="space-y-1">
        <p class="font-bold text-slate-900">To,</p>
        <p class="font-bold text-slate-800">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
        <p class="text-xs text-slate-600">Unit: <strong>{{ $booking->unit_no }}</strong> | Project: <strong>{{ $project->name }}</strong></p>
        <p class="text-xs text-slate-600">Mobile: {{ $booking->mobile_no }}</p>
    </div>

    <!-- Subject Banner -->
    <div class="p-3 bg-amber-50 border border-amber-300 rounded-xl text-amber-900 font-bold uppercase text-xs tracking-wider flex items-center justify-between">
        <span>DEMAND NOTICE / PAYMENT CALL FOR UNIT NO. {{ $booking->unit_no }}</span>
        <span class="font-mono text-slate-900">Due Date: {{ date('d M, Y', strtotime('+15 days')) }}</span>
    </div>

    <p>
        Dear {{ $booking->customer_salutation }} {{ $booking->customer_name }},
    </p>

    <p>
        We wish to inform you that construction work at <strong>"{{ $project->name }}"</strong> has progressed to the scheduled milestone. In accordance with the payment schedule of your allotment, the subsequent installment is now due for payment.
    </p>

    <!-- Demand Breakdown Card -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <div class="bg-slate-100 px-4 py-2 font-bold uppercase text-xs text-slate-800 border-b border-slate-300">
            Current Account Standing & Demand Breakdown
        </div>
        <table class="w-full text-xs font-mono">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-600">Total Contract Booking Value</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">₹{{ number_format($booking->total_booking_value, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-600">Total Taxable Bank Target (Agreement + GST)</td>
                    <td class="py-2.5 px-4 text-right text-slate-800">₹{{ number_format($booking->gross_taxable_value, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-600">Total Bank Collections Credited to Date</td>
                    <td class="py-2.5 px-4 text-right text-emerald-700 font-bold">-₹{{ number_format($booking->total_taxable_received, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-600">Total Cash Collections Credited to Date</td>
                    <td class="py-2.5 px-4 text-right text-amber-700 font-bold">-₹{{ number_format($booking->total_cash_received, 2) }}</td>
                </tr>
                @if($booking->total_dishonor_penalties > 0)
                    <tr>
                        <td class="py-2.5 px-4 font-sans text-rose-600">Cheque Bounce Penalties</td>
                        <td class="py-2.5 px-4 text-right text-rose-600 font-bold">+₹{{ number_format($booking->total_dishonor_penalties, 2) }}</td>
                    </tr>
                @endif
                <tr class="bg-amber-100/60 font-black">
                    <td class="py-3 px-4 font-sans text-amber-950 text-sm uppercase">Total Immediate Outstanding Balance Due</td>
                    <td class="py-3 px-4 text-right text-base text-amber-950">₹{{ number_format($booking->total_outstanding_due, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Escrow Bank Account Transfer Details -->
    <div class="bg-slate-900 text-white p-5 rounded-2xl space-y-2 text-xs font-mono">
        <div class="text-xs uppercase font-bold text-sky-400 font-sans tracking-wider border-b border-slate-700 pb-1">
            Bank Escrow Remittance Details (For NEFT / RTGS / Cheque)
        </div>
        <div class="grid grid-cols-2 gap-2 pt-1 text-slate-300">
            <div>Account Name: <strong class="text-white">{{ $escrowAccount?->account_name ?? $company->name }}</strong></div>
            <div>Bank Name: <strong class="text-white">{{ $escrowAccount?->bank_name ?? 'HDFC Bank' }}</strong></div>
            <div>Account Number: <strong class="text-emerald-400 text-sm">{{ $escrowAccount?->account_number ?? '50200012345678' }}</strong></div>
            <div>IFSC Code: <strong class="text-white">{{ $escrowAccount?->ifsc_code ?? 'HDFC0001234' }}</strong></div>
            <div>Branch: <strong class="text-white">{{ $escrowAccount?->branch ?? 'Silchar' }}</strong></div>
            <div>Account Type: <strong class="text-white uppercase">{{ $escrowAccount?->account_type ?? 'Escrow' }}</strong></div>
        </div>
    </div>

    <p class="text-xs text-slate-600">
        Kindly remit the due amount on or before <strong>{{ date('d M, Y', strtotime('+15 days')) }}</strong> to avoid interest charges and ensure uninterrupted construction scheduling. Please share the UTR reference number upon successful transfer.
    </p>

</div>
@endsection
