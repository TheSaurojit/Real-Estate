@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
        <div>
            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">STATEMENT OF ACCOUNT / CUSTOMER LEDGER</h2>
            <p class="text-xs text-slate-500">Comprehensive Chronological Statement of Debits, Credits & Dual-Ledger Balances</p>
        </div>
        <div class="text-right text-xs font-mono">
            <div>Statement Date: <strong>{{ date('d M, Y') }}</strong></div>
            <div>Account Status: <strong class="text-emerald-700 uppercase">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</strong></div>
        </div>
    </div>

    <!-- Live Ledger Matrix Card -->
    <div class="bg-slate-900 text-white p-5 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 space-y-1">
            <div class="text-[10px] text-emerald-300 uppercase font-bold tracking-wider font-sans">Taxable Banking Stream</div>
            <div class="text-slate-400">Target: <span class="text-white font-semibold">₹{{ number_format($booking->gross_taxable_value, 2) }}</span></div>
            <div class="text-slate-400">Paid: <span class="text-emerald-400 font-semibold">₹{{ number_format($booking->total_taxable_received, 2) }}</span></div>
            <div class="pt-1 border-t border-slate-700 text-white font-bold flex items-center justify-between">
                <span>Taxable Due:</span>
                <span class="text-amber-300">₹{{ number_format($booking->taxable_due_balance, 2) }}</span>
            </div>
        </div>

        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 space-y-1">
            <div class="text-[10px] text-amber-300 uppercase font-bold tracking-wider font-sans">Non-Taxable Cash Stream</div>
            <div class="text-slate-400">Target: <span class="text-white font-semibold">₹{{ number_format($booking->gross_cash_value, 2) }}</span></div>
            <div class="text-slate-400">Paid: <span class="text-amber-400 font-semibold">₹{{ number_format($booking->total_cash_received, 2) }}</span></div>
            <div class="pt-1 border-t border-slate-700 text-white font-bold flex items-center justify-between">
                <span>Cash Due:</span>
                <span class="text-amber-300">₹{{ number_format($booking->cash_due_balance, 2) }}</span>
            </div>
        </div>

        <div class="bg-emerald-950/60 p-3 rounded-xl border border-emerald-800 space-y-1 flex flex-col justify-between font-sans">
            <div>
                <div class="text-[10px] text-emerald-400 uppercase font-bold tracking-wider">Total Net Outstanding Due</div>
                <div class="text-2xl font-black font-mono text-white mt-1">₹{{ number_format($booking->total_outstanding_due, 2) }}</div>
            </div>
            @if($booking->total_dishonor_penalties > 0)
                <div class="text-[10px] text-rose-300 font-mono">
                    (Includes ₹{{ number_format($booking->total_dishonor_penalties, 2) }} Bounced Cheque Penalties)
                </div>
            @endif
        </div>
    </div>

    <!-- Transaction Ledger Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
        <table class="w-full">
            <thead class="bg-slate-100 font-bold uppercase text-slate-700 border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-3 text-left">Date</th>
                    <th class="py-2.5 px-3 text-left">Voucher ID & Particulars</th>
                    <th class="py-2.5 px-3 text-left">Stream / Mode</th>
                    <th class="py-2.5 px-3 text-right">Debit (₹)</th>
                    <th class="py-2.5 px-3 text-right">Credit (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                
                <!-- Initial Booking Charge Entry -->
                <tr>
                    <td class="py-2 px-3 align-top">{{ $booking->booking_date->format('d M, Y') }}</td>
                    <td class="py-2 px-3 align-top font-sans">
                        <div class="font-bold text-slate-900">Apartment Booking Contract Consideration</div>
                        <div class="text-slate-500 text-[11px]">Unit: {{ $booking->unit_no }} ({{ number_format($booking->super_built_up_area, 0) }} sqft)</div>
                    </td>
                    <td class="py-2 px-3 align-top text-slate-600 font-sans">Contract Booking</td>
                    <td class="py-2 px-3 align-top text-right font-bold text-slate-900">₹{{ number_format($booking->total_booking_value, 2) }}</td>
                    <td class="py-2 px-3 align-top text-right text-slate-400">-</td>
                </tr>

                <!-- Payments Credited -->
                @foreach($booking->transactions as $tx)
                    <tr>
                        <td class="py-2 px-3 align-top">{{ $tx->voucher_date->format('d M, Y') }}</td>
                        <td class="py-2 px-3 align-top font-sans">
                            <div class="font-bold text-slate-900">{{ $tx->transaction_code }}</div>
                            <div class="text-slate-500 text-[11px]">{{ $tx->particulars ?? 'Payment Received' }}</div>
                        </td>
                        <td class="py-2 px-3 align-top font-sans">
                            <span class="uppercase font-semibold text-slate-700">{{ $tx->transaction_mode }}</span>
                            <span class="text-[10px] text-slate-400 block">({{ $tx->is_taxable_transaction ? 'Bank/Taxable' : 'Cash' }})</span>
                        </td>
                        @if($tx->voucher_category === 'payment_refund')
                            <td class="py-2 px-3 align-top text-right text-rose-600 font-bold">₹{{ number_format($tx->amount, 2) }}</td>
                            <td class="py-2 px-3 align-top text-right text-slate-400">-</td>
                        @else
                            <td class="py-2 px-3 align-top text-right text-slate-400">-</td>
                            <td class="py-2 px-3 align-top text-right text-emerald-700 font-bold">₹{{ number_format($tx->amount, 2) }}</td>
                        @endif
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</div>
@endsection
