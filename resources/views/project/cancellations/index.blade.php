@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    currentStatus: '{{ $booking->status === 'cancelled' ? 'cancelled' : 'live' }}',
    bookingDate: '{{ $booking->booking_date->format('Y-m-d') }}',
    statusDate: '{{ $booking->cancellation_date?->format('Y-m-d') ?? date('Y-m-d') }}',
    loanInst: {{ (float)$loanInstruction }},
    taxInst: {{ (float)$taxableInstruction }},
    cashInst: {{ (float)$cashInstruction }},
    loanRec: {{ (float)$loanReceived }},
    taxRec: {{ (float)$selfTaxableReceived }},
    cashRec: {{ (float)$selfCashReceived }},
    loanRef: {{ (float)$loanRefunded }},
    taxRef: {{ (float)$selfTaxableRefunded }},
    cashRef: {{ (float)$selfCashRefunded }},

    get totalTaxableInst() {
        return Math.round((parseFloat(this.loanInst || 0) + parseFloat(this.taxInst || 0)) * 100) / 100;
    },
    get grossInst() {
        return Math.round((this.totalTaxableInst + parseFloat(this.cashInst || 0)) * 100) / 100;
    },
    get chargeLoan() {
        return Math.max(0, Math.round((this.loanRec - parseFloat(this.loanInst || 0)) * 100) / 100);
    },
    get chargeTax() {
        return Math.max(0, Math.round((this.taxRec - parseFloat(this.taxInst || 0)) * 100) / 100);
    },
    get chargeCash() {
        return Math.max(0, Math.round((this.cashRec - parseFloat(this.cashInst || 0)) * 100) / 100);
    },
    get totalCharge() {
        return Math.round((this.chargeLoan + this.chargeTax + this.chargeCash) * 100) / 100;
    },
    get daysGap() {
        if (!this.bookingDate || !this.statusDate) return 0;
        const b = new Date(this.bookingDate);
        const s = new Date(this.statusDate);
        const diffTime = s - b;
        return Math.max(0, Math.floor(diffTime / (1000 * 60 * 60 * 24)));
    }
}">

    <!-- Stage 2.1.6 Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="hover:text-sky-600">{{ $booking->booking_code }}</a>
                <span>/</span>
                <span class="text-rose-600 font-bold">Stage 2.1.6: Booking Cancellation</span>
            </nav>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">2.1.6. Booking Cancellation & Refund Reconciliation</h1>
                @if($booking->status === 'cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-rose-600 text-white shadow-sm animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-white mr-1.5 animate-ping"></span>
                        CANCELLED
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-white shadow-sm animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-white mr-1.5 animate-ping"></span>
                        LIVE
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Back-calculate received receipts across Loan, Taxable Self, and Cash accounts, record refund instructions, and enforce reactivation safeguards.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Booking</span>
            </a>
        </div>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center space-x-1.5">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Action Blocked / Validation Error</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- SELECT PROJECT & CUSTOMER BAR -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Select Project -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Select Project <span class="text-rose-500">*</span>
            </label>
            <select onchange="window.location.href='/project/' + this.value + '/cancellations'"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ $p->id === $project->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->project_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Select Customer / Booking -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Select Customer <span class="text-rose-500">*</span>
            </label>
            <select onchange="window.location.href='{{ route('project.cancellations.index', $project->id) }}?booking_id=' + this.value"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                @foreach($projectBookings as $b)
                    <option value="{{ $b->id }}" {{ $b->id === $booking->id ? 'selected' : '' }}>
                        {{ $b->customer_name }} [Unit: {{ $b->unit_no }}] ({{ $b->booking_code }}) - {{ strtoupper($b->status) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- READONLY STAGE FINANCIAL SUMMARY CARD -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-md border border-slate-700/80">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700">
            <span class="text-xs font-bold uppercase tracking-wider text-sky-400">
                Booking Financial Summary (Auto-Generated)
            </span>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-mono text-slate-300">Booking ID: <strong class="text-white">{{ $booking->booking_code }}</strong></span>
                <span class="text-slate-500">|</span>
                <span class="text-xs font-mono text-slate-300">Booking Date: <strong class="text-white">{{ $booking->booking_date->format('d/m/Y') }}</strong></span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 pt-4 text-xs font-mono">
            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-slate-400 block">Consideration Val</span>
                <span class="text-sm font-bold text-white">₹{{ number_format($booking->consideration_value, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-slate-400 block">Sale Agreement Val</span>
                <span class="text-sm font-bold text-white">₹{{ number_format($booking->taxable_agreement_value, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-slate-400 block">Taxable Value</span>
                <span class="text-sm font-bold text-white">₹{{ number_format($booking->taxable_agreement_value, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-slate-400 block">Tax Value (GST)</span>
                <span class="text-sm font-bold text-white">₹{{ number_format($booking->tax_amount, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-emerald-400 block">Add-ons (+)</span>
                <span class="text-sm font-bold text-emerald-300">₹{{ number_format($booking->customizations()->where('job_type', 'addon')->sum('job_total'), 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-amber-400 block">Dislodges (-)</span>
                <span class="text-sm font-bold text-amber-300">₹{{ number_format($booking->customizations()->where('job_type', 'dislodge')->sum('job_total'), 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-indigo-400 block">Supplement Cost</span>
                <span class="text-sm font-bold text-indigo-200">{{ $booking->supplementary_value >= 0 ? '+' : '' }}₹{{ number_format($booking->supplementary_value, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-sky-400 block">Gross Booking Val</span>
                <span class="text-sm font-bold text-sky-200">₹{{ number_format($booking->gross_booking_value ?: $booking->total_booking_value, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-amber-300 block">Adjustments</span>
                <span class="text-sm font-bold text-amber-300">-₹{{ number_format($booking->adjustments, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10">
                <span class="text-[10px] uppercase font-sans text-emerald-400 block">Final Booking Val</span>
                <span class="text-sm font-black text-emerald-400">₹{{ number_format($booking->final_booking_value ?: $booking->total_booking_value, 2) }}</span>
            </div>

            <div class="bg-white/5 p-3 rounded-xl border border-white/10 sm:col-span-2">
                <span class="text-[10px] uppercase font-sans text-sky-300 block">Gross Taxable Value</span>
                <span class="text-sm font-bold text-white">₹{{ number_format($booking->gross_taxable_value, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- STATUS TOGGLE & FORM SECTION -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                <i class="fa-solid fa-power-off text-rose-600"></i>
                <span>Booking Status Management</span>
            </h3>
            <div class="text-xs font-mono">
                Gap from Booking: <span class="font-bold text-rose-600" x-text="daysGap + ' days'">{{ $daysGap }} days</span>
            </div>
        </div>

        <form action="{{ route('project.bookings.cancellation.status', [$project->id, $booking->id]) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Status Dropdown -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Booking Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" x-model="currentStatus" required
                            class="w-full px-3.5 py-2.5 rounded-xl border text-xs font-bold transition focus:ring-2 focus:outline-none"
                            :class="currentStatus === 'cancelled' ? 'bg-rose-50 border-rose-300 text-rose-800 focus:ring-rose-500' : 'bg-emerald-50 border-emerald-300 text-emerald-800 focus:ring-emerald-500'">
                        <option value="live">Live</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Status Updated On -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Status Updated On <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="cancellation_date" x-model="statusDate" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">Auto calculated gap: <span class="font-bold text-slate-700" x-text="daysGap + ' days'">{{ $daysGap }} days</span></span>
                </div>

                <!-- Remarks / Reason -->
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Remarks / Reason for Cancellation or Status Update <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="cancellation_remarks" rows="2" required
                              placeholder="e.g. Customer requested cancellation due to personal financial hardship. Cancellation charge applied as agreed."
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">{{ old('cancellation_remarks', $booking->cancellation_remarks) }}</textarea>
                </div>
            </div>

            <!-- 3-ACCOUNT RECONCILIATION TABLE -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            Refund Instruction & Payout Matrix (3 Accounts)
                        </h4>
                        <p class="text-[11px] text-slate-500">
                            Total amount generated from previous stages. Received amount calculated from cleared receipts.
                        </p>
                    </div>
                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <i class="fa-solid fa-circle-info mr-1"></i> Refund instruction must be &ge; Refunded amt.
                    </span>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="text-[11px] font-bold uppercase text-slate-500 bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Particular</th>
                                <th class="py-3 px-4 font-mono">Total Amt.</th>
                                <th class="py-3 px-4 font-mono">Received Amt.</th>
                                <th class="py-3 px-4 font-mono w-44">Refund Instruction</th>
                                <th class="py-3 px-4 font-mono">Refunded Amt.</th>
                                <th class="py-3 px-4 font-mono text-rose-600">Cancellation Charge</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            <!-- (a) Loan a/c -->
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-sans font-bold text-slate-800">
                                    (a) Loan a/c
                                </td>
                                <td class="py-3 px-4 text-slate-600">₹{{ number_format($loanTotal, 2) }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">₹{{ number_format($loanReceived, 2) }}</td>
                                <td class="py-2 px-4">
                                    <input type="number" step="0.01" name="loan_refund_instruction" x-model.number="loanInst" min="{{ $loanRefunded }}"
                                           class="w-full px-2.5 py-1.5 bg-amber-50/60 border border-amber-300 rounded-lg text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-700">₹{{ number_format($loanRefunded, 2) }}</td>
                                <td class="py-3 px-4 font-black text-rose-600" x-text="'₹' + chargeLoan.toFixed(2)">
                                    ₹{{ number_format(max(0, $loanReceived - $loanInstruction), 2) }}
                                </td>
                            </tr>

                            <!-- (b) Self (taxable) a/c -->
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-sans font-bold text-slate-800">
                                    (b) Self (taxable) a/c
                                </td>
                                <td class="py-3 px-4 text-slate-600">₹{{ number_format($selfTaxableTotal, 2) }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">₹{{ number_format($selfTaxableReceived, 2) }}</td>
                                <td class="py-2 px-4">
                                    <input type="number" step="0.01" name="taxable_refund_instruction" x-model.number="taxInst" min="{{ $selfTaxableRefunded }}"
                                           class="w-full px-2.5 py-1.5 bg-amber-50/60 border border-amber-300 rounded-lg text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-700">₹{{ number_format($selfTaxableRefunded, 2) }}</td>
                                <td class="py-3 px-4 font-black text-rose-600" x-text="'₹' + chargeTax.toFixed(2)">
                                    ₹{{ number_format(max(0, $selfTaxableReceived - $taxableInstruction), 2) }}
                                </td>
                            </tr>

                            <!-- (c) Total Taxable a/c (a+b) -->
                            <tr class="bg-slate-50/90 font-bold border-y border-slate-200 text-slate-900">
                                <td class="py-3 px-4 font-sans text-sky-800">
                                    (c) Total Taxable a/c (a+b)
                                </td>
                                <td class="py-3 px-4">₹{{ number_format($totalTaxableTotal, 2) }}</td>
                                <td class="py-3 px-4">₹{{ number_format($totalTaxableReceived, 2) }}</td>
                                <td class="py-3 px-4 text-sky-800 font-black" x-text="'₹' + totalTaxableInst.toFixed(2)">
                                    ₹{{ number_format($totalTaxableInstruction, 2) }}
                                </td>
                                <td class="py-3 px-4 text-emerald-700">₹{{ number_format($totalTaxableRefunded, 2) }}</td>
                                <td class="py-3 px-4 text-rose-600 font-black" x-text="'₹' + (chargeLoan + chargeTax).toFixed(2)">
                                    ₹{{ number_format(max(0, $totalTaxableReceived - $totalTaxableInstruction), 2) }}
                                </td>
                            </tr>

                            <!-- (d) Self (non-taxable) a/c -->
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-sans font-bold text-slate-800">
                                    (d) Self (non-taxable) a/c
                                </td>
                                <td class="py-3 px-4 text-slate-600">₹{{ number_format($selfCashTotal, 2) }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">₹{{ number_format($selfCashReceived, 2) }}</td>
                                <td class="py-2 px-4">
                                    <input type="number" step="0.01" name="cash_refund_instruction" x-model.number="cashInst" min="{{ $selfCashRefunded }}"
                                           class="w-full px-2.5 py-1.5 bg-amber-50/60 border border-amber-300 rounded-lg text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-700">₹{{ number_format($selfCashRefunded, 2) }}</td>
                                <td class="py-3 px-4 font-black text-rose-600" x-text="'₹' + chargeCash.toFixed(2)">
                                    ₹{{ number_format(max(0, $selfCashReceived - $cashInstruction), 2) }}
                                </td>
                            </tr>

                            <!-- (e) Gross Total (c+d) -->
                            <tr class="bg-slate-900 text-white font-bold border-t-2 border-slate-900 text-sm">
                                <td class="py-3.5 px-4 font-sans text-sky-400">
                                    (e) Gross Total (c+d)
                                </td>
                                <td class="py-3.5 px-4 font-mono">₹{{ number_format($grossTotal, 2) }}</td>
                                <td class="py-3.5 px-4 font-mono text-emerald-400">₹{{ number_format($grossReceived, 2) }}</td>
                                <td class="py-3.5 px-4 font-mono text-amber-300 font-black" x-text="'₹' + grossInst.toFixed(2)">
                                    ₹{{ number_format($grossInstruction, 2) }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-emerald-300">₹{{ number_format($grossRefunded, 2) }}</td>
                                <td class="py-3.5 px-4 font-mono text-rose-400 font-black" x-text="'₹' + totalCharge.toFixed(2)">
                                    ₹{{ number_format(max(0, $grossReceived - $grossInstruction), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Net Cancellation Charge: <span class="font-bold text-rose-600 font-mono" x-text="'₹' + totalCharge.toFixed(2)">₹{{ number_format(max(0, $grossReceived - $grossInstruction), 2) }}</span>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="submit"
                            :class="currentStatus === 'cancelled' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="px-6 py-2.5 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span x-text="currentStatus === 'cancelled' ? 'Confirm Cancellation & Save Instructions' : 'Update Status to LIVE'">
                            Update Status & Instructions
                        </span>
                    </button>
                </div>
            </div>

        </form>

    </div>

    <!-- PAYOUT EXECUTION VOUCHER FORM (When Cancelled) -->
    @if($booking->status === 'cancelled')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <div class="bg-gradient-to-r from-rose-700 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-money-bill-transfer text-white"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">Execute Refund Payout Voucher</h4>
                        <p class="text-[11px] text-rose-200">Disburse refunds to Lending Bank, Customer Bank (Taxable), or Customer Cash account</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $refund->status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-950' }}">
                    {{ ucfirst(str_replace('_', ' ', $refund->status)) }}
                </span>
            </div>

            <form action="{{ route('project.bookings.cancellation.payout', [$project->id, $booking->id]) }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Target Head -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Target Account Stream <span class="text-rose-500">*</span>
                        </label>
                        <select name="refund_target" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            <option value="bank_loan">1. Loan A/C (Pending: ₹{{ number_format(max(0, $loanInstruction - $loanRefunded), 2) }})</option>
                            <option value="party_taxable">2. Party-Taxable A/C (Pending: ₹{{ number_format(max(0, $taxableInstruction - $selfTaxableRefunded), 2) }})</option>
                            <option value="party_cash">3. Party Cash A/C (Pending: ₹{{ number_format(max(0, $cashInstruction - $selfCashRefunded), 2) }})</option>
                        </select>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Payout Amount (₹) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="amount" required min="0.01" placeholder="0.00"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-rose-700 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Payout Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="refund_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                    </div>

                    <!-- Payment Mode -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Payment Mode <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_mode" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="cheque">Cheque</option>
                            <option value="dd">Demand Draft (DD)</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <!-- Debiting Bank Account -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Debiting Bank Account
                        </label>
                        <select name="bank_account_id"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            <option value="">-- Cash Account / Cash in Hand --</option>
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">🏦 {{ $acc->account_nick_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ref No -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Cheque / UTR Ref No
                        </label>
                        <input type="text" name="ref_no" placeholder="e.g. UTR-98765432"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                    </div>

                    <!-- Remarks -->
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Payout Remarks / Reference
                        </label>
                        <input type="text" name="remarks" placeholder="e.g. Refund installment 1 issued via NEFT"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Issue Payment Voucher</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection
