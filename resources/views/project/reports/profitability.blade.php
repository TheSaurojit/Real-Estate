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
                <span class="text-emerald-600">Profitability</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Project Profitability & Cost vs Revenue Analysis</h1>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive financial statement comparing projected & realized revenues against construction costs.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Statement</span>
            </button>
            <a href="{{ route('project.reports.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Profitability KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
        
        <!-- Total Projected Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-sky-600 tracking-wider font-sans block">Total Projected Revenue</span>
            <h3 class="text-xl font-bold text-slate-900 mt-1">₹{{ number_format($projectedRevenue, 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Sum of active contract values
            </div>
        </div>

        <!-- Total Construction Expenses -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-rose-600 tracking-wider font-sans block">Total Direct Cost Incurred</span>
            <h3 class="text-xl font-bold text-rose-700 mt-1">₹{{ number_format($totalDirectExpenses, 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Materials + Labor + Overheads
            </div>
        </div>

        <!-- Projected Gross Profit -->
        <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-emerald-700 tracking-wider font-sans block">Projected Gross Profit</span>
            <h3 class="text-2xl font-black text-emerald-800 mt-1">₹{{ number_format($projectedGrossProfit, 2) }}</h3>
            <div class="mt-2 text-[11px] text-emerald-600 font-sans font-bold">
                Margin: {{ $projectedProfitMargin }}%
            </div>
        </div>

        <!-- Realized Net Cash Inflow -->
        <div class="bg-slate-900 text-white p-5 rounded-2xl shadow-md border border-slate-800 flex flex-col justify-between font-sans">
            <div>
                <span class="text-[10px] uppercase font-bold text-cyan-400 tracking-wider block">Realized Operating Cash Surplus</span>
                <h3 class="text-xl font-black font-mono mt-1 text-white">
                    {{ $realizedCashFlowNet >= 0 ? '+' : '' }}₹{{ number_format($realizedCashFlowNet, 2) }}
                </h3>
            </div>
            <div class="text-[11px] text-cyan-200 mt-2 font-mono">
                Realized Collections - Total Expenses
            </div>
        </div>

    </div>

    <!-- Itemized Statement Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-slate-50 px-6 py-3.5 border-b border-slate-200 font-bold uppercase text-xs text-slate-700 tracking-wider">
            Statement of Cost, Revenue & Profitability Breakdown
        </div>

        <table class="w-full text-left text-sm text-slate-600">
            <tbody class="divide-y divide-slate-100 font-mono text-xs">
                
                <!-- SECTION 1: REVENUE -->
                <tr class="bg-sky-50/50 font-bold font-sans text-sky-900 text-xs uppercase">
                    <td colspan="2" class="py-2.5 px-6">1. Revenue & Inflows Analysis</td>
                </tr>
                <tr>
                    <td class="py-3 px-6 font-sans text-slate-700">Gross Contract Value from Active Bookings (Consideration + GST + Job Sheets)</td>
                    <td class="py-3 px-6 text-right font-bold text-slate-900">₹{{ number_format($projectedRevenue, 2) }}</td>
                </tr>
                <tr class="text-emerald-700">
                    <td class="py-2.5 px-6 font-sans pl-10">↳ Realized Banking Inflows (Cleared Money Receipts into Escrow)</td>
                    <td class="py-2.5 px-6 text-right font-bold">₹{{ number_format($realizedBank, 2) }}</td>
                </tr>
                <tr class="text-amber-700">
                    <td class="py-2.5 px-6 font-sans pl-10">↳ Realized Cash Inflows (Cleared Receipt Vouchers into Cash Accounts)</td>
                    <td class="py-2.5 px-6 text-right font-bold">₹{{ number_format($realizedCash, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold font-sans">
                    <td class="py-2.5 px-6 text-slate-800">Total Realized Collections to Date</td>
                    <td class="py-2.5 px-6 text-right text-slate-900 font-mono">₹{{ number_format($totalRealizedInflow, 2) }}</td>
                </tr>

                <!-- SECTION 2: CONSTRUCTION DIRECT COSTS -->
                <tr class="bg-rose-50/50 font-bold font-sans text-rose-900 text-xs uppercase">
                    <td colspan="2" class="py-2.5 px-6">2. Construction Costs & Direct Expenditures</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-6 font-sans pl-10">Raw Material Purchases (Cement, Steel, Bricks, Aggregates, Sand)</td>
                    <td class="py-2.5 px-6 text-right text-rose-700 font-bold">₹{{ number_format($materialCost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-6 font-sans pl-10">Labor & Subcontractor Payouts (Civil Masonry, Shuttering, Plumbing)</td>
                    <td class="py-2.5 px-6 text-right text-rose-700 font-bold">₹{{ number_format($laborCost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-6 font-sans pl-10">Site Overheads, Machinery Rental & Municipal Fees</td>
                    <td class="py-2.5 px-6 text-right text-rose-700 font-bold">₹{{ number_format($overheadCost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-6 font-sans pl-10">Inter-Project Stock Transfers Net Cost (Inward ₹{{ number_format($inwardStock, 2) }} - Outward ₹{{ number_format($outwardStock, 2) }})</td>
                    <td class="py-2.5 px-6 text-right text-slate-700 font-bold">{{ $netStockImpact >= 0 ? '+' : '' }}₹{{ number_format($netStockImpact, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold font-sans">
                    <td class="py-2.5 px-6 text-slate-800">Total Construction & Direct Expenses Incurred</td>
                    <td class="py-2.5 px-6 text-right text-rose-700 font-mono">₹{{ number_format($totalDirectExpenses, 2) }}</td>
                </tr>

                <!-- SECTION 3: PROFITABILITY -->
                <tr class="bg-slate-900 text-white font-bold font-sans text-sm">
                    <td class="py-3.5 px-6 uppercase text-emerald-300">
                        Projected Project Gross Profit (Contract Value - Total Expenses)
                    </td>
                    <td class="py-3.5 px-6 text-right font-mono text-base text-emerald-400 font-black">
                        ₹{{ number_format($projectedGrossProfit, 2) }} ({{ $projectedProfitMargin }}%)
                    </td>
                </tr>

            </tbody>
        </table>

    </div>

</div>
@endsection
