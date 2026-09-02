@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-indigo-600">Executive Reports</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Executive Business Intelligence & Financial Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5">Project profitability, unit sales velocity, customer dues aging matrix, and bank escrow reconciliation.</p>
        </div>
    </div>

    <!-- Reports Hub Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Report 1: Project Profitability -->
        <a href="{{ route('project.reports.profitability', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md hover:border-emerald-300 transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                        Financial Statement
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-emerald-600 transition">Project Profitability & Cost vs Revenue</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Compare projected contract revenue, realized banking & cash collections against construction material purchases, labor contractor bills, and net stock transfers.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3 font-medium">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-500 text-[10px]"></i><span>Gross Profit & Net Margin Percentage</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-500 text-[10px]"></i><span>Material vs Labor vs Overheads Breakdown</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-500 text-[10px]"></i><span>Realized Operating Cash Surplus</span></div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Statement 1</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-emerald-600 group-hover:translate-x-1 transition">
                    <span>View Statement</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </a>

        <!-- Report 2: Unit Sales & Inventory Velocity -->
        <a href="{{ route('project.reports.inventory', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md hover:border-sky-300 transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl group-hover:bg-sky-600 group-hover:text-white transition">
                        <i class="fa-solid fa-cubes-stacked"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 bg-sky-50 px-2 py-1 rounded-md">
                        Inventory Analytics
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-sky-600 transition">Unit Sales & Area Velocity Report</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Track unit allotment progress across Live, Agreement Executed, Deed Registered, and Cancelled units with average realization rate per square foot.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3 font-medium">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-sky-500 text-[10px]"></i><span>Super Built-up Area Sold vs Remaining</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-sky-500 text-[10px]"></i><span>Average Selling Rate / Sq. Ft. Realization</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-sky-500 text-[10px]"></i><span>Stage-wise Unit Lifecycle Distribution</span></div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Statement 2</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-sky-600 group-hover:translate-x-1 transition">
                    <span>View Velocity</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </a>

        <!-- Report 3: Customer Dues & Aging Analysis -->
        <a href="{{ route('project.reports.aging', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md hover:border-amber-300 transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-1 rounded-md">
                        Receivables & Recovery
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-amber-600 transition">Customer Dues & Aging Analysis</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Classify outstanding client dues into aging buckets (< 30 days, 30-60 days, 60-90 days, > 90 days) with dedicated defaulters & bounced cheques tracking.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3 font-medium">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-amber-500 text-[10px]"></i><span>Aging Buckets Matrix</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-amber-500 text-[10px]"></i><span>Taxable Bank vs Non-Taxable Cash Due Split</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-amber-500 text-[10px]"></i><span>Dishonored Cheques & Penalties Tracker</span></div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Statement 3</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-amber-600 group-hover:translate-x-1 transition">
                    <span>View Aging Matrix</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </a>

        <!-- Report 4: Bank Escrow & Cash Reconciliation -->
        <a href="{{ route('project.reports.banking', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md hover:border-purple-300 transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl group-hover:bg-purple-600 group-hover:text-white transition">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-1 rounded-md">
                        Cash & Escrow Flows
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-purple-600 transition">Banking Escrow & Cash Reconciliation</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Track total customer inflows, expense debits, and cancellation refunds across all project bank accounts and the cash-in-hand ledger.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3 font-medium">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-purple-500 text-[10px]"></i><span>Escrow Account Inflow/Outflow Health</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-purple-500 text-[10px]"></i><span>Cash Account Balance Reconciliation</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-check text-purple-500 text-[10px]"></i><span>Net Liquidity & Cash Flow Balance</span></div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Statement 4</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-purple-600 group-hover:translate-x-1 transition">
                    <span>View Banking Report</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </a>

    </div>

</div>
@endsection
