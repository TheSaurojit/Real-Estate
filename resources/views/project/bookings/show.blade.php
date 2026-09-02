@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'jobsheets',
    showCancelModal: false,
    showAddCustomizationModal: false,
    showAgreementModal: false,
    showLoanModal: false,
    showDeedModal: false,

    // Addon calculation modal state
    jobType: 'addon',
    matRate: 0,
    labRate: 0,
    schRate: 0,
    qty: 1,
    get calculatedJobTotal() {
        return Math.round(((parseFloat(this.matRate || 0) + parseFloat(this.labRate || 0) + parseFloat(this.schRate || 0)) * parseFloat(this.qty || 1)) * 100) / 100;
    }
}">

    <!-- Top Breadcrumbs & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.bookings.index', $project->id) }}" class="hover:text-sky-600">Bookings</a>
                <span>/</span>
                <span class="text-sky-600">{{ $booking->booking_code }}</span>
            </nav>
            <div class="flex items-center space-x-3 mt-1">
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                    {{ $booking->customer_salutation }} {{ $booking->customer_name }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                    Unit: {{ $booking->unit_no }}
                </span>
                @if($booking->status === 'live')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600 inline-block mr-1"></span> Live Booking
                    </span>
                @elseif($booking->status === 'executed_agreement')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="fa-solid fa-file-contract mr-1"></i> Agreement Executed
                    </span>
                @elseif($booking->status === 'registered_deed')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                        <i class="fa-solid fa-certificate mr-1"></i> Deed Registered
                    </span>
                @elseif($booking->status === 'cancelled')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <i class="fa-solid fa-ban mr-1"></i> Cancelled
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Booking Date: <span class="font-medium text-slate-700">{{ $booking->booking_date->format('d M, Y') }}</span> |
                Phone: <span class="font-medium text-slate-700">{{ $booking->mobile_no }}</span> |
                Project: <span class="font-medium text-slate-700">{{ $project->name }} ({{ $project->project_code }})</span>
            </p>
        </div>

        <!-- Top Action Buttons -->
        <div class="flex items-center space-x-2" x-data="{ printMenuOpen: false }">
            <a href="{{ route('project.transactions.create', ['project' => $project->id, 'booking_id' => $booking->id]) }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Collect Payment</span>
            </a>

            <!-- Print Documents Dropdown -->
            <div class="relative">
                <button @click="printMenuOpen = !printMenuOpen" type="button" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Documents</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                </button>
                <div x-show="printMenuOpen" @click.away="printMenuOpen = false" class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 text-xs" style="display: none;">
                    <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        12 Print-Ready Templates
                    </div>
                    @foreach(\App\Http\Controllers\Project\DocumentController::DOCUMENT_TYPES as $typeKey => $typeInfo)
                        <a href="{{ route('project.documents.show', [$project->id, $booking->id, $typeKey]) }}" target="_blank"
                           class="flex items-center space-x-2 px-3 py-2 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition">
                            <i class="fa-solid {{ $typeInfo['icon'] }} w-4 text-slate-400"></i>
                            <span class="font-medium truncate">{{ $typeInfo['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($booking->total_taxable_received > $booking->gross_taxable_value)
                <a href="{{ route('project.bookings.adjustment.create', [$project->id, $booking->id]) }}" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1.5 animate-pulse">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Rebalance Overpayment</span>
                </a>
            @endif

            @if($booking->status === 'cancelled')
                <a href="{{ route('project.bookings.cancellation-refund.show', [$project->id, $booking->id]) }}" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Refund Settlement</span>
                </a>
            @endif

            <a href="{{ route('project.bookings.edit', [$project->id, $booking->id]) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1.5">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Specs</span>
            </a>

            @if($booking->status !== 'cancelled')
                <button @click="showCancelModal = true" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-semibold transition border border-rose-200 flex items-center space-x-1.5">
                    <i class="fa-solid fa-ban"></i>
                    <span>Cancel Booking</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Financial Key Metrics & Dual Ledger Split -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Booking Value -->
        <div class="bg-gradient-to-br from-slate-900 to-sky-950 text-white p-5 rounded-2xl shadow-md border border-slate-800 sm:col-span-2 lg:col-span-1 flex flex-col justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-sky-400 tracking-wider block">Total Booking Value</span>
                <h3 class="text-2xl font-black font-mono mt-1 text-white tracking-tight">₹{{ number_format($booking->total_booking_value, 2) }}</h3>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-700/80 text-[11px] text-slate-300 space-y-0.5">
                <div>Base: ₹{{ number_format($booking->consideration_value, 2) }}</div>
                <div>GST ({{ $booking->tax_rate }}%): +₹{{ number_format($booking->tax_amount, 2) }}</div>
            </div>
        </div>

        <!-- Consideration Base -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Consideration</span>
            <h3 class="text-lg font-bold text-slate-800 mt-1">₹{{ number_format($booking->consideration_value, 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Rate: ₹{{ number_format($booking->rate_per_sqft, 2) }}/sqft
            </div>
        </div>

        <!-- Job Sheet Rollup -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Job Sheet Rollup</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">
                    {{ $booking->customizations->count() }} Items
                </span>
            </div>
            <h3 class="text-lg font-bold {{ $booking->supplementary_value >= 0 ? 'text-indigo-700' : 'text-amber-700' }} mt-1">
                {{ $booking->supplementary_value > 0 ? '+' : '' }}₹{{ number_format($booking->supplementary_value, 2) }}
            </h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Net Add-ons & Dislodges
            </div>
        </div>

        <!-- Dual Accounting Split: Taxable Agreement -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-emerald-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-building-columns text-[10px]"></i>
                <span>Taxable Agreement</span>
            </span>
            <h3 class="text-lg font-bold text-emerald-800 mt-1">₹{{ number_format($booking->gross_taxable_value, 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-mono">
                Paid: ₹{{ number_format($booking->total_taxable_received, 2) }} | <span class="text-amber-600 font-bold">Due: ₹{{ number_format($booking->taxable_due_balance, 2) }}</span>
            </div>
        </div>

        <!-- Dual Accounting Split: Non-Taxable Cash -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-amber-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                <span>Non-Taxable Cash</span>
            </span>
            <h3 class="text-lg font-bold text-amber-800 mt-1">₹{{ number_format($booking->gross_cash_value, 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Paid: ₹{{ number_format($booking->total_cash_received, 2) }} | <span class="text-amber-600 font-bold">Due: ₹{{ number_format($booking->cash_due_balance, 2) }}</span>
            </div>
        </div>

    </div>

    <!-- MAIN TABBED INTERFACE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Tab Bar -->
        <div class="flex items-center space-x-2 p-3 bg-slate-50/80 border-b border-slate-200/80 overflow-x-auto">
            
            <button @click="activeTab = 'transactions'"
                    :class="activeTab === 'transactions' ? 'bg-emerald-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition shrink-0">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Transactions & Receipts ({{ $booking->transactions->count() }})</span>
            </button>

            <button @click="activeTab = 'jobsheets'"
                    :class="activeTab === 'jobsheets' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition shrink-0">
                <i class="fa-solid fa-sliders"></i>
                <span>Customization Job Sheet ({{ $booking->customizations->count() }})</span>
            </button>

            <button @click="activeTab = 'agreement'"
                    :class="activeTab === 'agreement' ? 'bg-sky-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition shrink-0">
                <i class="fa-solid fa-file-contract"></i>
                <span>Sale Agreement (Bayna-nama)</span>
            </button>

            <button @click="activeTab = 'loan'"
                    :class="activeTab === 'loan' ? 'bg-emerald-700 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition shrink-0">
                <i class="fa-solid fa-landmark"></i>
                <span>Bank Finance (Home Loan)</span>
            </button>

            <button @click="activeTab = 'deed'"
                    :class="activeTab === 'deed' ? 'bg-purple-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition shrink-0">
                <i class="fa-solid fa-certificate"></i>
                <span>Sale Deed & Registration</span>
            </button>

            <button @click="activeTab = 'specs'"
                    :class="activeTab === 'specs' ? 'bg-slate-800 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition shrink-0">
                <i class="fa-solid fa-circle-info"></i>
                <span>Full Property & Customer Specs</span>
            </button>

        </div>

        <!-- TAB CONTENT CONTAINER -->
        <div class="p-6">
            
            <!-- TAB 0: TRANSACTIONS & RECEIPTS -->
            <div x-show="activeTab === 'transactions'" class="space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                            <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i>
                            <span>Financial Transactions & Receipts Ledger</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Money Receipts (Taxable Banking), Receipt Vouchers (Cash), and payment adjustments recorded for this booking.
                        </p>
                    </div>
                    <a href="{{ route('project.transactions.create', ['project' => $project->id, 'booking_id' => $booking->id]) }}"
                       class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ Collect Payment</span>
                    </a>
                </div>

                <!-- Live Ledger Matrix Card -->
                <div class="bg-gradient-to-br from-slate-900 to-sky-950 text-white p-5 rounded-2xl shadow-md border border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                    <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 space-y-1">
                        <div class="text-[10px] text-emerald-300 uppercase font-bold tracking-wider font-sans">Taxable Banking Stream</div>
                        <div class="text-slate-400">Target: <span class="text-white font-semibold">₹{{ number_format($booking->gross_taxable_value, 2) }}</span></div>
                        <div class="text-slate-400">Received: <span class="text-emerald-400 font-semibold">₹{{ number_format($booking->total_taxable_received, 2) }}</span></div>
                        <div class="pt-1 border-t border-slate-700 text-white font-bold flex items-center justify-between">
                            <span>Due Balance:</span>
                            <span class="text-amber-300">₹{{ number_format($booking->taxable_due_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 space-y-1">
                        <div class="text-[10px] text-amber-300 uppercase font-bold tracking-wider font-sans">Non-Taxable Cash Stream</div>
                        <div class="text-slate-400">Target: <span class="text-white font-semibold">₹{{ number_format($booking->gross_cash_value, 2) }}</span></div>
                        <div class="text-slate-400">Received: <span class="text-amber-400 font-semibold">₹{{ number_format($booking->total_cash_received, 2) }}</span></div>
                        <div class="pt-1 border-t border-slate-700 text-white font-bold flex items-center justify-between">
                            <span>Due Balance:</span>
                            <span class="text-amber-300">₹{{ number_format($booking->cash_due_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="bg-emerald-950/60 p-3 rounded-xl border border-emerald-800 space-y-1 flex flex-col justify-between font-sans">
                        <div>
                            <div class="text-[10px] text-emerald-400 uppercase font-bold tracking-wider">Total Net Outstanding Due</div>
                            <div class="text-2xl font-black font-mono text-white mt-1">₹{{ number_format($booking->total_outstanding_due, 2) }}</div>
                        </div>
                        @if($booking->total_dishonor_penalties > 0)
                            <div class="text-[10px] text-rose-300 font-mono">
                                (Includes ₹{{ number_format($booking->total_dishonor_penalties, 2) }} Bounced Cheque Penalties)
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Transactions History Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Voucher No & Date</th>
                                <th class="py-3 px-4">Voucher Type</th>
                                <th class="py-3 px-4">Mode & Ref</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4">Clearance Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($booking->transactions()->latest('voucher_date')->get() as $tx)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 align-top text-xs">
                                        <a href="{{ route('project.transactions.show', [$project->id, $tx->id]) }}" class="font-mono font-bold text-slate-800 hover:text-sky-600 hover:underline">
                                            {{ $tx->transaction_code }}
                                        </a>
                                        <div class="text-slate-400 text-[11px]">{{ $tx->voucher_date->format('d M, Y') }}</div>
                                    </td>
                                    <td class="py-3 px-4 align-top text-xs">
                                        @if($tx->voucher_type === 'money_receipt')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Money Receipt (Bank)
                                            </span>
                                        @elseif($tx->voucher_type === 'receipt_voucher')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Receipt Voucher (Cash)
                                            </span>
                                        @elseif($tx->voucher_type === 'payment_voucher')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                                Payment Outflow
                                            </span>
                                        @elseif($tx->voucher_type === 'adjustment_voucher')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                                Rebalance Adjustment
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-top text-xs space-y-0.5">
                                        <div class="font-bold uppercase text-slate-700">{{ $tx->transaction_mode }}</div>
                                        @if($tx->instrument_ref_no)
                                            <div class="font-mono text-slate-500 text-[11px]">Ref: {{ $tx->instrument_ref_no }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-top font-mono font-bold text-xs {{ $tx->voucher_category === 'payment_refund' ? 'text-rose-600' : 'text-slate-900' }}">
                                        {{ $tx->voucher_category === 'payment_refund' ? '-' : '+' }}₹{{ number_format($tx->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-4 align-top text-xs">
                                        @if($tx->instrument_status === 'cleared' || $tx->instrument_status === 'not_applicable')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fa-solid fa-circle-check mr-1"></i> Cleared
                                            </span>
                                        @elseif($tx->instrument_status === 'pending_clearance')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                                <i class="fa-solid fa-clock mr-1"></i> Pending
                                            </span>
                                        @elseif($tx->instrument_status === 'dishonored')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Dishonored
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-top text-right">
                                        <a href="{{ route('project.transactions.show', [$project->id, $tx->id]) }}" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs transition">
                                            <i class="fa-solid fa-print mr-1"></i> Voucher
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        No payments recorded yet for this booking. Click <strong>"+ Collect Payment"</strong> to issue the first Money Receipt or Cash Voucher.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                            <i class="fa-solid fa-sliders text-indigo-600"></i>
                            <span>Property Customization Job Sheets</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Record Add-ons (extra customer requirements) and Dislodges (materials deducted by client with adjusted labor).
                        </p>
                    </div>
                    <button @click="showAddCustomizationModal = true" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Job Sheet Item</span>
                    </button>
                </div>

                <!-- Customizations Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Particular / Scope of Work</th>
                                <th class="py-3 px-4">Material Rate</th>
                                <th class="py-3 px-4">Labour Rate</th>
                                <th class="py-3 px-4">Quantity</th>
                                <th class="py-3 px-4">Item Total</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($booking->customizations as $c)
                                <tr class="hover:bg-slate-50 transition">
                                    
                                    <td class="py-3 px-4 align-top">
                                        @if($c->job_type === 'addon')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                + Add-on
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                - Dislodge
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-3 px-4 align-top text-xs">
                                        <div class="font-bold text-slate-800">{{ $c->particular }}</div>
                                        @if($c->description)
                                            <div class="text-slate-400 text-[11px] mt-0.5">{{ $c->description }}</div>
                                        @endif
                                    </td>

                                    <td class="py-3 px-4 align-top text-xs font-mono">
                                        ₹{{ number_format($c->material_rate, 2) }}
                                    </td>

                                    <td class="py-3 px-4 align-top text-xs font-mono">
                                        ₹{{ number_format($c->labour_rate, 2) }}
                                    </td>

                                    <td class="py-3 px-4 align-top text-xs font-mono font-medium">
                                        {{ $c->quantity }} {{ $c->unit_measure }}
                                    </td>

                                    <td class="py-3 px-4 align-top text-xs font-mono font-bold text-slate-800">
                                        {{ $c->job_type === 'addon' ? '+' : '-' }}₹{{ number_format($c->job_total, 2) }}
                                    </td>

                                    <td class="py-3 px-4 align-top text-right">
                                        <form action="{{ route('project.bookings.jobsheets.destroy', [$project->id, $booking->id, $c->id]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this customization item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition" title="Delete Line Item">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                        No customization items added yet. Click <strong>"+ Add Job Sheet Item"</strong> to record extra electrical/civil add-ons or material deductions.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($booking->customizations->count() > 0)
                            <tfoot class="bg-slate-50 font-bold text-xs border-t border-slate-200">
                                <tr>
                                    <td colspan="5" class="py-3 px-4 text-right text-slate-700">Net Supplementary Value Rollup:</td>
                                    <td class="py-3 px-4 font-mono {{ $booking->supplementary_value >= 0 ? 'text-indigo-700' : 'text-amber-700' }} text-sm font-black">
                                        {{ $booking->supplementary_value > 0 ? '+' : '' }}₹{{ number_format($booking->supplementary_value, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

            </div>

            <!-- TAB 2: SALE AGREEMENT (BAYNA-NAMA) -->
            <div x-show="activeTab === 'agreement'" class="space-y-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                            <i class="fa-solid fa-file-contract text-sky-600"></i>
                            <span>Sale Agreement (Bayna-nama) & Dual Accounting</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Set the official registered agreement value. The system recalculates GST exclusively on the Agreement Value and splits the balance into Non-Taxable Cash.
                        </p>
                    </div>
                </div>

                @php $agr = $booking->saleAgreement; @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    
                    <form action="{{ route('project.bookings.sale-agreement.update', [$project->id, $booking->id]) }}" method="POST" class="space-y-4 md:col-span-2">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            
                            <!-- Agreement Status -->
                            <div>
                                <label for="agreement_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Agreement Status <span class="text-rose-500">*</span>
                                </label>
                                <select id="agreement_status" name="agreement_status" required
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                                    <option value="pending" {{ ($agr->agreement_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="drafted" {{ ($agr->agreement_status ?? '') === 'drafted' ? 'selected' : '' }}>Drafted</option>
                                    <option value="executed" {{ ($agr->agreement_status ?? '') === 'executed' ? 'selected' : '' }}>Executed / Signed</option>
                                    <option value="registered" {{ ($agr->agreement_status ?? '') === 'registered' ? 'selected' : '' }}>Registered at Sub-Registrar</option>
                                </select>
                            </div>

                            <!-- Serial No -->
                            <div>
                                <label for="agreement_serial_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Agreement Document No
                                </label>
                                <input type="text" id="agreement_serial_no" name="agreement_serial_no" value="{{ old('agreement_serial_no', $agr->agreement_serial_no ?? '') }}"
                                       placeholder="e.g. AGR/2026/088"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                            </div>

                            <!-- Execution Date -->
                            <div>
                                <label for="execution_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Execution Date
                                </label>
                                <input type="date" id="execution_date" name="execution_date" value="{{ old('execution_date', $agr?->execution_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                            </div>

                            <!-- Agreement Value -->
                            <div>
                                <label for="agreement_value" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Official Agreement Value (₹) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" id="agreement_value" name="agreement_value" value="{{ old('agreement_value', $agr->agreement_value ?? $booking->consideration_value) }}" required
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                                <span class="text-[10px] text-slate-400 mt-1 block">Value stated on registered stamp paper</span>
                            </div>

                            <!-- Tax Rate % -->
                            <div>
                                <label for="tax_rate" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    GST Rate (%) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $agr->tax_rate ?? $booking->tax_rate) }}" required
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-3">
                                <label for="document_details" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Agreement Terms / Remarks
                                </label>
                                <textarea id="document_details" name="document_details" rows="2"
                                          placeholder="e.g. Executed on ₹100 non-judicial stamp paper, Sub-registrar Silchar"
                                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">{{ old('document_details', $agr->document_details ?? '') }}</textarea>
                            </div>

                        </div>

                        <div class="flex items-center justify-end pt-3">
                            <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold transition flex items-center space-x-1.5 shadow-sm">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Update Sale Agreement & Rebalance Split</span>
                            </button>
                        </div>

                    </form>

                </div>

            </div>

            <!-- TAB 3: BANK FINANCE (HOME LOAN) -->
            <div x-show="activeTab === 'loan'" class="space-y-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                            <i class="fa-solid fa-landmark text-emerald-600"></i>
                            <span>Bank Finance & Home Loan Tracking</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Manage home loan application status, bank sanction letters, and loan disbursement progress.
                        </p>
                    </div>
                </div>

                @php $loan = $booking->bankFinance; @endphp
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    
                    <form action="{{ route('project.bookings.bank-finance.update', [$project->id, $booking->id]) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            
                            <!-- Finance Status -->
                            <div>
                                <label for="finance_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Loan Status <span class="text-rose-500">*</span>
                                </label>
                                <select id="finance_status" name="finance_status" required
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                                    <option value="not_applicable" {{ ($loan->finance_status ?? 'not_applicable') === 'not_applicable' ? 'selected' : '' }}>Self-Financed / N/A</option>
                                    <option value="applied" {{ ($loan->finance_status ?? '') === 'applied' ? 'selected' : '' }}>Applied / In Process</option>
                                    <option value="sanctioned" {{ ($loan->finance_status ?? '') === 'sanctioned' ? 'selected' : '' }}>Loan Sanctioned</option>
                                    <option value="disbursed" {{ ($loan->finance_status ?? '') === 'disbursed' ? 'selected' : '' }}>Partially / Fully Disbursed</option>
                                </select>
                            </div>

                            <!-- Bank Name -->
                            <div>
                                <label for="bank_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Lending Bank Name
                                </label>
                                <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $loan->bank_name ?? '') }}"
                                       placeholder="e.g. State Bank of India or HDFC"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            </div>

                            <!-- Branch Name -->
                            <div>
                                <label for="branch_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Bank Branch
                                </label>
                                <input type="text" id="branch_name" name="branch_name" value="{{ old('branch_name', $loan->branch_name ?? '') }}"
                                       placeholder="e.g. Silchar Main Branch"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            </div>

                            <!-- Loan Account No -->
                            <div>
                                <label for="loan_account_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Loan Account / File No
                                </label>
                                <input type="text" id="loan_account_no" name="loan_account_no" value="{{ old('loan_account_no', $loan->loan_account_no ?? '') }}"
                                       placeholder="e.g. HL-987654321"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            </div>

                            <!-- Sanctioned Amount -->
                            <div>
                                <label for="sanctioned_amount" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Sanctioned Amount (₹)
                                </label>
                                <input type="number" step="0.01" id="sanctioned_amount" name="sanctioned_amount" value="{{ old('sanctioned_amount', $loan->sanctioned_amount ?? 0) }}"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            </div>

                            <!-- Disbursed Amount -->
                            <div>
                                <label for="disbursed_amount" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Disbursed Amount (₹)
                                </label>
                                <input type="number" step="0.01" id="disbursed_amount" name="disbursed_amount" value="{{ old('disbursed_amount', $loan->disbursed_amount ?? 0) }}"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            </div>

                            <!-- Remarks -->
                            <div class="sm:col-span-3">
                                <label for="remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Bank Remarks / NOC Status
                                </label>
                                <textarea id="remarks" name="remarks" rows="2"
                                          placeholder="e.g. Tripartite agreement signed, 1st disbursement cleared via NEFT"
                                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">{{ old('remarks', $loan->remarks ?? '') }}</textarea>
                            </div>

                        </div>

                        <div class="flex items-center justify-end pt-3">
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition flex items-center space-x-1.5 shadow-sm">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Save Bank Finance Details</span>
                            </button>
                        </div>

                    </form>

                </div>

            </div>

            <!-- TAB 4: SALE DEED & REGISTRATION -->
            <div x-show="activeTab === 'deed'" class="space-y-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                            <i class="fa-solid fa-certificate text-purple-600"></i>
                            <span>Sale Deed & Registration Registry</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Record the permanent registered sale deed number, execution date, and sub-registrar office details.
                        </p>
                    </div>
                </div>

                @php $deed = $booking->saleDeed; @endphp
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    
                    <form action="{{ route('project.bookings.sale-deed.update', [$project->id, $booking->id]) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            
                            <!-- Status -->
                            <div>
                                <label for="deed_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Registration Status <span class="text-rose-500">*</span>
                                </label>
                                <select id="deed_status" name="status" required
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
                                    <option value="pending" {{ ($deed->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending Registration</option>
                                    <option value="registered" {{ ($deed->status ?? '') === 'registered' ? 'selected' : '' }}>Registered Deed Completed</option>
                                </select>
                            </div>

                            <!-- Sale Deed No -->
                            <div>
                                <label for="sale_deed_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Sale Deed Number
                                </label>
                                <input type="text" id="sale_deed_no" name="sale_deed_no" value="{{ old('sale_deed_no', $deed->sale_deed_no ?? '') }}"
                                       placeholder="e.g. Deed No 1234/2026"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
                            </div>

                            <!-- Executed Date -->
                            <div>
                                <label for="executed_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Registration Date
                                </label>
                                <input type="date" id="executed_date" name="executed_date" value="{{ old('executed_date', $deed?->executed_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
                            </div>

                            <!-- Sub Registrar Office -->
                            <div class="sm:col-span-2">
                                <label for="sub_registrar_office" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Sub-Registrar Office
                                </label>
                                <input type="text" id="sub_registrar_office" name="sub_registrar_office" value="{{ old('sub_registrar_office', $deed->sub_registrar_office ?? '') }}"
                                       placeholder="e.g. Senior Sub-Registrar Office, Silchar, Cachar"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
                            </div>

                            <!-- Remarks -->
                            <div class="sm:col-span-3">
                                <label for="deed_remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Registration Remarks
                                </label>
                                <textarea id="deed_remarks" name="remarks" rows="2"
                                          placeholder="e.g. Volume No 14, Book No 1, Pages 100 to 125"
                                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">{{ old('remarks', $deed->remarks ?? '') }}</textarea>
                            </div>

                        </div>

                        <div class="flex items-center justify-end pt-3">
                            <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition flex items-center space-x-1.5 shadow-sm">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Save Sale Deed Records</span>
                            </button>
                        </div>

                    </form>

                </div>

            </div>

            <!-- TAB 5: PROPERTY & CUSTOMER SPECIFICATIONS -->
            <div x-show="activeTab === 'specs'" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Property Details Card -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                        <div class="font-bold text-slate-800 text-sm uppercase tracking-wide flex items-center space-x-2 border-b border-slate-200 pb-2">
                            <i class="fa-solid fa-building text-sky-600"></i>
                            <span>Property Specifications</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-slate-600">
                            <div>Unit No: <span class="font-bold text-slate-900 font-mono">{{ $booking->unit_no }}</span></div>
                            <div>Block: <span class="font-semibold text-slate-800">{{ $booking->block_name ?? 'N/A' }}</span></div>
                            <div>Floor: <span class="font-semibold text-slate-800">{{ $booking->floor_no ?? 'N/A' }}</span></div>
                            <div>Property Type: <span class="font-semibold text-slate-800">{{ $booking->property_type }}</span></div>
                            <div>Built-up: <span class="font-semibold text-slate-800">{{ $booking->built_up_area }} sq.ft.</span></div>
                            <div>Super Built-up: <span class="font-bold text-indigo-700 font-mono">{{ $booking->super_built_up_area }} sq.ft.</span></div>
                            <div>Parking: <span class="font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }}</span></div>
                            <div>Parking Slot: <span class="font-semibold text-slate-800">{{ $booking->parking_no ?? 'None' }}</span></div>
                        </div>
                        <div class="pt-2 border-t border-slate-200 text-slate-500">
                            <strong>Project Land:</strong> Daag: {{ $project->daag_no ?? '-' }}, Patta: {{ $project->patta_no ?? '-' }}, Mouza: {{ $project->mouza ?? '-' }}
                        </div>
                    </div>

                    <!-- Customer Profile Card -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                        <div class="font-bold text-slate-800 text-sm uppercase tracking-wide flex items-center space-x-2 border-b border-slate-200 pb-2">
                            <i class="fa-solid fa-user-check text-emerald-600"></i>
                            <span>Customer Profile</span>
                        </div>
                        <div class="space-y-1.5 text-slate-600">
                            <div>Name: <span class="font-bold text-slate-900">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</span></div>
                            @if($booking->guardian_name)
                                <div>{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: <span class="font-semibold text-slate-800">{{ $booking->guardian_name }}</span></div>
                            @endif
                            <div>Mobile: <span class="font-semibold text-slate-800">{{ $booking->mobile_no }}</span></div>
                            @if($booking->alt_mobile_no)
                                <div>Alt Mobile: <span class="font-semibold text-slate-800">{{ $booking->alt_mobile_no }}</span></div>
                            @endif
                            <div>Email: <span class="font-semibold text-slate-800">{{ $booking->email_id ?? 'N/A' }}</span></div>
                            <div>Address: <span class="font-semibold text-slate-800">{{ $booking->address ?? 'N/A' }}</span></div>
                            <div>PAN: <span class="font-mono font-bold text-slate-800">{{ $booking->pan_number ?? 'N/A' }}</span> | GSTIN: <span class="font-mono font-bold text-slate-800">{{ $booking->gstin ?? 'N/A' }}</span></div>
                            <div>ID Proof: <span class="font-semibold text-slate-800">{{ ucfirst($booking->photo_id_type) }} ({{ $booking->photo_id_no ?? 'N/A' }})</span></div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- MODAL: ADD CUSTOMIZATION (JOB SHEET) ITEM -->
    <div x-show="showAddCustomizationModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200" @click.away="showAddCustomizationModal = false">
            
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-sliders text-white"></i>
                    <h3 class="font-bold text-sm">ADD JOB SHEET CUSTOMIZATION ITEM</h3>
                </div>
                <button @click="showAddCustomizationModal = false" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('project.bookings.jobsheets.store', [$project->id, $booking->id]) }}" method="POST" class="p-6 space-y-4">
                @csrf

                <!-- Job Type: Addon vs Dislodge -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Customization Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                               :class="jobType === 'addon' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="job_type" value="addon" x-model="jobType" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs">+ Add-on (Addition)</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                               :class="jobType === 'dislodge' ? 'bg-amber-50 border-amber-500 text-amber-800 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="job_type" value="dislodge" x-model="jobType" class="text-amber-600 focus:ring-amber-500">
                            <span class="text-xs">- Dislodge (Deduction)</span>
                        </label>
                    </div>
                </div>

                <!-- Particular -->
                <div>
                    <label for="particular" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Particular / Item Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="particular" name="particular" required
                           placeholder="e.g. Extra AC Point Wiring, Marble Flooring upgrade"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <!-- Rate Inputs -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Material (₹)
                        </label>
                        <input type="number" step="0.01" name="material_rate" x-model.number="matRate" required
                               placeholder="0.00"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Labour (₹)
                        </label>
                        <input type="number" step="0.01" name="labour_rate" x-model.number="labRate" required
                               placeholder="0.00"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Schedule (₹)
                        </label>
                        <input type="number" step="0.01" name="schedule_rate" x-model.number="schRate"
                               placeholder="0.00"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Quantity & Unit -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Quantity <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="quantity" x-model.number="qty" required min="0.01"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Unit <span class="text-rose-500">*</span>
                        </label>
                        <select name="unit_measure" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <option value="LS">LS (Lump Sum)</option>
                            <option value="nos">nos (Units)</option>
                            <option value="sq.ft.">sq.ft.</option>
                            <option value="points">points</option>
                            <option value="rft">rft</option>
                        </select>
                    </div>
                </div>

                <!-- Live Total Preview -->
                <div class="p-3 rounded-xl bg-slate-100 flex items-center justify-between border border-slate-200">
                    <span class="text-xs font-bold text-slate-700">Calculated Line Total:</span>
                    <span class="text-base font-black font-mono" :class="jobType === 'addon' ? 'text-emerald-700' : 'text-amber-700'" x-text="(jobType === 'addon' ? '+' : '-') + '₹' + calculatedJobTotal.toFixed(2)">
                        ₹0.00
                    </span>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="showAddCustomizationModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md transition">
                        Add to Job Sheet
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- MODAL: CANCEL BOOKING -->
    <div x-show="showCancelModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200" @click.away="showCancelModal = false">
            
            <div class="bg-rose-600 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-triangle-exclamation text-white"></i>
                    <h3 class="font-bold text-sm">CANCEL BOOKING: {{ $booking->booking_code }}</h3>
                </div>
                <button @click="showCancelModal = false" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('project.bookings.cancel', [$project->id, $booking->id]) }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="cancellation_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Cancellation Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="cancellation_date" name="cancellation_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                </div>

                <div>
                    <label for="cancellation_charge" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Cancellation Deduction Fee (₹) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" id="cancellation_charge" name="cancellation_charge" value="0.00" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                </div>

                <div>
                    <label for="cancellation_remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Reason for Cancellation <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="cancellation_remarks" name="cancellation_remarks" rows="3" required
                              placeholder="e.g. Client requested cancellation due to personal relocation"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="showCancelModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Close
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-md transition">
                        Confirm Cancellation
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
