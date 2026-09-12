@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    @php
        $modeLabel = match($receiptMode) {
            'single'              => 'Money Receipt / Receipt Voucher',
            'all_money_receipts'  => 'Consolidated Money Receipts (Taxable Banking / GST)',
            'all_receipt_vouchers'=> 'Consolidated Receipt Vouchers (Non-Taxable Cash)',
            default               => 'All Receipts Statement (Dual-Ledger Collections)',
        };
    @endphp

    <!-- Mode Badge & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-900 font-bold uppercase text-xs tracking-wider rounded-md border border-emerald-200 mb-1">
                8. {{ $modeLabel }}
            </div>
            <h2 class="text-xl font-black uppercase text-slate-900">{{ $project->name }}</h2>
            <p class="text-xs text-slate-600">
                Customer: <strong>{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong> 
                (Unit: <strong>{{ $booking->unit_no }}</strong>, Block: {{ $booking->block_name ?? 'I' }})
            </p>
        </div>
        <div class="text-left sm:text-right text-xs space-y-1">
            <p class="text-slate-600"><strong>Booking Code :</strong> <span class="font-mono font-bold">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Date :</strong> {{ date('d F Y') }}</p>
        </div>
    </div>

    @if($receiptMode === 'single' && $transaction)
        <!-- Single Receipt Voucher Layout -->
        <div class="border-2 border-slate-300 rounded-2xl overflow-hidden bg-white p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Voucher Type</span>
                    <span class="text-sm font-black text-slate-900">
                        {{ $transaction->voucher_type === 'money_receipt' ? 'Official Money Receipt (Taxable Banking)' : 'Cash Receipt Voucher (Non-Taxable)' }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Voucher No</span>
                    <span class="font-mono text-sm font-black text-emerald-700">{{ $transaction->voucher_no ?: $transaction->transaction_code }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Date of Receipt</span>
                    <span class="font-bold text-slate-800">{{ $transaction->voucher_date ? $transaction->voucher_date->format('d M, Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Payment Mode</span>
                    <span class="font-bold uppercase text-slate-800">{{ $transaction->transaction_mode }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Instrument Ref</span>
                    <span class="font-mono font-semibold text-slate-800">{{ $transaction->instrument_ref_no ?: 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Credited Account</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->bankAccount ? $transaction->bankAccount->account_nick_name : 'Cash in Hand' }}</span>
                </div>
            </div>

            <!-- Amount Banner -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900 block">Received Amount</span>
                    <span class="font-mono text-2xl font-black text-emerald-950">₹{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="text-right text-xs italic font-bold text-emerald-900 max-w-md">
                    {{ \App\Services\AutoNumberService::numberToIndianWords($transaction->amount) }}
                </div>
            </div>

            @if($transaction->particulars)
                <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <strong>Particulars / Narration :</strong> {{ $transaction->particulars }}
                </div>
            @endif
        </div>
    @else
        <!-- Consolidated Receipts Table -->
        <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
            <div class="bg-slate-800 text-white px-4 py-2.5 font-bold uppercase text-xs tracking-wider flex items-center justify-between">
                <span>Collections Registry</span>
                <span class="font-mono text-emerald-400">{{ $receiptTransactions->count() }} vouchers</span>
            </div>
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Voucher No</th>
                        <th class="py-2.5 px-3">Mode</th>
                        <th class="py-2.5 px-3">Instrument Details</th>
                        <th class="py-2.5 px-3">Credited A/C</th>
                        <th class="py-2.5 px-4 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($receiptTransactions as $t)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2 px-3 whitespace-nowrap">{{ $t->voucher_date ? $t->voucher_date->format('d-m-Y') : '-' }}</td>
                            <td class="py-2 px-3 whitespace-nowrap">
                                @if($t->voucher_type === 'money_receipt')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Taxable Bank</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Cash Voucher</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $t->voucher_no ?: $t->transaction_code }}</td>
                            <td class="py-2 px-3 uppercase text-slate-700">{{ $t->transaction_mode }}</td>
                            <td class="py-2 px-3 font-mono text-slate-600">{{ $t->instrument_ref_no ?: 'N/A' }}</td>
                            <td class="py-2 px-3 text-slate-700">{{ $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash in Hand' }}</td>
                            <td class="py-2 px-4 text-right font-mono font-bold text-emerald-800 whitespace-nowrap">
                                ₹{{ number_format($t->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No collection vouchers found for this selection.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($receiptTransactions->isNotEmpty())
                    <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300 font-mono text-xs">
                        <tr>
                            <td colspan="6" class="py-2.5 px-4 font-sans text-slate-900 text-right">Total Collections Cleared :</td>
                            <td class="py-2.5 px-4 text-right text-emerald-950 font-black text-sm">
                                ₹{{ number_format($receiptTransactions->sum('amount'), 2) }}
                            </td>
                        </tr>
                        <tr class="bg-slate-100 font-sans text-[11px]">
                            <td colspan="7" class="py-2 px-4 text-right italic text-slate-700">
                                {{ \App\Services\AutoNumberService::numberToIndianWords($receiptTransactions->sum('amount')) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    <!-- Notes & Terms -->
    <div class="text-xs text-slate-500 space-y-1 pt-2">
        <p>1. All receipts are subject to realization of cheque / instrument clearance.</p>
        <p>2. Computer generated receipt statement, signature as acknowledgement.</p>
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
            <p class="text-[11px] text-slate-400">Authorized Signatory / Cashier</p>
        </div>
    </div>

</div>
@endsection
