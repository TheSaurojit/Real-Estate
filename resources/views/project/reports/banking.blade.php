@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.reports.index', $project->id) }}" class="hover:text-sky-600">Reports</a>
                <span>/</span>
                <span class="text-purple-600">Banking Reconciliation</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Banking Escrow & Cash Account Flows</h1>
            <p class="text-xs text-slate-500 mt-0.5">Account-by-account inflow collections, expense disbursements, and net cash balances.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Reconciliation</span>
            </button>
            <a href="{{ route('project.reports.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Bank Accounts Summary Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-3.5 border-b border-slate-200 font-bold uppercase text-xs text-slate-700 tracking-wider">
            Bank Accounts Flow & Escrow Summary
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Account Nick Name & Bank</th>
                        <th class="py-3.5 px-4">Account Number & Type</th>
                        <th class="py-3.5 px-4 text-right">Inflows (₹)</th>
                        <th class="py-3.5 px-4 text-right">Expense Outflows (₹)</th>
                        <th class="py-3.5 px-4 text-right">Refund Outflows (₹)</th>
                        <th class="py-3.5 px-4 text-right">Net Flow (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($accountSummaries as $row)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-sans font-bold text-slate-900">
                                {{ $row['account']->account_nick_name }}
                                <div class="text-[11px] text-slate-400 font-normal">{{ $row['account']->bank_name }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ $row['account']->account_number }}
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-sans font-bold uppercase bg-slate-100 text-slate-600 ml-1">
                                    {{ $row['account']->account_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-700 font-bold">
                                +₹{{ number_format($row['inflows'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-rose-600 font-bold">
                                -₹{{ number_format($row['expense_outflows'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-rose-600 font-bold">
                                -₹{{ number_format($row['refund_outflows'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-base font-black {{ $row['net_flow'] >= 0 ? 'text-slate-900' : 'text-rose-700' }}">
                                {{ $row['net_flow'] >= 0 ? '+' : '' }}₹{{ number_format($row['net_flow'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-sans">No bank accounts configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cash Ledger Summary Card -->
    <div class="bg-gradient-to-br from-slate-900 via-amber-950 to-slate-900 text-white p-6 rounded-2xl shadow-md border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-700 pb-2">
            <span class="text-xs uppercase font-bold text-amber-400 tracking-wider">Cash-in-Hand / Cash Account Ledger Flow</span>
            <span class="text-xs font-mono font-bold bg-amber-900/60 px-2 py-0.5 rounded text-amber-200">Non-Taxable Stream</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 font-mono text-xs">
            <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700">
                <span class="text-[10px] text-slate-400 font-sans uppercase block">Cash Collections</span>
                <span class="text-base font-bold text-emerald-400 mt-1 block">+₹{{ number_format($cashInflows, 2) }}</span>
            </div>

            <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700">
                <span class="text-[10px] text-slate-400 font-sans uppercase block">Cash Site Expenses</span>
                <span class="text-base font-bold text-rose-400 mt-1 block">-₹{{ number_format($cashExpenses, 2) }}</span>
            </div>

            <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700">
                <span class="text-[10px] text-slate-400 font-sans uppercase block">Cash Cancellation Refunds</span>
                <span class="text-base font-bold text-rose-400 mt-1 block">-₹{{ number_format($cashRefunds, 2) }}</span>
            </div>

            <div class="bg-amber-950/80 p-3 rounded-xl border border-amber-800 text-right">
                <span class="text-[10px] text-amber-300 font-sans uppercase block font-bold">Net Cash Balance</span>
                <span class="text-lg font-black text-amber-300 mt-1 block">{{ $netCash >= 0 ? '+' : '' }}₹{{ number_format($netCash, 2) }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
