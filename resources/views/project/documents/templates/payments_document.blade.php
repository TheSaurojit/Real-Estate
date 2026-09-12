@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    @php
        $modeLabel = match($paymentMode) {
            'single'         => 'Payment / Refund Outflow Voucher',
            'all_taxable'    => 'Consolidated Taxable Payment & Refund Outflows',
            'all_nontaxable' => 'Consolidated Non-Taxable / Cash Payouts',
            default          => 'All Payments & Refund Outflows Statement',
        };
    @endphp

    <!-- Mode Badge & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="inline-block px-3 py-1 bg-rose-50 text-rose-900 font-bold uppercase text-xs tracking-wider rounded-md border border-rose-200 mb-1">
                9. {{ $modeLabel }}
            </div>
            <h2 class="text-xl font-black uppercase text-slate-900">{{ $project->name }}</h2>
            <p class="text-xs text-slate-600">
                Customer / Payee: <strong>{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong> 
                (Unit: <strong>{{ $booking->unit_no }}</strong>, Block: {{ $booking->block_name ?? 'I' }})
            </p>
        </div>
        <div class="text-left sm:text-right text-xs space-y-1">
            <p class="text-slate-600"><strong>Booking Code :</strong> <span class="font-mono font-bold">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Disbursement Date :</strong> {{ date('d F Y') }}</p>
        </div>
    </div>

    @if($paymentMode === 'single' && $transaction)
        <!-- Single Payment / Refund Voucher -->
        <div class="border-2 border-slate-300 rounded-2xl overflow-hidden bg-white p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Voucher Nature</span>
                    <span class="text-sm font-black text-slate-900">
                        Payment & Refund Outflow Voucher ({{ ($transaction->payment_category ?? ($transaction->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable' ? 'Taxable Bank' : 'Non-Taxable Cash' }})
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Voucher No</span>
                    <span class="font-mono text-sm font-black text-rose-700">{{ $transaction->voucher_no ?: $transaction->transaction_code }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Date of Payment</span>
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
                    <span class="text-slate-400 block">Debited Account</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->bankAccount ? $transaction->bankAccount->account_nick_name : 'Cash in Hand' }}</span>
                </div>
            </div>

            <!-- Amount Banner -->
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-900 block">Disbursed Outflow Amount</span>
                    <span class="font-mono text-2xl font-black text-rose-950">₹{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="text-right text-xs italic font-bold text-rose-900 max-w-md">
                    {{ \App\Services\AutoNumberService::numberToIndianWords($transaction->amount) }}
                </div>
            </div>

            @if($transaction->particulars)
                <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <strong>Particulars / Purpose :</strong> {{ $transaction->particulars }}
                </div>
            @endif
        </div>
    @else
        <!-- Consolidated Payments Table -->
        <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
            <div class="bg-slate-900 text-white px-4 py-2.5 font-bold uppercase text-xs tracking-wider flex items-center justify-between">
                <span>Disbursements & Outflows Registry</span>
                <span class="font-mono text-rose-400">{{ $paymentTransactions->count() }} vouchers</span>
            </div>
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Voucher No</th>
                        <th class="py-2.5 px-3">Mode</th>
                        <th class="py-2.5 px-3">Instrument Ref</th>
                        <th class="py-2.5 px-3">Debited A/C</th>
                        <th class="py-2.5 px-3">Paid To</th>
                        <th class="py-2.5 px-4 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($paymentTransactions as $t)
                        @php
                            $isTaxable = ($t->payment_category ?? ($t->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable';
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2 px-3 whitespace-nowrap">{{ $t->voucher_date ? $t->voucher_date->format('d-m-Y') : '-' }}</td>
                            <td class="py-2 px-3 whitespace-nowrap">
                                @if($isTaxable)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Taxable Bank</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Non-Taxable</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $t->voucher_no ?: $t->transaction_code }}</td>
                            <td class="py-2 px-3 uppercase text-slate-700">{{ $t->transaction_mode }}</td>
                            <td class="py-2 px-3 font-mono text-slate-600">{{ $t->instrument_ref_no ?: 'N/A' }}</td>
                            <td class="py-2 px-3 text-slate-700">{{ $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash in Hand' }}</td>
                            <td class="py-2 px-3 text-slate-700">{{ $t->source_of_payment === 'through_loan_account' ? 'Loan A/C' : 'Direct Customer' }}</td>
                            <td class="py-2 px-4 text-right font-mono font-bold text-rose-700 whitespace-nowrap">
                                -₹{{ number_format($t->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No payment / refund vouchers found for this selection.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($paymentTransactions->isNotEmpty())
                    <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300 font-mono text-xs">
                        <tr>
                            <td colspan="7" class="py-2.5 px-4 font-sans text-slate-900 text-right">Total Outflow Disbursed :</td>
                            <td class="py-2.5 px-4 text-right text-rose-950 font-black text-sm">
                                -₹{{ number_format($paymentTransactions->sum('amount'), 2) }}
                            </td>
                        </tr>
                        <tr class="bg-slate-100 font-sans text-[11px]">
                            <td colspan="8" class="py-2 px-4 text-right italic text-slate-700">
                                {{ \App\Services\AutoNumberService::numberToIndianWords($paymentTransactions->sum('amount')) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    <!-- Notes & Terms -->
    <div class="text-xs text-slate-500 space-y-1 pt-2">
        <p>1. All payment disbursements are made through official banking/cash vouchers as acknowledged.</p>
        <p>2. Computer generated statement for records and audit.</p>
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
            <p class="text-[11px] text-slate-400">Authorized Signatory / Accounts</p>
        </div>
    </div>

</div>
@endsection
