@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedTx: null,
    showClearanceModal: false,
    clearanceStatus: 'cleared',
    dishonorPenalty: 500,
    dishonorDate: '{{ date('Y-m-d') }}',
    dishonorRemarks: '',
    openRebalanceModal: false,
    rebalanceBookingId: '',
    openClearanceModal(tx) {
        this.selectedTx = tx;
        this.clearanceStatus = 'cleared';
        this.dishonorPenalty = 500;
        this.dishonorDate = '{{ date('Y-m-d') }}';
        this.dishonorRemarks = '';
        this.showClearanceModal = true;
    }
}">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-emerald-600">Transactions Ledger</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dual-Ledger Financial Transactions</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track Money Receipts (Taxable Banking/GST), Receipt Vouchers (Cash), Payment Refunds, and Cheque Clearances.</p>
        </div>

        {{-- 5 Core Financial Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            
            {{-- 1. Collect Payment --}}
            @if(auth()->user()->hasPermission('create_receipts'))
                <a href="{{ route('project.transactions.create', $project->id) }}"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>Collect Payment</span>
                </a>
            @endif

            {{-- 2. Refunds (New requested button with Dropdown) --}}
            @if(auth()->user()->hasAnyPermission(['create_refunds', 'create_receipts', 'view_transactions']))
                <div class="relative" x-data="{ refundMenuOpen: false }">
                    <button @click="refundMenuOpen = !refundMenuOpen" type="button"
                            class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-sm shadow-amber-500/20 transition">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Refunds</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 transition-transform" :class="refundMenuOpen ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="refundMenuOpen" @click.away="refundMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 text-xs" style="display: none;">
                        <div class="px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            Refund Actions & Outflows
                        </div>
                        @if(auth()->user()->hasPermission('create_refunds') || auth()->user()->hasPermission('create_receipts'))
                            <a href="{{ route('project.transactions.create', ['project' => $project->id, 'voucher_type' => 'payment_voucher']) }}"
                               class="flex items-start space-x-2.5 px-3.5 py-2 text-slate-700 hover:bg-amber-50 hover:text-amber-800 transition">
                                <i class="fa-solid fa-receipt text-amber-500 mt-1 w-4"></i>
                                <div>
                                    <div class="font-bold">Issue Refund Voucher</div>
                                    <div class="text-[10px] text-slate-400">Payment Outflow (Taxable/Non-Taxable)</div>
                                </div>
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('cancel_bookings'))
                            <a href="{{ route('project.cancellations.index', $project->id) }}"
                               class="flex items-start space-x-2.5 px-3.5 py-2 text-slate-700 hover:bg-rose-50 hover:text-rose-800 transition">
                                <i class="fa-solid fa-scale-balanced text-rose-500 mt-1 w-4"></i>
                                <div>
                                    <div class="font-bold">Cancellation Payouts Hub</div>
                                    <div class="text-[10px] text-slate-400">Stage 2.1.6: 3-Account Settlement</div>
                                </div>
                            </a>
                        @endif
                        <a href="{{ route('project.transactions.index', ['project' => $project->id, 'type' => 'payment_voucher']) }}"
                           class="flex items-start space-x-2.5 px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition border-t border-slate-100">
                            <i class="fa-solid fa-filter text-slate-400 mt-1 w-4"></i>
                            <div>
                                <div class="font-bold">Filter Refund Records</div>
                                <div class="text-[10px] text-slate-400">Show all payment vouchers in ledger</div>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            {{-- 3. Rebalance Overpayment (With Overpayment Detection Modal) --}}
            @if(auth()->user()->hasPermission('manage_adjustments'))
                <button @click="openRebalanceModal = true" type="button"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/20 transition {{ count($overpaidBookings) > 0 ? 'ring-2 ring-indigo-400 animate-pulse' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Rebalance Overpayment</span>
                    @if(count($overpaidBookings) > 0)
                        <span class="bg-amber-400 text-slate-950 text-[10px] font-black px-1.5 py-0.5 rounded-full" title="{{ count($overpaidBookings) }} customer bookings with excess bank collections">
                            {{ count($overpaidBookings) }}
                        </span>
                    @endif
                </button>
            @endif

            {{-- 4. Cancel Booking --}}
            @if(auth()->user()->hasPermission('cancel_bookings'))
                <a href="{{ route('project.cancellations.index', $project->id) }}"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold shadow-sm transition">
                    <i class="fa-solid fa-ban"></i>
                    <span>Cancel Booking</span>
                </a>
            @endif

            {{-- 5. Print Documents --}}
            @if(auth()->user()->hasPermission('print_documents'))
                <a href="{{ route('project.documents.index', $project->id) }}"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Documents</span>
                </a>
            @endif

        </div>
    </div>

    <!-- Financial Stats Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Bank Received -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-emerald-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-building-columns text-[10px]"></i>
                <span>Bank Receipts (Taxable)</span>
            </span>
            <h3 class="text-xl font-bold text-emerald-800 mt-1">₹{{ number_format($stats['total_bank_received'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Official Bayna-nama & GST
            </div>
        </div>

        <!-- Total Cash Received -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-amber-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                <span>Cash Receipts (Non-Taxable)</span>
            </span>
            <h3 class="text-xl font-bold text-amber-800 mt-1">₹{{ number_format($stats['total_cash_received'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Customizations & Cash split
            </div>
        </div>

        <!-- Total Refunds Outflow -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-rose-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-hand-holding-dollar text-[10px]"></i>
                <span>Refunds & Outflows</span>
            </span>
            <h3 class="text-xl font-bold text-rose-700 mt-1">₹{{ number_format($stats['total_refunds'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Cancellations & Adjustments
            </div>
        </div>

        <!-- Pending Cheques -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Pending Cheques</span>
                    <h3 class="text-xl font-bold text-amber-600 mt-1">{{ $stats['pending_cheques_count'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Awaiting bank clearing
            </div>
        </div>

        <!-- Dishonored Cheques -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Bounced Cheques</span>
                    <h3 class="text-xl font-bold text-rose-600 mt-1">{{ $stats['dishonored_count'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Reversed with penalty
            </div>
        </div>

    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Search Input -->
        <form action="{{ route('project.transactions.index', $project->id) }}" method="GET" class="w-full md:w-96 flex items-center">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search Voucher Code, Customer, Cheque Ref..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
            </div>
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
        </form>

        <!-- Status Filter Tabs -->
        <div class="flex items-center space-x-1.5 overflow-x-auto w-full md:w-auto text-xs font-medium">
            <a href="{{ route('project.transactions.index', ['project' => $project->id, 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ !request('type') ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Vouchers
            </a>
            <a href="{{ route('project.transactions.index', ['project' => $project->id, 'type' => 'money_receipt', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('type') === 'money_receipt' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Money Receipts (Bank)
            </a>
            <a href="{{ route('project.transactions.index', ['project' => $project->id, 'type' => 'receipt_voucher', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('type') === 'receipt_voucher' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Receipt Vouchers (Cash)
            </a>
            <a href="{{ route('project.transactions.index', ['project' => $project->id, 'type' => 'payment_voucher', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('type') === 'payment_voucher' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Payments & Refunds
            </a>
            <a href="{{ route('project.transactions.index', ['project' => $project->id, 'type' => 'pending_cheques', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('type') === 'pending_cheques' ? 'bg-amber-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Pending Cheques ({{ $stats['pending_cheques_count'] }})
            </a>
        </div>

    </div>

    <!-- Transactions Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Voucher ID & Date</th>
                        <th class="py-3.5 px-4">Customer & Property</th>
                        <th class="py-3.5 px-4">Stream & Account</th>
                        <th class="py-3.5 px-4">Payment Mode & Ref</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Status & Clearance</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- Voucher ID & Date -->
                            <td class="py-4 px-4 align-top">
                                <a href="{{ route('project.transactions.show', [$project->id, $t->id]) }}" class="font-mono font-bold text-xs bg-slate-100 hover:bg-slate-200 px-2 py-0.5 rounded border border-slate-300 text-slate-800 inline-block">
                                    {{ $t->transaction_code }}
                                </a>
                                @if($t->voucher_no)
                                    <div class="text-[11px] font-mono text-slate-600 font-semibold mt-0.5" title="User-entered Voucher Number">
                                        Vch: {{ $t->voucher_no }}
                                    </div>
                                @endif
                                <div class="text-xs text-slate-400 mt-1">
                                    <i class="fa-regular fa-calendar text-[11px] mr-1"></i> {{ $t->voucher_date->format('d M Y') }}
                                </div>
                            </td>

                            <!-- Customer & Property -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                @if($t->booking)
                                    <div class="font-bold text-slate-800 text-sm">
                                        <a href="{{ route('project.bookings.show', [$project->id, $t->booking->id]) }}" class="hover:text-sky-600 hover:underline">
                                            {{ $t->booking->customer_name }}
                                        </a>
                                    </div>
                                    <div class="text-slate-500">
                                        Unit: <span class="font-bold text-indigo-700 font-mono">{{ $t->booking->unit_no }}</span> ({{ $t->booking->booking_code }})
                                    </div>
                                @else
                                    <div class="font-bold text-slate-500 italic">Direct Project Expense / Other</div>
                                @endif
                            </td>

                            <!-- Stream & Account -->
                            <td class="py-4 px-4 align-top text-xs space-y-1">
                                <div>
                                    @if($t->voucher_type === 'money_receipt')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <i class="fa-solid fa-building-columns mr-1 text-[9px]"></i> Money Receipt (Taxable)
                                        </span>
                                    @elseif($t->voucher_type === 'receipt_voucher')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <i class="fa-solid fa-money-bill-wave mr-1 text-[9px]"></i> Receipt Voucher (Cash)
                                        </span>
                                    @elseif($t->voucher_type === 'payment_voucher')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <i class="fa-solid fa-hand-holding-dollar mr-1 text-[9px]"></i> Payment Outflow ({{ ($t->payment_category ?? ($t->is_taxable_transaction ? 'taxable' : 'non_taxable')) === 'taxable' ? 'Taxable' : 'Non-Taxable' }})
                                        </span>
                                    @elseif($t->voucher_type === 'adjustment_voucher')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                                            <i class="fa-solid fa-scale-balanced mr-1 text-[9px]"></i> Rebalance Adjustment
                                        </span>
                                    @endif
                                </div>
                                <div class="text-slate-500 text-[11px]">
                                    {{ $t->bankAccount ? $t->bankAccount->account_nick_name : 'Cash Account' }}
                                </div>
                            </td>

                            <!-- Payment Mode & Ref -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                <div class="font-bold uppercase text-slate-700">
                                    {{ $t->transaction_mode }}
                                    @if($t->source_of_payment === 'through_loan_account')
                                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded ml-1">
                                            Home Loan
                                        </span>
                                    @endif
                                </div>
                                @if($t->instrument_ref_no)
                                    <div class="font-mono text-slate-500 text-[11px]">
                                        Ref: {{ $t->instrument_ref_no }}
                                    </div>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-4 px-4 align-top font-mono">
                                <span class="text-base font-black {{ $t->voucher_category === 'payment_refund' ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ $t->voucher_category === 'payment_refund' ? '-' : '+' }}₹{{ number_format($t->amount, 2) }}
                                </span>
                            </td>

                            <!-- Status & Clearance -->
                            <td class="py-4 px-4 align-top text-xs space-y-1">
                                <div>
                                    @if($t->instrument_status === 'cleared' || $t->instrument_status === 'not_applicable')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Cleared
                                        </span>
                                    @elseif($t->instrument_status === 'pending_clearance')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                            <i class="fa-solid fa-clock mr-1 text-[10px]"></i> Pending Clearance
                                        </span>
                                    @elseif($t->instrument_status === 'dishonored')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-triangle-exclamation mr-1 text-[10px]"></i> Dishonored
                                        </span>
                                        <div class="text-[10px] text-rose-600 font-semibold mt-0.5">
                                            Penalty: +₹{{ number_format($t->dishonor_penalty_amount, 2) }}
                                        </div>
                                    @endif
                                </div>

                                @if($t->instrument_status === 'pending_clearance')
                                    <button type="button" @click="openClearanceModal({{ json_encode($t) }})"
                                            class="inline-block mt-1 text-[11px] font-bold text-sky-600 hover:underline">
                                        Update Clearance &rarr;
                                    </button>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('project.transactions.show', [$project->id, $t->id]) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-900 hover:bg-emerald-600 text-white rounded-lg text-xs font-medium transition shadow-sm" title="View & Print Voucher">
                                    <i class="fa-solid fa-print mr-1"></i> Voucher
                                </a>
                                @if(auth()->user()->hasPermission('create_receipts'))
                                    <a href="{{ route('project.transactions.edit', [$project->id, $t->id]) }}" class="inline-flex items-center px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-medium transition shadow-sm" title="Edit Transaction">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                    </a>
                                    <form action="{{ route('project.transactions.destroy', [$project->id, $t->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to permanently delete transaction {{ $t->transaction_code }}? This will reverse its financial ledger entries and update customer balances.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-medium transition shadow-sm" title="Delete Transaction">
                                            <i class="fa-solid fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-receipt text-3xl text-slate-300 mb-2 block"></i>
                                No financial transactions recorded yet for <strong>{{ $project->name }}</strong>.
                                <div class="mt-2">
                                    <a href="{{ route('project.transactions.create', $project->id) }}" class="inline-flex items-center space-x-1 text-xs font-bold text-emerald-600 hover:underline">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Record First Payment Receipt</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

    <!-- MODAL: CHEQUE CLEARANCE / DISHONOR ACTION -->
    <div x-show="showClearanceModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200" @click.away="showClearanceModal = false">
            
            <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-money-check-dollar text-emerald-400"></i>
                    <h3 class="font-bold text-sm">BANK INSTRUMENT CLEARANCE</h3>
                </div>
                <button @click="showClearanceModal = false" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <template x-if="selectedTx">
                <form :action="'{{ url('project') }}/{{ $project->id }}/transactions/' + selectedTx.id + '/status'" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                        <div>Voucher: <span class="font-mono font-bold" x-text="selectedTx.transaction_code"></span></div>
                        <div>Amount: <span class="font-mono font-bold text-emerald-700" x-text="'₹' + parseFloat(selectedTx.amount).toFixed(2)"></span></div>
                        <div>Ref / Cheque No: <span class="font-mono" x-text="selectedTx.instrument_ref_no || 'N/A'"></span></div>
                    </div>

                    <!-- Status Selection -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Clearance Result <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="clearanceStatus === 'cleared' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                <input type="radio" name="instrument_status" value="cleared" x-model="clearanceStatus" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs">Cleared in Bank</span>
                            </label>
                            <label class="flex items-center space-x-2 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="clearanceStatus === 'dishonored' ? 'bg-rose-50 border-rose-500 text-rose-800 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                <input type="radio" name="instrument_status" value="dishonored" x-model="clearanceStatus" class="text-rose-600 focus:ring-rose-500">
                                <span class="text-xs">Dishonored / Bounced</span>
                            </label>
                        </div>
                    </div>

                    <!-- Dishonor Penalty & Remarks (Only when bounced) -->
                    <div x-show="clearanceStatus === 'dishonored'" class="space-y-3 pt-2 border-t border-slate-100">
                        <div>
                            <label for="dishonor_penalty_amount" class="block text-xs font-bold uppercase tracking-wider text-rose-700 mb-1">
                                Cheque Bounce Penalty Fee (₹) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" id="dishonor_penalty_amount" name="dishonor_penalty_amount" x-model.number="dishonorPenalty"
                                   class="w-full px-3 py-2 bg-rose-50/50 border border-rose-200 rounded-xl text-xs font-mono font-bold text-rose-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Added directly onto customer balance due</span>
                        </div>

                        <div>
                            <label for="dishonor_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Dishonor Date
                            </label>
                            <input type="date" id="dishonor_date" name="dishonor_date" x-model="dishonorDate"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label for="dishonor_remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Bank Return Reason
                            </label>
                            <input type="text" id="dishonor_remarks" name="dishonor_remarks" x-model="dishonorRemarks"
                                   placeholder="e.g. Insufficient Funds / Signature Mismatch"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3">
                        <button type="button" @click="showClearanceModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold shadow-md transition">
                            Confirm Update
                        </button>
                    </div>

                </form>
            </template>

        </div>
    </div>

    {{-- Rebalance Overpayment Wizard Modal --}}
    @if(auth()->user()->hasPermission('manage_adjustments'))
    <div x-show="openRebalanceModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;"
         @keydown.escape.window="openRebalanceModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden"
             @click.away="openRebalanceModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-700 to-purple-800 p-5 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-scale-balanced text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Cross-Ledger Overpayment Rebalance</h3>
                        <p class="text-[11px] text-indigo-200">Rebalance excess taxable bank collections to non-taxable cash ledger</p>
                    </div>
                </div>
                <button type="button" @click="openRebalanceModal = false" class="text-white/70 hover:text-white text-lg transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <!-- Overpayment Detection List -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2.5 flex items-center justify-between">
                        <span class="flex items-center space-x-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                            <span>Detected Taxable Overpayments</span>
                        </span>
                        <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                            {{ count($overpaidBookings) }} {{ Str::plural('booking', count($overpaidBookings)) }} detected
                        </span>
                    </h4>

                    @if(count($overpaidBookings) > 0)
                        <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                            @foreach($overpaidBookings as $ob)
                                @php
                                    $excess = round($ob->total_taxable_received - (float)$ob->gross_taxable_value, 2);
                                @endphp
                                <div class="p-3.5 rounded-xl border border-indigo-100 bg-indigo-50/50 hover:bg-indigo-50 transition flex items-center justify-between gap-3">
                                    <div class="space-y-0.5 min-w-0">
                                        <div class="font-bold text-xs text-slate-800 truncate">
                                            {{ $ob->customer_name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium">
                                            Unit: <span class="font-bold text-indigo-700 font-mono">{{ $ob->unit_no }}</span> ({{ $ob->booking_code }})
                                        </div>
                                        <div class="text-[10px] text-slate-600 flex items-center space-x-2">
                                            <span>Target: ₹{{ number_format($ob->gross_taxable_value, 2) }}</span>
                                            <span>•</span>
                                            <span class="text-emerald-700 font-semibold">Bank Rec: ₹{{ number_format($ob->total_taxable_received, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="inline-block text-xs font-mono font-black text-indigo-800 bg-indigo-100 px-2.5 py-0.5 rounded-md border border-indigo-200">
                                            +₹{{ number_format($excess, 2) }}
                                        </span>
                                        <div class="mt-1.5">
                                            <a href="{{ route('project.bookings.adjustment.create', [$project->id, $ob->id]) }}"
                                               class="inline-flex items-center space-x-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                                <span>Rebalance Now</span>
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 text-center text-xs text-emerald-800 space-y-1">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-xl block"></i>
                            <div class="font-bold">All Bookings Balanced</div>
                            <p class="text-[11px] text-emerald-700 font-normal">
                                No excess taxable collections detected across project bookings. All customer bank receipts are within agreed targets.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Manual Selection for Any Active Booking -->
                <div class="pt-4 border-t border-slate-100">
                    <label for="rebalance_booking_select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Or Select Any Customer Booking to Rebalance
                    </label>
                    <div class="flex items-center space-x-2">
                        <select id="rebalance_booking_select" x-model="rebalanceBookingId"
                                class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <option value="">-- Choose Flat / Customer Booking --</option>
                            @foreach($allProjectBookings as $ab)
                                <option value="{{ $ab->id }}">
                                    🏢 Unit {{ $ab->unit_no }} - {{ $ab->customer_name }} ({{ $ab->booking_code }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button"
                                :disabled="!rebalanceBookingId"
                                @click="if (rebalanceBookingId) { window.location.href = '/project/{{ $project->id }}/bookings/' + rebalanceBookingId + '/adjustment'; }"
                                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                            <span>Proceed</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
@endsection
