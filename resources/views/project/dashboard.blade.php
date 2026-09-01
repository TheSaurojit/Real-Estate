@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Project Context Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-sky-800/40 relative overflow-hidden">
        
        <!-- Subtle background building silhouette decoration -->
        <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none text-9xl text-white transform translate-x-8 translate-y-6">
            <i class="fa-solid fa-city"></i>
        </div>

        <div class="relative z-10">
            
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                
                <div>
                    <div class="flex items-center space-x-2.5 mb-2">
                        <span class="px-2.5 py-1 rounded-lg bg-sky-500/20 text-sky-300 font-mono font-bold text-xs border border-sky-400/30">
                            {{ $project->project_code }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 font-bold text-xs border border-amber-400/30">
                            NICK: {{ $project->nick_name }}
                        </span>
                        @if($project->rera_category === 'registered')
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-semibold text-xs border border-emerald-400/30 flex items-center space-x-1">
                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                <span>RERA: {{ $project->rera_reg_no }}</span>
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-xs">
                                RERA: {{ ucfirst($project->rera_category) }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-white">
                        {{ $project->name }}
                    </h1>
                    
                    <p class="text-sm text-sky-200/90 mt-2 max-w-2xl leading-relaxed flex items-start space-x-2">
                        <i class="fa-solid fa-location-dot text-rose-400 mt-1 shrink-0"></i>
                        <span>{{ $project->full_address }}</span>
                    </p>
                </div>

                <!-- Land Details Pill Box -->
                <div class="bg-slate-800/80 backdrop-blur rounded-2xl p-4 border border-slate-700/80 text-xs space-y-2 shrink-0 min-w-[280px]">
                    <div class="font-bold text-sky-300 uppercase tracking-wider text-[11px] pb-1.5 border-b border-slate-700 flex items-center justify-between">
                        <span>Land Registry Particulars</span>
                        <i class="fa-solid fa-map-location-dot text-sky-400"></i>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 font-mono text-[11px]">
                        <div class="text-slate-400">DAG NO: <span class="text-white font-bold">{{ $project->daag_no ?? 'N/A' }}</span></div>
                        <div class="text-slate-400">PATTA NO: <span class="text-white font-bold">{{ $project->patta_no ?? 'N/A' }}</span></div>
                        <div class="text-slate-400">HOLDING: <span class="text-white font-bold">{{ $project->holding_no ?? 'N/A' }}</span></div>
                        <div class="text-slate-400">MOUZA: <span class="text-white font-medium">{{ $project->mouza ?? 'N/A' }}</span></div>
                    </div>
                    @if($project->pogonah)
                        <div class="text-slate-400 text-[11px]">POGONAH: <span class="text-white font-medium">{{ $project->pogonah }}</span></div>
                    @endif
                </div>

            </div>

            <!-- Linked Escrow / RERA Bank Accounts -->
            <div class="mt-6 pt-5 border-t border-slate-800/80 flex flex-wrap items-center gap-3 text-xs">
                <span class="font-semibold text-slate-400 flex items-center">
                    <i class="fa-solid fa-building-columns text-emerald-400 mr-1.5"></i>
                    Linked Project Accounts:
                </span>
                @forelse($projectBankAccounts as $pAcc)
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl bg-emerald-950/60 text-emerald-300 border border-emerald-700/50 font-medium">
                        <i class="fa-solid fa-shield-halved text-emerald-400 text-[10px]"></i>
                        <span>{{ $pAcc->account_nick_name }} ({{ $pAcc->bank_name }} - {{ $pAcc->account_number }})</span>
                    </span>
                @empty
                    <span class="text-slate-400 text-xs italic">
                        No dedicated project escrow account linked. Using general accounts.
                    </span>
                @endforelse
            </div>

        </div>

    </div>

    <!-- Project Workspace Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Module 1: Bookings Management -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl group-hover:bg-sky-600 group-hover:text-white transition">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 bg-sky-50 px-2 py-1 rounded-md">
                        Section 2.2
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Bookings & Property Units</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Book flats on the fly without static inventory constraints. Configure blocks, floors, units, area, rate, parking, and customer info.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-sky-500 text-[10px]"></i>
                        <span>Flexible Flat/Unit Booking</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-sky-500 text-[10px]"></i>
                        <span>Sale Agreement & Bank Loan Tracker</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-sky-500 text-[10px]"></i>
                        <span>Sale Deed & Registry Records</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-sky-500 text-[10px]"></i>
                        <span>Booking Cancellation & Refund Splits</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Phase 2 Engine</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-sky-600 group-hover:translate-x-1 transition">
                    <span>Manage Bookings</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </div>

        <!-- Module 2: Booking Customization (Job Sheets) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-1 rounded-md">
                        Section 2.2.4
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Customization Job Sheets</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Track custom flat alterations beyond builder specifications: Add-on work (material + labor) and Dislodge/Deductions with adjusted supplementary value.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-amber-500 text-[10px]"></i>
                        <span>Add-on (AC points, Electrical points)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-amber-500 text-[10px]"></i>
                        <span>Dislodge (Client tiles deduction + labor)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-amber-500 text-[10px]"></i>
                        <span>Schedule Rates & Quantity Calculation</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Job Sheet Rollup</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-amber-600 group-hover:translate-x-1 transition">
                    <span>Job Sheets</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </div>

        <!-- Module 3: Dual-Ledger Transactions -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                        Section 2.3
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Financial Transactions</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Dual accountability engine: Money Receipts (Taxable Bank/GST) and Receipt Vouchers (Cash), Payment Adjustments, and Bounced Cheques.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                        <span>Money Receipts (Taxable Banking)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                        <span>Receipt Vouchers (Cash Accounts)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                        <span>Cross-Ledger Payment Adjustments</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                        <span>Dishonored Cheque Penalty Tracker</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Dual Accountability</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-emerald-600 group-hover:translate-x-1 transition">
                    <span>Transactions</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </div>

        <!-- Module 4: View Reports -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl group-hover:bg-violet-600 group-hover:text-white transition">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-1 rounded-md">
                        Section 2.4
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Reports & Accountability</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Real-time analytics: General & Financial Booking reports, Collections (Bank vs Cash), Refunds, Banking instruments, and Defaulters.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-violet-500 text-[10px]"></i>
                        <span>Active vs Cancelled Bookings</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-violet-500 text-[10px]"></i>
                        <span>Taxable vs Cash Financial Split</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-violet-500 text-[10px]"></i>
                        <span>Banking Instrument Clearance Pipeline</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Live Statements</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-violet-600 group-hover:translate-x-1 transition">
                    <span>View Reports</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </div>

        <!-- Module 5: Print Documents -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl group-hover:bg-rose-600 group-hover:text-white transition">
                        <i class="fa-solid fa-print"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-1 rounded-md">
                        Section 2.5
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Print Documents & Letters</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    12 print-ready corporate document templates with company header, logo, and digital signature stamps.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-rose-500 text-[10px]"></i>
                        <span>Confirmation, Allotment & Demand Letters</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-rose-500 text-[10px]"></i>
                        <span>Handover, Possession & NOC Certificates</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-rose-500 text-[10px]"></i>
                        <span>Money Receipts, Cash Vouchers & Invoices</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">PDF / Print Ready</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-rose-600 group-hover:translate-x-1 transition">
                    <span>Print Documents</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </div>

        <!-- Module 6: Track Expense -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl group-hover:bg-cyan-600 group-hover:text-white transition">
                        <i class="fa-solid fa-cart-flatbed"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-600 bg-cyan-50 px-2 py-1 rounded-md">
                        Section 2.6
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Track Construction Expense</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Site procurement, raw materials purchases, contractor payments, inter-site stock transfers, and supplier ledger.
                </p>
                <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-cyan-500 text-[10px]"></i>
                        <span>Material Purchases & Labor Bills</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-cyan-500 text-[10px]"></i>
                        <span>Site-to-Site Stock Transfers</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-cyan-500 text-[10px]"></i>
                        <span>Supplier & Contractor Ledgers</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Site Management</span>
                <span class="inline-flex items-center space-x-1 text-xs font-bold text-cyan-600 group-hover:translate-x-1 transition">
                    <span>Track Expenses</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            </div>
        </div>

    </div>

</div>
@endsection
