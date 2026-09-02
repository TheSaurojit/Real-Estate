@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="hover:text-sky-600">{{ $booking->booking_code }}</a>
                <span>/</span>
                <span class="text-indigo-600">Cross-Ledger Adjustment</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Cross-Ledger Overpayment Rebalance Wizard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rebalance excess collections in the Taxable Banking stream to the Non-Taxable Cash ledger with full legal GST audit safety.</p>
        </div>
        <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Overview
        </a>
    </div>

    @php
        $bankExcess = max(0, round($booking->total_taxable_received - (float)$booking->gross_taxable_value, 2));
    @endphp

    <!-- Wizard Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-indigo-700 to-purple-800 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-scale-balanced text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">REBALANCE LEDGER: {{ $booking->customer_name }}</h3>
                    <p class="text-[11px] text-indigo-200">Unit: {{ $booking->unit_no }} | Code: {{ $booking->booking_code }}</p>
                </div>
            </div>
        </div>

        <!-- Ledger Diagnostics Summary -->
        <div class="p-6 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
            
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider font-sans block">Taxable Target (Agr + GST)</span>
                <span class="text-lg font-bold text-slate-800">₹{{ number_format($booking->gross_taxable_value, 2) }}</span>
                <div class="text-[11px] text-slate-500">Collected in Bank: ₹{{ number_format($booking->total_taxable_received, 2) }}</div>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider font-sans block">Current Cash Target / Due</span>
                <span class="text-lg font-bold text-slate-800">₹{{ number_format($booking->gross_cash_value, 2) }}</span>
                <div class="text-[11px] text-amber-600">Cash Due: ₹{{ number_format($booking->cash_due_balance, 2) }}</div>
            </div>

            <div class="bg-indigo-50 p-3.5 rounded-xl border border-indigo-200 space-y-1">
                <span class="text-[10px] text-indigo-700 uppercase font-bold tracking-wider font-sans block">Detected Bank Excess</span>
                <span class="text-xl font-black text-indigo-800">₹{{ number_format($bankExcess, 2) }}</span>
                <div class="text-[11px] text-indigo-600">Available to transfer to Cash</div>
            </div>

        </div>

        <form action="{{ route('project.bookings.adjustment.store', [$project->id, $booking->id]) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Explanation Callout -->
            <div class="p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-900 text-xs space-y-2">
                <div class="font-bold flex items-center space-x-1.5 text-sky-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>How Cross-Ledger Rebalancing Works:</span>
                </div>
                <p class="leading-relaxed text-sky-800">
                    When executed, the system will atomically generate <strong>two complementary records</strong>:
                </p>
                <ol class="list-decimal list-inside space-y-1 text-slate-700 font-medium">
                    <li>A <strong>Payment Outflow Voucher</strong> debiting the developer bank account for the excess amount.</li>
                    <li>A <strong>Receipt Inflow Voucher</strong> crediting the Cash Account towards the customer's cash balance.</li>
                </ol>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Adjustment Amount -->
                <div>
                    <label for="adjustment_amount" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Adjustment Amount (₹) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" id="adjustment_amount" name="adjustment_amount" value="{{ old('adjustment_amount', $bankExcess > 0 ? $bankExcess : 0) }}" required min="0.01"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-black text-indigo-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <!-- Adjustment Date -->
                <div>
                    <label for="adjustment_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Adjustment Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="adjustment_date" name="adjustment_date" value="{{ old('adjustment_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <!-- Bank Account -->
                <div class="sm:col-span-2">
                    <label for="bank_account_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Refunding / Source Developer Bank Account <span class="text-rose-500">*</span>
                    </label>
                    <select id="bank_account_id" name="bank_account_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">
                                🏦 {{ $acc->account_nick_name }} ({{ $acc->bank_name }} - {{ $acc->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Remarks -->
                <div class="sm:col-span-2">
                    <label for="remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Adjustment Narrative / Authorization Reason
                    </label>
                    <textarea id="remarks" name="remarks" rows="2"
                              placeholder="e.g. Agreement executed for ₹25L; rebalancing prior ₹30L bank deposit with ₹5L cash receipt"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">{{ old('remarks') }}</textarea>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Execute Cross-Ledger Rebalance</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
