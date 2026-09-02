@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="hover:text-sky-600">{{ $booking->booking_code }}</a>
                <span>/</span>
                <span class="text-rose-600">Cancellation Refund</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Booking Cancellation Split Refund Settlement</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage split refund payouts to Lending Bank (Home Loan) and Purchaser (Bank vs Cash ledgers).</p>
        </div>
        <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Overview
        </a>
    </div>

    <!-- Settlement Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-rose-700 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-hand-holding-dollar text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">CANCELLATION SETTLEMENT: {{ $booking->customer_name }}</h3>
                    <p class="text-[11px] text-rose-200">Unit: {{ $booking->unit_no }} | Cancelled on {{ $booking->cancellation_date?->format('d M, Y') ?? 'N/A' }}</p>
                </div>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $refund->status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-950' }}">
                {{ ucfirst(str_replace('_', ' ', $refund->status)) }}
            </span>
        </div>

        <!-- Settlement Calculations Breakdown Grid -->
        <div class="p-6 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-mono">
            
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider font-sans block">Total Bank Received</span>
                <span class="text-base font-bold text-slate-800">₹{{ number_format($refund->total_received_bank, 2) }}</span>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider font-sans block">Total Cash Received</span>
                <span class="text-base font-bold text-slate-800">₹{{ number_format($refund->total_received_cash, 2) }}</span>
            </div>

            <div class="bg-rose-50 p-3.5 rounded-xl border border-rose-200 space-y-1">
                <span class="text-[10px] text-rose-600 uppercase font-bold tracking-wider font-sans block">Cancellation Fee Deduction</span>
                <span class="text-base font-black text-rose-700">-₹{{ number_format($refund->cancellation_fee, 2) }}</span>
            </div>

            <div class="bg-emerald-50 p-3.5 rounded-xl border border-emerald-200 space-y-1">
                <span class="text-[10px] text-emerald-700 uppercase font-bold tracking-wider font-sans block">Net Payable Refund</span>
                <span class="text-lg font-black text-emerald-800">
                    ₹{{ number_format(max(0, (float)$refund->total_received_bank + (float)$refund->total_received_cash - (float)$refund->cancellation_fee), 2) }}
                </span>
            </div>

        </div>

        <!-- Split Progress Details -->
        <div class="p-6 border-b border-slate-200 space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Split Payout Progress</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-mono">
                
                <!-- Priority 1: Bank Loan -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-slate-700 font-bold font-sans">
                        <span>1. Lending Bank Loan</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 font-mono">Priority 1</span>
                    </div>
                    <div class="text-slate-500">Refundable: <span class="font-bold text-slate-800">₹{{ number_format($refund->refundable_to_bank_loan, 2) }}</span></div>
                    <div class="text-emerald-700">Paid: <span class="font-bold">₹{{ number_format($refund->refunded_to_bank_loan, 2) }}</span></div>
                    <div class="text-rose-600 font-bold pt-1 border-t border-slate-200">
                        Pending: ₹{{ number_format(max(0, $refund->refundable_to_bank_loan - $refund->refunded_to_bank_loan), 2) }}
                    </div>
                </div>

                <!-- Priority 2: Customer Bank (Taxable) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-slate-700 font-bold font-sans">
                        <span>2. Customer Bank</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-mono">Priority 2</span>
                    </div>
                    <div class="text-slate-500">Refundable: <span class="font-bold text-slate-800">₹{{ number_format($refund->refundable_to_party_taxable, 2) }}</span></div>
                    <div class="text-emerald-700">Paid: <span class="font-bold">₹{{ number_format($refund->refunded_to_party_taxable, 2) }}</span></div>
                    <div class="text-rose-600 font-bold pt-1 border-t border-slate-200">
                        Pending: ₹{{ number_format(max(0, $refund->refundable_to_party_taxable - $refund->refunded_to_party_taxable), 2) }}
                    </div>
                </div>

                <!-- Priority 3: Customer Cash -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-slate-700 font-bold font-sans">
                        <span>3. Customer Cash</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-mono">Priority 3</span>
                    </div>
                    <div class="text-slate-500">Refundable: <span class="font-bold text-slate-800">₹{{ number_format($refund->refundable_to_party_cash, 2) }}</span></div>
                    <div class="text-emerald-700">Paid: <span class="font-bold">₹{{ number_format($refund->refunded_to_party_cash, 2) }}</span></div>
                    <div class="text-rose-600 font-bold pt-1 border-t border-slate-200">
                        Pending: ₹{{ number_format(max(0, $refund->refundable_to_party_cash - $refund->refunded_to_party_cash), 2) }}
                    </div>
                </div>

            </div>
        </div>

        <!-- Process Refund Payout Form -->
        <form action="{{ route('project.bookings.cancellation-refund.store', [$project->id, $booking->id]) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 pb-2 border-b border-slate-100 flex items-center space-x-2">
                <i class="fa-solid fa-money-bill-transfer text-rose-600"></i>
                <span>Issue Refund Payout Voucher</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Target Recipient -->
                <div>
                    <label for="refund_target" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Refund Target Stream <span class="text-rose-500">*</span>
                    </label>
                    <select id="refund_target" name="refund_target" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                        <option value="bank_loan">1. Lending Bank Home Loan Account</option>
                        <option value="party_taxable">2. Customer Bank Account (Taxable Refund)</option>
                        <option value="party_cash">3. Customer Cash Refund</option>
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payout Amount (₹) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" id="amount" name="amount" required min="0.01"
                           placeholder="0.00"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-rose-700 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                </div>

                <!-- Date -->
                <div>
                    <label for="refund_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payout Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="refund_date" name="refund_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                </div>

                <!-- Payment Mode -->
                <div>
                    <label for="payment_mode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payment Mode <span class="text-rose-500">*</span>
                    </label>
                    <select id="payment_mode" name="payment_mode" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="cheque">Cheque</option>
                        <option value="dd">Demand Draft (DD)</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <!-- Bank Account -->
                <div>
                    <label for="bank_account_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Debiting Bank Account
                    </label>
                    <select id="bank_account_id" name="bank_account_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                        <option value="">-- Cash Account / Cash in Hand --</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">
                                🏦 {{ $acc->account_nick_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Ref No -->
                <div>
                    <label for="ref_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Cheque / UTR Ref No
                    </label>
                    <input type="text" id="ref_no" name="ref_no" placeholder="e.g. UTR-12345678"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                </div>

                <!-- Remarks -->
                <div class="sm:col-span-3">
                    <label for="remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Settlement Narrative
                    </label>
                    <textarea id="remarks" name="remarks" rows="2"
                              placeholder="e.g. Settlement installment 1 paid via NEFT to customer SBI account"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition"></textarea>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-rose-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Issue Payment Outflow Voucher</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
