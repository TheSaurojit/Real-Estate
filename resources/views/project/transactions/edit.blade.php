@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    voucherType: '{{ old('voucher_type', $transaction->voucher_type) }}',
    paymentCategory: '{{ old('payment_category', $transaction->payment_category ?? ($transaction->is_taxable_transaction ? 'taxable' : 'non_taxable')) }}',
    txMode: '{{ old('transaction_mode', $transaction->transaction_mode) }}',
    paymentSource: '{{ old('source_of_payment', $transaction->source_of_payment) }}',
    enteredAmount: {{ old('amount', $transaction->amount) }},
    selectedBookingId: '{{ old('booking_id', $transaction->booking_id) }}',
    instrumentStatus: '{{ old('instrument_status', $transaction->instrument_status) }}',

    bookingsData: {
        @foreach($bookings as $b)
            '{{ $b->id }}': {
                customer: '{{ addslashes($b->customer_name) }}',
                code: '{{ $b->booking_code }}',
                unit: '{{ $b->unit_no }}',
                bookingAmount: {{ (float)($b->final_booking_value > 0 ? $b->final_booking_value : $b->total_booking_value) }},
                grossTaxable: {{ $b->gross_taxable_value }},
                taxableReceived: {{ $b->total_taxable_received }},
                taxableDue: {{ $b->taxable_due_balance }},
                grossCash: {{ $b->gross_cash_value }},
                cashReceived: {{ $b->total_cash_received }},
                cashDue: {{ $b->cash_due_balance }},
                penalties: {{ $b->total_dishonor_penalties }},
                totalDue: {{ $b->total_outstanding_due }},
                loanSanctioned: {{ $b->bankFinance?->sanctioned_amount ?? 0 }},
                loanDisbursed: {{ $b->bankFinance?->disbursed_amount ?? 0 }},
            },
        @endforeach
    },

    get activeBooking() {
        return this.bookingsData[this.selectedBookingId] || null;
    },

    fillAmount(amt) {
        this.enteredAmount = amt;
    },

    formatNumber(num) {
        return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);
    }
}">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.transactions.index', $project->id) }}" class="hover:text-sky-600">Transactions</a>
                <span>/</span>
                <span class="text-amber-600">Edit Voucher</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Transaction & Voucher</h1>
            <p class="text-xs text-slate-500 mt-0.5">Modify transaction particulars, dates, instruments, or amounts with dual-ledger synchronization.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('project.transactions.show', [$project->id, $transaction->id]) }}" class="px-3.5 py-2 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold transition flex items-center space-x-1.5 shadow-sm">
                <i class="fa-solid fa-print mr-1"></i> View Voucher
            </a>
            <a href="{{ route('project.transactions.index', $project->id) }}" class="px-3.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Ledger
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700">
            <div class="font-bold flex items-center space-x-2 mb-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Please fix the following validation errors:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Transaction Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-amber-700 via-slate-800 to-slate-900 px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-pen-to-square text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white">EDIT TRANSACTION & VOUCHER</h3>
                    <p class="text-[11px] text-amber-200">Transaction code is immutable to preserve financial audit trail.</p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] text-amber-200 uppercase tracking-wider block font-semibold">Voucher Code (Immutable)</span>
                <span class="text-sm font-mono font-bold bg-black/40 px-2.5 py-0.5 rounded border border-amber-500/40 text-amber-300">
                    {{ $transaction->transaction_code }}
                </span>
            </div>
        </div>

        <form action="{{ route('project.transactions.update', [$project->id, $transaction->id]) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- 1. Booking Selection -->
            <div>
                <label for="booking_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Customer / Property Booking <span class="text-rose-500">*</span>
                </label>
                <select id="booking_id" name="booking_id" x-model="selectedBookingId" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition cursor-pointer">
                    <option value="" disabled>-- Select Flat / Customer Booking --</option>
                    @foreach($bookings as $b)
                        <option value="{{ $b->id }}" {{ old('booking_id', $transaction->booking_id) == $b->id ? 'selected' : '' }}>
                            🏢 Unit: {{ $b->unit_no }} - {{ $b->customer_salutation }} {{ $b->customer_name }} ({{ $b->booking_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Live Customer Due Balance Matrix -->
            <div x-show="activeBooking" class="bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900 text-white p-5 rounded-2xl shadow-md border border-slate-800 space-y-4">
                
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-2.5">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-scale-balanced text-emerald-400"></i>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-300">Current Balance Ledger Matrix</h4>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-mono font-bold bg-emerald-950 px-2.5 py-0.5 rounded text-emerald-300 border border-emerald-700">
                            Booking Value: ₹<span x-text="formatNumber(activeBooking?.bookingAmount)"></span>
                        </span>
                        <span class="text-xs font-mono font-bold bg-sky-900/80 px-2 py-0.5 rounded text-sky-200" x-text="'Unit: ' + activeBooking?.unit"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                    
                    <!-- Taxable Bank Stream -->
                    <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 space-y-1">
                        <div class="text-[10px] text-emerald-300 uppercase font-bold tracking-wider">Taxable Banking Stream</div>
                        <div class="text-slate-400">Target: <span class="text-white font-semibold" x-text="'₹' + formatNumber(activeBooking?.grossTaxable)"></span></div>
                        <div class="text-slate-400">Paid: <span class="text-emerald-400 font-semibold" x-text="'₹' + formatNumber(activeBooking?.taxableReceived)"></span></div>
                        <div class="pt-1 border-t border-slate-700 text-white font-bold flex items-center justify-between">
                            <span>Taxable Due:</span>
                            <span class="text-amber-300" x-text="'₹' + formatNumber(activeBooking?.taxableDue)"></span>
                        </div>
                    </div>

                    <!-- Non-Taxable Cash Stream -->
                    <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 space-y-1">
                        <div class="text-[10px] text-amber-300 uppercase font-bold tracking-wider">Non-Taxable Cash Stream</div>
                        <div class="text-slate-400">Target: <span class="text-white font-semibold" x-text="'₹' + formatNumber(activeBooking?.grossCash)"></span></div>
                        <div class="text-slate-400">Paid: <span class="text-amber-400 font-semibold" x-text="'₹' + formatNumber(activeBooking?.cashReceived)"></span></div>
                        <div class="pt-1 border-t border-slate-700 text-white font-bold flex items-center justify-between">
                            <span>Cash Due:</span>
                            <span class="text-amber-300" x-text="'₹' + formatNumber(activeBooking?.cashDue)"></span>
                        </div>
                    </div>

                    <!-- Total Outstanding -->
                    <div class="bg-emerald-950/60 p-3 rounded-xl border border-emerald-800 space-y-1 flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] text-emerald-400 uppercase font-bold tracking-wider">Total Customer Due</div>
                            <div class="text-2xl font-black text-white mt-1" x-text="'₹' + formatNumber(activeBooking?.totalDue)"></div>
                        </div>
                        <template x-if="activeBooking?.penalties > 0">
                            <div class="text-[10px] text-rose-300">
                                (Includes ₹<span x-text="formatNumber(activeBooking?.penalties)"></span> Bounced Cheque Penalties)
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Quick Fill Buttons -->
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                    <span class="text-slate-400 font-sans text-[11px]">Quick Amount Fill:</span>
                    <button type="button" @click="fillAmount(activeBooking?.taxableDue); voucherType = 'money_receipt'; txMode = 'neft'"
                            class="px-2.5 py-1 rounded bg-emerald-900/60 hover:bg-emerald-800 text-emerald-200 border border-emerald-700 text-[11px] font-mono transition">
                        Pay Taxable Due (₹<span x-text="formatNumber(activeBooking?.taxableDue)"></span>)
                    </button>
                    <button type="button" @click="fillAmount(activeBooking?.cashDue); voucherType = 'receipt_voucher'; txMode = 'cash'"
                            class="px-2.5 py-1 rounded bg-amber-900/60 hover:bg-amber-800 text-amber-200 border border-amber-700 text-[11px] font-mono transition">
                        Pay Cash Due (₹<span x-text="formatNumber(activeBooking?.cashDue)"></span>)
                    </button>
                </div>

            </div>

            <!-- 3. Voucher Classification -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Voucher Type & Accounting Stream <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    
                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer transition"
                           :class="voucherType === 'money_receipt' ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                        <input type="radio" name="voucher_type" value="money_receipt" x-model="voucherType" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs block">Money Receipt (Taxable)</span>
                            <span class="text-[10px] text-slate-400 font-normal">Sale Agreement & GST</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer transition"
                           :class="voucherType === 'receipt_voucher' ? 'bg-amber-50 border-amber-500 text-amber-900 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                        <input type="radio" name="voucher_type" value="receipt_voucher" x-model="voucherType" class="text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="text-xs block">Receipt Voucher (Cash)</span>
                            <span class="text-[10px] text-slate-400 font-normal">Job Sheets & Cash Split</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer transition"
                           :class="voucherType === 'payment_voucher' ? 'bg-rose-50 border-rose-500 text-rose-900 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                        <input type="radio" name="voucher_type" value="payment_voucher" x-model="voucherType" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="text-xs block">Payment / Refund Outflow</span>
                            <span class="text-[10px] text-slate-400 font-normal">Refunds & Outward Payouts</span>
                        </div>
                    </label>

                </div>
            </div>

            <!-- 3.1 Payment Category (Required when Voucher Type is Payment) -->
            <div x-show="voucherType === 'payment_voucher'" x-transition class="p-4 bg-rose-50/80 rounded-2xl border border-rose-200 space-y-2">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-tags text-rose-600 text-xs"></i>
                    <label class="block text-xs font-bold uppercase tracking-wider text-rose-900">
                        Payment Category – Taxable / Non - Taxable <span class="text-rose-600">*</span>
                    </label>
                </div>
                <p class="text-[11px] text-rose-700">Select whether this payment / refund is deducted against the Taxable Agreement & Bank stream or Non-Taxable Cash stream.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer bg-white transition"
                           :class="paymentCategory === 'taxable' ? 'border-rose-500 ring-2 ring-rose-500/20 shadow-sm text-rose-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                        <input type="radio" name="payment_category" value="taxable" x-model="paymentCategory" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="text-xs block">Taxable Payment / Refund</span>
                            <span class="text-[10px] text-slate-500 font-normal">Agreement Consideration & Bank Escrow Account</span>
                        </div>
                    </label>
                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer bg-white transition"
                           :class="paymentCategory === 'non_taxable' ? 'border-rose-500 ring-2 ring-rose-500/20 shadow-sm text-rose-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                        <input type="radio" name="payment_category" value="non_taxable" x-model="paymentCategory" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="text-xs block">Non-Taxable Payment / Refund</span>
                            <span class="text-[10px] text-slate-500 font-normal">Cash Payout / Supplementary Job Sheet Split</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 4. Payment Parameters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Voucher Date -->
                <div>
                    <label for="voucher_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Voucher Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="voucher_date" name="voucher_date" value="{{ old('voucher_date', $transaction->voucher_date?->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>

                <!-- Voucher No. (Manual User Input) -->
                <div>
                    <label for="voucher_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Voucher No.
                    </label>
                    <input type="text" id="voucher_no" name="voucher_no" value="{{ old('voucher_no', $transaction->voucher_no) }}"
                           placeholder="e.g. VCH-00123 / Book No. 4"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>

                <!-- Transaction Amount -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="amount" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Amount (₹) <span class="text-rose-500">*</span>
                        </label>
                        <template x-if="activeBooking">
                            <span class="text-[10px] text-slate-500 font-mono font-semibold">
                                Max: ₹<span x-text="formatNumber(activeBooking.bookingAmount)"></span>
                            </span>
                        </template>
                    </div>
                    <input type="number" step="0.01" id="amount" name="amount" x-model.number="enteredAmount" required min="0.01"
                           :max="activeBooking ? activeBooking.bookingAmount : null"
                           placeholder="0.00"
                           class="w-full px-3.5 py-2 bg-slate-50 border rounded-xl text-sm font-mono font-black focus:outline-none transition"
                           :class="activeBooking && enteredAmount > activeBooking.bookingAmount ? 'border-rose-500 text-rose-700 bg-rose-50 focus:ring-2 focus:ring-rose-500' : 'border-slate-200 text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500'">
                    <template x-if="activeBooking && enteredAmount > activeBooking.bookingAmount">
                        <p class="text-[11px] text-rose-600 font-bold mt-1 flex items-center space-x-1">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            <span>Amount cannot be greater than the booking amount (₹<span x-text="formatNumber(activeBooking.bookingAmount)"></span>).</span>
                        </p>
                    </template>
                </div>

                <!-- Payment Mode -->
                <div>
                    <label for="transaction_mode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payment Mode <span class="text-rose-500">*</span>
                    </label>
                    <select id="transaction_mode" name="transaction_mode" x-model="txMode" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        <option value="neft">NEFT / RTGS</option>
                        <option value="cheque">Cheque</option>
                        <option value="upi">UPI / Online Transfer</option>
                        <option value="dd">Demand Draft (DD)</option>
                        <option value="cash">Cash in Hand</option>
                        <option value="bank_transfer">Direct Bank Transfer</option>
                    </select>
                </div>

                <!-- Bank Account / Cash Ledger -->
                <div class="sm:col-span-2">
                    <label for="bank_account_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Crediting / Debiting Account
                    </label>
                    <select id="bank_account_id" name="bank_account_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        <option value="">-- Cash Account / Cash in Hand --</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('bank_account_id', $transaction->bank_account_id) == $acc->id ? 'selected' : '' }}>
                                🏦 {{ $acc->account_nick_name }} ({{ $acc->bank_name }} - {{ $acc->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Source of Payment -->
                <div>
                    <label for="source_of_payment" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payment Source <span class="text-rose-500">*</span>
                    </label>
                    <select id="source_of_payment" name="source_of_payment" x-model="paymentSource" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        <option value="self">Self (Direct Customer Payment)</option>
                        <option value="through_loan_account">Through Home Loan Account (Bank Disbursement)</option>
                    </select>
                </div>

            </div>

            <!-- 5. Instrument Details (Cheque, DD, UTR Reference) -->
            <div x-show="txMode !== 'cash'" class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-700 pb-1 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-money-check text-sky-600"></i>
                        <span>Banking Instrument / UTR Reference Particulars</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-mono">Current Status: {{ strtoupper(str_replace('_', ' ', $transaction->instrument_status)) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- Instrument Ref / Cheque No -->
                    <div>
                        <label for="instrument_ref_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Cheque / UTR / Txn No
                        </label>
                        <input type="text" id="instrument_ref_no" name="instrument_ref_no" value="{{ old('instrument_ref_no', $transaction->instrument_ref_no) }}"
                               placeholder="e.g. CHQ-654321 or UTR-987654"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Instrument Date -->
                    <div>
                        <label for="instrument_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Instrument Date
                        </label>
                        <input type="date" id="instrument_date" name="instrument_date" value="{{ old('instrument_date', $transaction->instrument_date?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Issuing Bank -->
                    <div>
                        <label for="issuing_bank" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Customer / Issuing Bank
                        </label>
                        <input type="text" id="issuing_bank" name="issuing_bank" value="{{ old('issuing_bank', $transaction->issuing_bank) }}"
                               placeholder="e.g. State Bank of India"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Issuing Branch -->
                    <div>
                        <label for="issuing_branch" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Issuing Branch
                        </label>
                        <input type="text" id="issuing_branch" name="issuing_branch" value="{{ old('issuing_branch', $transaction->issuing_branch) }}"
                               placeholder="e.g. Silchar Main"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Instrument Clearance Status -->
                    <div class="sm:col-span-2">
                        <label for="instrument_status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Instrument Status
                        </label>
                        <select id="instrument_status" name="instrument_status" x-model="instrumentStatus"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            <option value="cleared">Cleared (Funds Settled)</option>
                            <option value="pending_clearance">Pending Clearance</option>
                            <option value="dishonored">Dishonored / Bounced</option>
                            <option value="not_applicable">Not Applicable</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- 6. Particulars / Notes -->
            <div>
                <label for="particulars" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Particulars / Transaction Narrative
                </label>
                <textarea id="particulars" name="particulars" rows="2"
                          placeholder="e.g. Part payment received towards 2nd installment of Sale Agreement"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">{{ old('particulars', $transaction->particulars) }}</textarea>
            </div>

            <!-- Submit Actions -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <span class="text-[11px] text-slate-400">
                    <i class="fa-solid fa-shield-halved mr-1 text-slate-400"></i>
                    Updates will automatically recalculate customer ledger and balance due metrics.
                </span>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('project.transactions.index', $project->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                        Cancel
                    </a>
                    <button type="submit"
                            :disabled="activeBooking && enteredAmount > activeBooking.bookingAmount"
                            class="px-6 py-2.5 text-white text-xs font-semibold rounded-xl shadow-md transition flex items-center space-x-2"
                            :class="activeBooking && enteredAmount > activeBooking.bookingAmount ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20'">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>
@endsection
