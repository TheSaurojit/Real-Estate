@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.reports.index', $project->id) }}" class="hover:text-sky-600">Reports</a>
                <span>/</span>
                <span class="text-rose-600 font-bold">2.3.3 Refund / Payment Report</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Refund & Payment Outflow Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">Itemized outward disbursements: cancellation refunds, loan settlement reimbursements, and cross-ledger payouts.</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Export to Excel Button -->
            <a href="{{ route('project.reports.refunds', array_merge(request()->query(), ['project' => $project->id, 'export' => 'excel'])) }}"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export to Excel</span>
            </a>
            <a href="{{ route('project.reports.index', $project->id) }}"
               class="px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Reports Hub</span>
            </a>
        </div>
    </div>

    <!-- Keyword & Parameter Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('project.reports.refunds', $project->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tx Code, Voucher No, Customer, Unit, Ref..."
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Payment Category</label>
                <select name="payment_category" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                    <option value="">All Categories</option>
                    <option value="taxable" {{ request('payment_category') === 'taxable' ? 'selected' : '' }}>Taxable Outflow (Bank/GST)</option>
                    <option value="non_taxable" {{ request('payment_category') === 'non_taxable' ? 'selected' : '' }}>Non-Taxable Outflow (Cash)</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'payment_category', 'from_date', 'to_date']))
                    <a href="{{ route('project.reports.refunds', $project->id) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Refunds Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-hand-holding-dollar text-rose-600 text-sm"></i>
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">Payment Outflows & Refund Registry</h3>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Total Records: <strong class="text-slate-800 font-mono">{{ $refunds->total() }}</strong>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="text-[11px] font-bold uppercase text-slate-600 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Transaction ID</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Voucher No</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Block - Floor - Unit</th>
                        <th class="py-3 px-4 text-right">Refund Amount (₹)</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4">Instrument Details</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Debited A/C</th>
                        <th class="py-3 px-4">Paid To</th>
                        <th class="py-3 px-4">Particular / Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($refunds as $t)
                        @php
                            $isTaxable = ($t->payment_category ?? ($t->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable';
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Transaction ID -->
                            <td class="py-3 px-4 font-mono font-bold text-rose-700">
                                <a href="{{ route('project.transactions.show', [$project->id, $t->id]) }}" class="hover:underline">
                                    {{ $t->transaction_code }}
                                </a>
                            </td>

                            <!-- Date -->
                            <td class="py-3 px-4 text-slate-700">
                                {{ $t->voucher_date ? $t->voucher_date->format('d M Y') : 'N/A' }}
                            </td>

                            <!-- Category -->
                            <td class="py-3 px-4">
                                @if($isTaxable)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Taxable Outflow
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Non-Taxable Outflow
                                    </span>
                                @endif
                            </td>

                            <!-- Voucher No -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $t->voucher_no ?: '-' }}
                            </td>

                            <!-- Customer Name -->
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $t->booking?->customer_name ?? 'Direct Refund' }}
                            </td>

                            <!-- Unit Info -->
                            <td class="py-3 px-4">
                                @if($t->booking)
                                    {{ $t->booking->block_name }} / {{ $t->booking->floor_no }} / <span class="font-bold text-indigo-600 font-mono">{{ $t->booking->unit_no }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-3 px-4 text-right font-mono font-black text-rose-600">
                                -₹{{ number_format($t->amount, 2) }}
                            </td>

                            <!-- Mode -->
                            <td class="py-3 px-4 uppercase font-bold text-[11px] text-slate-700">
                                {{ $t->transaction_mode }}
                            </td>

                            <!-- Instrument Details -->
                            <td class="py-3 px-4 font-mono">
                                {{ $t->instrument_ref_no ?: '-' }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ strtoupper($t->instrument_status) }}
                                </span>
                            </td>

                            <!-- Debited A/C -->
                            <td class="py-3 px-4">
                                {{ $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash in Hand Account' }}
                            </td>

                            <!-- Paid To -->
                            <td class="py-3 px-4">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $t->source_of_payment === 'through_loan_account' ? 'bg-teal-50 text-teal-800 border border-teal-200' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $t->source_of_payment === 'through_loan_account' ? 'Lending Bank Loan A/C' : 'Direct Customer' }}
                                </span>
                            </td>

                            <!-- Remarks -->
                            <td class="py-3 px-4">
                                <div class="text-[11px] text-slate-600 truncate max-w-sm" title="{{ $t->particulars }}">
                                    {{ $t->particulars ?: 'Refund Outflow' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-12 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-hand-holding-dollar text-3xl mb-2 text-slate-300 block"></i>
                                No refund / payment transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($refunds->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $refunds->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
