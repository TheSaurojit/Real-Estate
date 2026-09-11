@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Actions (Hidden when printing) -->
    <div class="flex items-center justify-between print:hidden">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.transactions.index', $project->id) }}" class="hover:text-sky-600">Transactions</a>
                <span>/</span>
                <span class="text-slate-600">{{ $transaction->transaction_code }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Voucher Receipt</h1>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Document</span>
            </button>
            <a href="{{ route('project.transactions.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Official Printable Voucher Sheet -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-12 print:border-none print:shadow-none print:p-0 space-y-8 font-sans text-slate-800">
        
        <!-- Company Letterhead Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6">
            <div class="space-y-1 max-w-md">
                @if($transaction->company->logo_path)
                    <img src="{{ asset('storage/' . $transaction->company->logo_path) }}" alt="Company Logo" class="h-12 object-contain mb-2">
                @endif
                <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">{{ $transaction->company->name }}</h2>
                <p class="text-xs text-slate-500 leading-relaxed">{{ $transaction->company->address }}</p>
                <div class="text-[11px] text-slate-600 font-mono space-x-3 pt-1">
                    <span>GSTIN: <strong>{{ $transaction->company->gstin ?? 'N/A' }}</strong></span>
                    <span>PAN: <strong>{{ $transaction->company->pan_number ?? 'N/A' }}</strong></span>
                </div>
            </div>
            <div class="text-right space-y-1">
                <div class="inline-block px-3 py-1 rounded-lg font-bold uppercase text-xs tracking-wider {{ $transaction->voucher_type === 'money_receipt' ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : ($transaction->voucher_type === 'receipt_voucher' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-rose-100 text-rose-900 border border-rose-300') }}">
                    {{ str_replace('_', ' ', strtoupper($transaction->voucher_type)) }}
                    @if($transaction->voucher_type === 'payment_voucher')
                        <span>({{ ($transaction->payment_category ?? ($transaction->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable' ? 'TAXABLE' : 'NON-TAXABLE' }})</span>
                    @endif
                </div>
                <div class="text-xs font-mono font-bold text-slate-800 pt-1">
                    Code: {{ $transaction->transaction_code }}
                </div>
                @if($transaction->voucher_no)
                    <div class="text-xs font-mono font-bold text-indigo-800">
                        Voucher No: {{ $transaction->voucher_no }}
                    </div>
                @endif
                <div class="text-xs text-slate-500">
                    Date: <strong>{{ $transaction->voucher_date->format('d M, Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Receipt Body Details -->
        <div class="space-y-4 text-sm leading-relaxed">
            
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <span class="text-slate-500 font-medium">Received with thanks from:</span>
                    <span class="col-span-2 font-bold text-slate-900 text-sm">
                        {{ $transaction->booking ? $transaction->booking->customer_salutation . ' ' . $transaction->booking->customer_name : 'N/A' }}
                    </span>
                </div>
                @if($transaction->booking && $transaction->booking->guardian_name)
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <span class="text-slate-500 font-medium">{{ str_replace('_', ' ', ucfirst($transaction->booking->guardian_relation)) }}:</span>
                        <span class="col-span-2 text-slate-800 font-medium">{{ $transaction->booking->guardian_name }}</span>
                    </div>
                @endif
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <span class="text-slate-500 font-medium">On account of Project:</span>
                    <span class="col-span-2 text-slate-800 font-medium">
                        <strong>{{ $project->name }}</strong> (Unit No: <strong class="text-indigo-700 font-mono">{{ $transaction->booking?->unit_no ?? '-' }}</strong>, {{ $transaction->booking?->property_type }})
                    </span>
                </div>
                @if($transaction->booking?->address)
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <span class="text-slate-500 font-medium">Customer Address:</span>
                        <span class="col-span-2 text-slate-700">{{ $transaction->booking->address }}</span>
                    </div>
                @endif
            </div>

            <!-- Amount Highlight Box -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-900 text-white font-mono">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-sans">Amount Received</span>
                    <span class="text-2xl font-black text-emerald-400">₹{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="text-right text-xs font-sans text-slate-300">
                    <span>Mode: <strong>{{ strtoupper($transaction->transaction_mode) }}</strong></span>
                    @if($transaction->source_of_payment === 'through_loan_account')
                        <span class="block text-emerald-300 font-medium text-[11px] mt-0.5">(Home Loan Bank Disbursement)</span>
                    @endif
                </div>
            </div>

            <!-- Payment Instrument Specifications -->
            <div class="grid grid-cols-2 gap-4 text-xs pt-2">
                <div>
                    <span class="text-slate-500 block">Instrument / UTR Reference No:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $transaction->instrument_ref_no ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Instrument Date:</span>
                    <span class="font-medium text-slate-800">{{ $transaction->instrument_date ? $transaction->instrument_date->format('d M, Y') : 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Bank Account:</span>
                    <span class="font-medium text-slate-800">{{ $transaction->bankAccount ? $transaction->bankAccount->account_nick_name : 'Cash Account' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Clearance Status:</span>
                    <span class="font-bold {{ $transaction->instrument_status === 'dishonored' ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $transaction->instrument_status)) }}
                    </span>
                </div>
                @if($transaction->voucher_type === 'payment_voucher')
                    <div class="col-span-2 pt-1 border-t border-slate-100">
                        <span class="text-slate-500 block">Payment Category:</span>
                        <span class="font-bold {{ ($transaction->payment_category ?? ($transaction->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable' ? 'text-rose-700' : 'text-amber-700' }}">
                            {{ ($transaction->payment_category ?? ($transaction->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable' ? 'Taxable Payment (Agreement & Bank Escrow Outflow)' : 'Non-Taxable Payment (Cash Payout)' }}
                        </span>
                    </div>
                @endif
            </div>

            @if($transaction->particulars)
                <div class="text-xs text-slate-600 pt-2 border-t border-slate-100">
                    <span class="font-bold text-slate-700">Narrative:</span> {{ $transaction->particulars }}
                </div>
            @endif

        </div>

        <!-- Signature Footer -->
        <div class="pt-16 flex items-end justify-between text-xs text-slate-500 border-t border-slate-200">
            <div>
                <p>Prepared by: <strong>{{ $transaction->creator?->name ?? 'System Admin' }}</strong></p>
                <p class="text-[10px] text-slate-400">Printed on {{ date('d M, Y h:i A') }}</p>
            </div>
            <div class="text-center space-y-1">
                <div class="w-48 border-b border-slate-400 pb-1"></div>
                <span class="font-bold uppercase tracking-wider text-slate-800 text-[11px] block">Authorized Signatory</span>
                <span class="text-[10px] text-slate-400">For {{ $transaction->company->name }}</span>
            </div>
        </div>

    </div>

</div>
@endsection
