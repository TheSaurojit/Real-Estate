@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Title Banner -->
    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
        <div>
            <h2 class="text-xl font-black uppercase tracking-tight text-rose-800">PAYMENT & REFUND OUTFLOW VOUCHER</h2>
            <p class="text-xs text-slate-500">Official Debit Voucher for Outward Disbursements & Refunds</p>
        </div>
        <div class="text-right text-xs font-mono">
            <div>Voucher No: <strong>{{ $transaction?->transaction_code ?? $booking->booking_code . '/PMT' }}</strong></div>
            <div>Date: <strong>{{ $transaction?->voucher_date?->format('d M, Y') ?? date('d M, Y') }}</strong></div>
        </div>
    </div>

    <!-- Payout Details Card -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
        <div class="grid grid-cols-3 gap-2">
            <span class="text-slate-500 font-medium">Paid To / Beneficiary:</span>
            <span class="col-span-2 font-bold text-slate-900 text-sm">
                {{ $booking->customer_salutation }} {{ $booking->customer_name }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="text-slate-500 font-medium">On Account of Unit / Booking:</span>
            <span class="col-span-2 text-slate-800 font-medium">
                Unit No. <strong class="text-indigo-900 font-mono">{{ $booking->unit_no }}</strong>, {{ $project->name }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="text-slate-500 font-medium">Outflow Type:</span>
            <span class="col-span-2 font-bold text-rose-700">Refund Payout / Ledger Outflow</span>
        </div>
    </div>

    <!-- Amount Highlight Box -->
    <div class="flex items-center justify-between p-4 rounded-xl bg-rose-950 text-white font-mono">
        <div>
            <span class="text-[10px] text-rose-300 uppercase tracking-wider block font-sans">Amount Disbursed</span>
            <span class="text-2xl font-black text-rose-400">₹{{ number_format($transaction?->amount ?? 0, 2) }}</span>
        </div>
        <div class="text-right text-xs font-sans text-rose-200">
            <span>Payment Mode: <strong>{{ strtoupper($transaction?->transaction_mode ?? 'BANK TRANSFER') }}</strong></span>
        </div>
    </div>

    @if($transaction?->particulars)
        <div class="text-xs text-slate-700 p-3 bg-rose-50/60 rounded-xl border border-rose-200">
            <span class="font-bold text-slate-800">Particulars:</span> {{ $transaction->particulars }}
        </div>
    @endif

</div>
@endsection
