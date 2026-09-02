@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Title Banner -->
    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
        <div>
            <h2 class="text-xl font-black uppercase tracking-tight text-emerald-800">MONEY RECEIPT (TAXABLE BANKING)</h2>
            <p class="text-xs text-slate-500">Official GST-Compliant Tax Receipt for Sale Agreement & Construction Dues</p>
        </div>
        <div class="text-right text-xs font-mono">
            <div>Receipt No: <strong>{{ $transaction?->transaction_code ?? $booking->booking_code . '/RCD' }}</strong></div>
            <div>Date: <strong>{{ $transaction?->voucher_date?->format('d M, Y') ?? date('d M, Y') }}</strong></div>
        </div>
    </div>

    <!-- Receipt Details Card -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
        <div class="grid grid-cols-3 gap-2">
            <span class="text-slate-500 font-medium">Received with thanks from:</span>
            <span class="col-span-2 font-bold text-slate-900 text-sm">
                {{ $booking->customer_salutation }} {{ $booking->customer_name }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="text-slate-500 font-medium">Towards Apartment / Property:</span>
            <span class="col-span-2 text-slate-800 font-medium">
                Unit No. <strong class="text-indigo-900 font-mono">{{ $booking->unit_no }}</strong> (Block: {{ $booking->block_name ?? '-' }}, Floor: {{ $booking->floor_no ?? '-' }}), {{ $project->name }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="text-slate-500 font-medium">GST Identification (GSTIN):</span>
            <span class="col-span-2 text-slate-700 font-mono">{{ $company->gstin ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Amount Highlight Box -->
    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-900 text-white font-mono">
        <div>
            <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-sans">Amount Credited in Bank</span>
            <span class="text-2xl font-black text-emerald-400">₹{{ number_format($transaction?->amount ?? $booking->total_taxable_received, 2) }}</span>
        </div>
        <div class="text-right text-xs font-sans text-slate-300">
            <span>Payment Mode: <strong>{{ strtoupper($transaction?->transaction_mode ?? 'BANKING') }}</strong></span>
            @if($transaction?->source_of_payment === 'through_loan_account')
                <span class="block text-emerald-300 text-[11px] mt-0.5">(Home Loan Bank Disbursement)</span>
            @endif
        </div>
    </div>

    <!-- Banking Instrument Details -->
    <div class="grid grid-cols-2 gap-4 text-xs">
        <div>
            <span class="text-slate-500 block">Instrument / UTR Ref No:</span>
            <span class="font-mono font-bold text-slate-800">{{ $transaction?->instrument_ref_no ?? 'N/A' }}</span>
        </div>
        <div>
            <span class="text-slate-500 block">Instrument Date:</span>
            <span class="font-medium text-slate-800">{{ $transaction?->instrument_date?->format('d M, Y') ?? date('d M, Y') }}</span>
        </div>
        <div>
            <span class="text-slate-500 block">Credited Bank Escrow Account:</span>
            <span class="font-medium text-slate-800">{{ $transaction?->bankAccount?->account_nick_name ?? ($escrowAccount?->account_nick_name ?? 'HDFC Escrow') }}</span>
        </div>
        <div>
            <span class="text-slate-500 block">Clearance Status:</span>
            <span class="font-bold text-emerald-700 uppercase">{{ ucfirst($transaction?->instrument_status ?? 'Cleared') }}</span>
        </div>
    </div>

    @if($transaction?->particulars)
        <div class="text-xs text-slate-600 pt-2 border-t border-slate-100">
            <span class="font-bold text-slate-700">Narrative / Particulars:</span> {{ $transaction->particulars }}
        </div>
    @endif

</div>
@endsection
