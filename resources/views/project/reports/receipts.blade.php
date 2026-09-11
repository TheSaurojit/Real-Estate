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
                <span class="text-indigo-600 font-bold">2.3.2 Receipt Report</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dual-Ledger Receipt Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">Itemized registry of customer inflows across Money Receipts (Taxable Banking) and Receipt Vouchers (Cash).</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Export to Excel Button -->
            <a href="{{ route('project.reports.receipts', array_merge(request()->query(), ['project' => $project->id, 'export' => 'excel'])) }}"
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
        <form action="{{ route('project.reports.receipts', $project->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tx Code, Voucher No, Customer, Ref..."
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Voucher Category -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Category</label>
                <select name="voucher_type" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    <option value="">All Categories</option>
                    <option value="money_receipt" {{ request('voucher_type') === 'money_receipt' ? 'selected' : '' }}>Money Receipt (Bank)</option>
                    <option value="receipt_voucher" {{ request('voucher_type') === 'receipt_voucher' ? 'selected' : '' }}>Receipt Voucher (Cash)</option>
                </select>
            </div>

            <!-- Instrument Status -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
                <select name="instrument_status" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    <option value="">All Statuses</option>
                    <option value="cleared" {{ request('instrument_status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                    <option value="pending_clearance" {{ request('instrument_status') === 'pending_clearance' ? 'selected' : '' }}>Pending Clearance</option>
                    <option value="dishonored" {{ request('instrument_status') === 'dishonored' ? 'selected' : '' }}>Dishonored / Bounced</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'voucher_type', 'instrument_status', 'from_date', 'to_date']))
                    <a href="{{ route('project.reports.receipts', $project->id) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Receipts Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-receipt text-indigo-600 text-sm"></i>
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">Collections & Receipts Registry</h3>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Total Records: <strong class="text-slate-800 font-mono">{{ $receipts->total() }}</strong>
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
                        <th class="py-3 px-4 text-right">Amount (₹)</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4">Instrument Details</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Fine Imposed</th>
                        <th class="py-3 px-4">Credited A/C</th>
                        <th class="py-3 px-4">Remarks (Loan/Direct)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $t)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Transaction ID -->
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">
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
                                @if($t->voucher_type === 'money_receipt')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Money Receipt (Bank)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Receipt Voucher (Cash)
                                    </span>
                                @endif
                            </td>

                            <!-- Voucher No -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $t->voucher_no ?: '-' }}
                            </td>

                            <!-- Customer Name -->
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $t->booking?->customer_name ?? 'N/A' }}
                            </td>

                            <!-- Unit Info -->
                            <td class="py-3 px-4">
                                @if($t->booking)
                                    {{ $t->booking->block_name }} / {{ $t->booking->floor_no }} / <span class="font-bold text-indigo-600 font-mono">{{ $t->booking->unit_no }}</span>
                                @else
                                    <span class="text-slate-400 italic">Direct Receipt</span>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-3 px-4 text-right font-mono font-black text-slate-900">
                                ₹{{ number_format($t->amount, 2) }}
                            </td>

                            <!-- Mode -->
                            <td class="py-3 px-4 uppercase font-bold text-[11px] text-slate-700">
                                {{ $t->transaction_mode }}
                            </td>

                            <!-- Instrument Details -->
                            <td class="py-3 px-4 font-mono">
                                @if($t->instrument_ref_no)
                                    <div>Ref: {{ $t->instrument_ref_no }}</div>
                                    @if($t->instrument_date)
                                        <div class="text-[10px] text-slate-400">Dt: {{ $t->instrument_date->format('d/m/Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if($t->instrument_status === 'cleared')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">CLEARED</span>
                                @elseif($t->instrument_status === 'pending_clearance')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">PENDING</span>
                                @elseif($t->instrument_status === 'dishonored')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">BOUNCED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ strtoupper($t->instrument_status) }}</span>
                                @endif
                            </td>

                            <!-- Fine Imposed -->
                            <td class="py-3 px-4 text-right font-mono">
                                @if($t->dishonor_penalty_amount > 0)
                                    <span class="text-rose-600 font-bold">₹{{ number_format($t->dishonor_penalty_amount, 2) }}</span>
                                @else
                                    <span class="text-slate-400">₹0.00</span>
                                @endif
                            </td>

                            <!-- Credited A/C -->
                            <td class="py-3 px-4">
                                {{ $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash in Hand Account' }}
                            </td>

                            <!-- Remarks -->
                            <td class="py-3 px-4">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $t->source_of_payment === 'through_loan_account' ? 'bg-teal-50 text-teal-800 border border-teal-200' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $t->source_of_payment === 'through_loan_account' ? 'Loan A/C' : 'Direct (Self)' }}
                                </span>
                                @if($t->particulars)
                                    <div class="text-[10px] text-slate-400 mt-0.5 truncate max-w-xs" title="{{ $t->particulars }}">{{ $t->particulars }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-12 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
                                No receipt transactions found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
