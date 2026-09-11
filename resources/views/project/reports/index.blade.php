@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-indigo-600">Reporting & BI Hub</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Executive Business Intelligence & Financial Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive registries, dual-ledger audit statements, itemized engineering job sheets, and instant Excel exports.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('project.reports.quick-summary', $project->id) }}" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Quick Summary (360° Booking View)</span>
            </a>
        </div>
    </div>

    <!-- 1. SECTION 2.3 CORE BUSINESS & AUDIT REPORTS -->
    <div class="space-y-4">
        <div class="flex items-center space-x-3 border-b border-slate-200 pb-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full"></span>
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wider">
                Section 2.3: Operational & Financial Audit Reports
            </h2>
            <span class="text-xs text-slate-400 font-normal">Excel Export Ready • Multi-keyword Searchable</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- 2.3.1.1 Booking General Information Report -->
            <a href="{{ route('project.reports.bookings.general', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-sky-300 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg group-hover:bg-sky-600 group-hover:text-white transition">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md">
                            Report 2.3.1.1
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-sky-600 transition">Booking General Information</h3>
                    <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                        Complete registry of customer KYC (PAN, GSTIN), unit & parking allocations, category (Landowner/Purchaser), and Agreement, Loan, and Deed statuses.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">26 Data Attributes</span>
                    <span class="text-sky-600 group-hover:translate-x-1 transition flex items-center space-x-1">
                        <span>View & Export</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- 2.3.1.2 Booking Financial Breakdown Report -->
            <a href="{{ route('project.reports.bookings.financial', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-emerald-300 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:bg-emerald-600 group-hover:text-white transition">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                            Report 2.3.1.2
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-emerald-600 transition">Booking Financial Breakdown</h3>
                    <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                        Dual-ledger calculations $(a)$ to $(L)$: consideration, addons/dislodges, supplement charges, GST, Bank Finance, Self-Taxable, and Cash streams (Live vs Cancelled).
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">Live & Cancelled Tabs</span>
                    <span class="text-emerald-600 group-hover:translate-x-1 transition flex items-center space-x-1">
                        <span>View & Export</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- 2.3.2 Receipt Report -->
            <a href="{{ route('project.reports.receipts', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg group-hover:bg-indigo-600 group-hover:text-white transition">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                            Report 2.3.2
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition">Dual-Ledger Receipt Report</h3>
                    <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                        Itemized money receipts (Taxable Banking) and receipt vouchers (Non-Taxable Cash), voucher numbers, clearance status, bounce penalties, and credited bank accounts.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">Cash & Bank Inflows</span>
                    <span class="text-indigo-600 group-hover:translate-x-1 transition flex items-center space-x-1">
                        <span>View & Export</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- 2.3.3 Refund / Payment Report -->
            <a href="{{ route('project.reports.refunds', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-rose-300 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg group-hover:bg-rose-600 group-hover:text-white transition">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                            Report 2.3.3
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-rose-600 transition">Refund & Payment Outflow Report</h3>
                    <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                        Taxable and non-taxable refund vouchers, stage 2.1.6 cancellation payouts, loan reimbursements, debited bank accounts, and payment adjustment remarks.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">Outflows & Reimbursements</span>
                    <span class="text-rose-600 group-hover:translate-x-1 transition flex items-center space-x-1">
                        <span>View & Export</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- 2.3.5 Booking Customization Report -->
            <a href="{{ route('project.reports.customizations', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-amber-300 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg group-hover:bg-amber-600 group-hover:text-white transition">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                            Report 2.3.5
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-amber-600 transition">Booking Customization Report</h3>
                    <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                        Itemized engineering job sheets searchable by particular (e.g. Electrical works, Civil, Plumbing), detailing material rates, labor rates, quantities, and totals.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">Job Sheet Breakdown</span>
                    <span class="text-amber-600 group-hover:translate-x-1 transition flex items-center space-x-1">
                        <span>View & Export</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- 2.3.6 Quick Summary - Booking -->
            <a href="{{ route('project.reports.quick-summary', $project->id) }}" class="bg-gradient-to-br from-slate-900 to-sky-950 text-white rounded-2xl border border-slate-800 p-5 shadow-md hover:shadow-xl transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 text-sky-400 flex items-center justify-center text-lg group-hover:bg-sky-500 group-hover:text-white transition">
                            <i class="fa-solid fa-gauge-high"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sky-300 bg-sky-900/60 px-2 py-0.5 rounded-md border border-sky-700">
                            Report 2.3.6
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1 group-hover:text-sky-300 transition">Quick Summary – Booking</h3>
                    <p class="text-xs text-slate-300 mb-3 leading-relaxed">
                        Single-click 360° financial dashboard. Inspect any flat booking's live parameters, multi-stream receipts, and 4-stream reconciliation table.
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-400">Interactive Single-Click UI</span>
                    <span class="text-sky-400 group-hover:translate-x-1 transition flex items-center space-x-1">
                        <span>Open Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

        </div>
    </div>

    <!-- 2. EXECUTIVE BI STATEMENTS -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center space-x-3 border-b border-slate-200 pb-2">
            <span class="w-2.5 h-6 bg-slate-700 rounded-full"></span>
            <h2 class="text-base font-bold text-slate-800 uppercase tracking-wider">
                Executive BI & Statutory Statements
            </h2>
            <span class="text-xs text-slate-400 font-normal">Macro Analytics • Profit Margins • Aging Matrix</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Profitability -->
            <a href="{{ route('project.reports.profitability', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-emerald-300 transition group flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base mb-3 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-emerald-600 transition">Project Profitability</h3>
                    <p class="text-xs text-slate-500 mb-2">Cost vs revenue analysis across materials, labor, and overheads.</p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-medium">
                    <span>Statement 1</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-emerald-600"></i>
                </div>
            </a>

            <!-- Inventory Velocity -->
            <a href="{{ route('project.reports.inventory', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-sky-300 transition group flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base mb-3 group-hover:bg-sky-600 group-hover:text-white transition">
                        <i class="fa-solid fa-cubes-stacked"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-sky-600 transition">Inventory Analytics & Velocity</h3>
                    <p class="text-xs text-slate-500 mb-2">Square footage sold, agreement pipeline, and deed execution rates.</p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-medium">
                    <span>Statement 2</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-sky-600"></i>
                </div>
            </a>

            <!-- Aging Analysis -->
            <a href="{{ route('project.reports.aging', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-amber-300 transition group flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base mb-3 group-hover:bg-amber-600 group-hover:text-white transition">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-amber-600 transition">Customer Dues Aging</h3>
                    <p class="text-xs text-slate-500 mb-2">Overdue receivables bucketed by 30/60/90+ days and dishonored cheques.</p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-medium">
                    <span>Statement 3</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-amber-600"></i>
                </div>
            </a>

            <!-- Banking & Escrow -->
            <a href="{{ route('project.reports.banking', $project->id) }}" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-teal-300 transition group flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base mb-3 group-hover:bg-teal-600 group-hover:text-white transition">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-teal-600 transition">Escrow & Cash Flows</h3>
                    <p class="text-xs text-slate-500 mb-2">Project bank accounts reconciliation and cash in hand ledger balances.</p>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-medium">
                    <span>Statement 4</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-teal-600"></i>
                </div>
            </a>

        </div>
    </div>

</div>
@endsection
