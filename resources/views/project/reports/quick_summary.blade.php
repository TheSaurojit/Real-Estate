@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.reports.index', $project->id) }}" class="hover:text-sky-600">Reports</a>
                <span>/</span>
                <span class="text-indigo-600 font-bold">2.3.6 Quick Summary</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Quick Summary – Booking</h1>
            <p class="text-xs text-slate-500 mt-0.5">360° financial overview, multi-stream receipts breakdown, and 4-stream summary reconciliation matrix.</p>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Summary</span>
            </button>
            <a href="{{ route('project.reports.index', $project->id) }}"
               class="px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Reports Hub</span>
            </a>
        </div>
    </div>

    <!-- Booking / Customer Selector Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-center">
            <!-- Project Indicator -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Project</label>
                <div class="px-3 py-2 bg-slate-100 rounded-xl text-xs font-bold text-slate-800 flex items-center justify-between">
                    <span>{{ $project->name }}</span>
                    <span class="text-[10px] font-mono px-2 py-0.5 bg-slate-200 text-slate-700 rounded">{{ $project->project_code }}</span>
                </div>
            </div>

            <!-- Customer / Booking Dropdown -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Select Customer / Booking</label>
                <select id="bookingSelect" onchange="if(this.value) window.location.href='{{ route('project.reports.quick-summary', $project->id) }}?booking_id=' + this.value"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    @forelse($projectBookings as $pb)
                        <option value="{{ $pb->id }}" {{ $selectedBooking && $selectedBooking->id === $pb->id ? 'selected' : '' }}>
                            {{ $pb->customer_name }} — {{ $pb->booking_code }} (Unit: {{ $pb->unit_no }}{{ $pb->block_name ? ', Block: ' . $pb->block_name : '' }})
                        </option>
                    @empty
                        <option value="">No bookings available in this project</option>
                    @endforelse
                </select>
            </div>

            <!-- Booking Status Badge -->
            <div class="flex flex-col justify-end">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Booking Status</label>
                <div>
                    @if($selectedBooking)
                        @if($selectedBooking->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <i class="fa-solid fa-ban mr-1.5"></i> Cancelled Booking
                            </span>
                        @elseif($selectedBooking->status === 'registered_deed')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                <i class="fa-solid fa-stamp mr-1.5"></i> Registered Deed
                            </span>
                        @elseif($selectedBooking->status === 'executed_agreement')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                <i class="fa-solid fa-file-contract mr-1.5"></i> Executed Agreement
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fa-solid fa-circle-check mr-1.5"></i> Active / Live Booking
                            </span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">None Selected</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($selectedBooking)
        @php
            $b = $selectedBooking;
            $consideration = (float)$b->consideration_value;
            $addons = (float)$b->customizations->where('job_type', 'addon')->sum('job_total');
            $dislodge = (float)$b->customizations->where('job_type', 'dislodge')->sum('job_total');
            $supplement = $addons - $dislodge;
            $discountApplied = (float)$b->discount_applied;
            $adjustments = (float)$b->adjustments;
            $totalDiscount = $discountApplied + $adjustments;
            $bookingValue = ($consideration + $supplement) - $totalDiscount;
            $taxableValue = (float)($b->taxable_agreement_value ?: $consideration);
            $taxName = ($b->tax_name ?: 'GST') . ' (' . number_format($b->tax_rate, 2) . '%)';
            $taxAmount = (float)$b->tax_amount;
            $grossTaxable = (float)$b->gross_taxable_value;
            $grossCash = (float)$b->gross_cash_value;
            $finalBookingVal = (float)$b->final_booking_value ?: ($bookingValue + $taxAmount);
            $loanAmount = (float)($b->bankFinance?->sanctioned_amount ?? 0);
            $partyTaxableAmount = max(0, round($grossTaxable - $loanAmount, 2));
        @endphp

        <!-- Main Dashboard Split: Left Financial Parameters Card & Right Multi-Stream Matrix -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT PANEL: Financial Metrics Card (approx. 4 cols) -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-800 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-receipt text-amber-400 text-sm"></i>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Financial Breakdown</h3>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-amber-300">{{ $b->booking_code }}</span>
                </div>

                <div class="p-4 space-y-1.5 text-xs">
                    <!-- Base Pricing Breakdown -->
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Rate per sq. ft.</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($b->rate_per_sqft, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Unit Value</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($b->unit_value, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Parking Value</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($b->parking_charges, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Transformer Charges</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($b->transformer_charges, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Amenities Charges</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($b->amenities_charges, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 bg-slate-50/50 px-1 rounded">
                        <span class="text-slate-700 font-bold">Total Base Value</span>
                        <span class="font-mono font-bold text-slate-900">₹{{ number_format($b->base_unit_price, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 text-rose-600">
                        <span class="font-medium">Discount Applied</span>
                        <span class="font-mono font-semibold">- ₹{{ number_format($discountApplied, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 bg-amber-50/50 px-1 rounded">
                        <span class="text-amber-900 font-bold">Consideration Value</span>
                        <span class="font-mono font-bold text-amber-900">₹{{ number_format($consideration, 2) }}</span>
                    </div>

                    <!-- Customizations & Taxes -->
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Add-ons Total</span>
                        <span class="font-mono font-semibold text-emerald-700">+ ₹{{ number_format($addons, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Dislodge Total</span>
                        <span class="font-mono font-semibold text-rose-700">- ₹{{ number_format($dislodge, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Suppl. Value (Addons - Dislodge)</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($supplement, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Gross Total (Before Tax)</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($consideration + $supplement, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Tax Category</span>
                        <span class="font-semibold text-slate-700">{{ $b->tax_category ?? 'Standard' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Tax ({{ $taxName }})</span>
                        <span class="font-mono font-semibold text-slate-800">₹{{ number_format($taxAmount, 2) }}</span>
                    </div>

                    <!-- Gross Streams -->
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100 bg-indigo-50/50 px-1 rounded">
                        <span class="text-indigo-900 font-bold">Net Final Booking Value</span>
                        <span class="font-mono font-black text-indigo-900 text-sm">₹{{ number_format($finalBookingVal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Gross Taxable Amount</span>
                        <span class="font-mono font-semibold text-sky-700">₹{{ number_format($grossTaxable, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Gross Cash / Non-Taxable</span>
                        <span class="font-mono font-semibold text-slate-700">₹{{ number_format($grossCash, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Sanctioned Bank Loan</span>
                        <span class="font-mono font-semibold text-purple-700">₹{{ number_format($loanAmount, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Party Self-Taxable Target</span>
                        <span class="font-mono font-semibold text-sky-700">₹{{ number_format($partyTaxableAmount, 2) }}</span>
                    </div>

                    <!-- Additional Details -->
                    <div class="pt-2 text-[11px] space-y-1 text-slate-500">
                        <div>
                            <span class="font-bold text-slate-600">Agreement:</span>
                            <span>{{ $b->saleAgreement ? 'Executed on ' . ($b->saleAgreement->agreement_date ? $b->saleAgreement->agreement_date->format('d-m-Y') : 'Yes') : 'Pending' }}</span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-600">Bank Finance:</span>
                            <span>{{ $b->bankFinance ? ($b->bankFinance->bank_name . ($b->bankFinance->loan_account_no ? ' - A/C ' . $b->bankFinance->loan_account_no : '')) : 'Direct / None' }}</span>
                        </div>
                        @if($b->particulars)
                            <div>
                                <span class="font-bold text-slate-600">Description:</span>
                                <span>{{ $b->particulars }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: 3 Transaction Tables (Top) & 4-Stream Summary Matrix (Bottom) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- TOP SECTION: 3 Side-by-Side Transaction Tables -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Table 1: Bank Finance Receipts -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-3.5 py-2.5 bg-purple-50 border-b border-purple-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-purple-900 flex items-center space-x-1">
                                <i class="fa-solid fa-building-columns text-purple-600 text-[10px]"></i>
                                <span>Recd :: Bank Finance</span>
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 bg-purple-200 text-purple-800 rounded font-bold">{{ $loanReceipts->count() }}</span>
                        </div>
                        <div class="overflow-x-auto flex-1 max-h-56">
                            <table class="w-full text-left text-[11px] text-slate-600 border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-100 uppercase text-[9px]">
                                        <th class="py-2 px-2.5">Date</th>
                                        <th class="py-2 px-2">Vch No</th>
                                        <th class="py-2 px-2.5 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($loanReceipts as $lt)
                                        <tr class="hover:bg-purple-50/30">
                                            <td class="py-1.5 px-2.5 whitespace-nowrap">{{ $lt->voucher_date ? $lt->voucher_date->format('d-m-y') : '' }}</td>
                                            <td class="py-1.5 px-2 font-mono text-slate-500 whitespace-nowrap">{{ $lt->voucher_no ?: $lt->transaction_code }}</td>
                                            <td class="py-1.5 px-2.5 text-right font-mono font-bold text-purple-900 whitespace-nowrap">₹{{ number_format($lt->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-slate-400 text-[11px]">No loan disbursements</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 bg-purple-50/60 border-t border-purple-100 flex items-center justify-between text-xs font-bold text-purple-900">
                            <span>Total Loan Recd</span>
                            <span class="font-mono">₹{{ number_format($loanReceipts->sum('amount'), 2) }}</span>
                        </div>
                    </div>

                    <!-- Table 2: Party (Taxable) Receipts -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-3.5 py-2.5 bg-sky-50 border-b border-sky-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-sky-900 flex items-center space-x-1">
                                <i class="fa-solid fa-money-check-dollar text-sky-600 text-[10px]"></i>
                                <span>Recd :: Party (Taxable)</span>
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 bg-sky-200 text-sky-800 rounded font-bold">{{ $partyTaxableReceipts->count() }}</span>
                        </div>
                        <div class="overflow-x-auto flex-1 max-h-56">
                            <table class="w-full text-left text-[11px] text-slate-600 border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-100 uppercase text-[9px]">
                                        <th class="py-2 px-2.5">Date</th>
                                        <th class="py-2 px-2">Vch No</th>
                                        <th class="py-2 px-2.5 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($partyTaxableReceipts as $pt)
                                        <tr class="hover:bg-sky-50/30">
                                            <td class="py-1.5 px-2.5 whitespace-nowrap">{{ $pt->voucher_date ? $pt->voucher_date->format('d-m-y') : '' }}</td>
                                            <td class="py-1.5 px-2 font-mono text-slate-500 whitespace-nowrap">{{ $pt->voucher_no ?: $pt->transaction_code }}</td>
                                            <td class="py-1.5 px-2.5 text-right font-mono font-bold text-sky-900 whitespace-nowrap">₹{{ number_format($pt->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-slate-400 text-[11px]">No taxable receipts</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 bg-sky-50/60 border-t border-sky-100 flex items-center justify-between text-xs font-bold text-sky-900">
                            <span>Total Taxable Recd</span>
                            <span class="font-mono">₹{{ number_format($partyTaxableReceipts->sum('amount'), 2) }}</span>
                        </div>
                    </div>

                    <!-- Table 3: Non-Taxable / Cash Receipts -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-3.5 py-2.5 bg-amber-50 border-b border-amber-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-900 flex items-center space-x-1">
                                <i class="fa-solid fa-coins text-amber-600 text-[10px]"></i>
                                <span>Recd :: Non-Taxable</span>
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 bg-amber-200 text-amber-800 rounded font-bold">{{ $cashReceipts->count() }}</span>
                        </div>
                        <div class="overflow-x-auto flex-1 max-h-56">
                            <table class="w-full text-left text-[11px] text-slate-600 border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-100 uppercase text-[9px]">
                                        <th class="py-2 px-2.5">Date</th>
                                        <th class="py-2 px-2">Vch No</th>
                                        <th class="py-2 px-2.5 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($cashReceipts as $ct)
                                        <tr class="hover:bg-amber-50/30">
                                            <td class="py-1.5 px-2.5 whitespace-nowrap">{{ $ct->voucher_date ? $ct->voucher_date->format('d-m-y') : '' }}</td>
                                            <td class="py-1.5 px-2 font-mono text-slate-500 whitespace-nowrap">{{ $ct->voucher_no ?: $ct->transaction_code }}</td>
                                            <td class="py-1.5 px-2.5 text-right font-mono font-bold text-amber-900 whitespace-nowrap">₹{{ number_format($ct->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-slate-400 text-[11px]">No non-taxable receipts</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 bg-amber-50/60 border-t border-amber-100 flex items-center justify-between text-xs font-bold text-amber-900">
                            <span>Total Cash Recd</span>
                            <span class="font-mono">₹{{ number_format($cashReceipts->sum('amount'), 2) }}</span>
                        </div>
                    </div>

                </div>

                <!-- BOTTOM SECTION: 4-Stream Summary Reconciliation Table -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-table-columns text-emerald-400 text-sm"></i>
                            <h3 class="font-bold text-xs uppercase tracking-wider">Multi-Stream Financial Reconciliation Summary</h3>
                        </div>
                        <span class="text-xs text-slate-400">All amounts in INR (₹)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200 uppercase text-[11px] tracking-wider">
                                    <th class="py-3.5 px-5">Particulars</th>
                                    <th class="py-3.5 px-4 text-right">Finance / Loan A/C</th>
                                    <th class="py-3.5 px-4 text-right">Party (Taxable)</th>
                                    <th class="py-3.5 px-4 text-right">Non-Taxable</th>
                                    <th class="py-3.5 px-5 text-right font-black text-slate-900">Gross Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                <!-- Row 1: Booking Summary -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-5 font-bold text-slate-900 flex items-center space-x-2">
                                        <i class="fa-solid fa-file-invoice text-indigo-500 text-xs"></i>
                                        <span>Booking Summary</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-semibold text-purple-800">
                                        ₹{{ number_format($financialSummary['booking_summary']['loan'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-semibold text-sky-800">
                                        ₹{{ number_format($financialSummary['booking_summary']['taxable'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-semibold text-amber-800">
                                        ₹{{ number_format($financialSummary['booking_summary']['cash'], 2) }}
                                    </td>
                                    <td class="py-3 px-5 text-right font-mono font-black text-slate-900">
                                        ₹{{ number_format($financialSummary['booking_summary']['gross'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 2: Sales-Receipt -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-5 font-bold text-slate-800 flex items-center space-x-2">
                                        <i class="fa-solid fa-arrow-down-to-bracket text-emerald-500 text-xs"></i>
                                        <span>Sales-Receipt</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-700">
                                        ₹{{ number_format($financialSummary['sales_receipt']['loan'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-700">
                                        ₹{{ number_format($financialSummary['sales_receipt']['taxable'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-700">
                                        ₹{{ number_format($financialSummary['sales_receipt']['cash'], 2) }}
                                    </td>
                                    <td class="py-3 px-5 text-right font-mono font-bold text-slate-900">
                                        ₹{{ number_format($financialSummary['sales_receipt']['gross'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 3: Payment Adjustments -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-5 font-bold text-slate-800 flex items-center space-x-2">
                                        <i class="fa-solid fa-arrow-up-from-bracket text-rose-500 text-xs"></i>
                                        <span>Payment Adjustments</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-rose-700">
                                        ₹{{ number_format($financialSummary['payment_adj']['loan'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-rose-700">
                                        ₹{{ number_format($financialSummary['payment_adj']['taxable'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-rose-700">
                                        ₹{{ number_format($financialSummary['payment_adj']['cash'], 2) }}
                                    </td>
                                    <td class="py-3 px-5 text-right font-mono font-bold text-rose-800">
                                        ₹{{ number_format($financialSummary['payment_adj']['gross'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 4: Net Sales-Receipt -->
                                <tr class="hover:bg-slate-50/70 transition bg-slate-50/40">
                                    <td class="py-3 px-5 font-bold text-slate-900 flex items-center space-x-2">
                                        <i class="fa-solid fa-calculator text-sky-600 text-xs"></i>
                                        <span>Net Sales-Receipt</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-purple-900">
                                        ₹{{ number_format($financialSummary['net_sales']['loan'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-sky-900">
                                        ₹{{ number_format($financialSummary['net_sales']['taxable'], 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-amber-900">
                                        ₹{{ number_format($financialSummary['net_sales']['cash'], 2) }}
                                    </td>
                                    <td class="py-3 px-5 text-right font-mono font-bold text-slate-900">
                                        ₹{{ number_format($financialSummary['net_sales']['gross'], 2) }}
                                    </td>
                                </tr>

                                <!-- Row 5: Balance Due -->
                                <tr class="bg-amber-50/60 border-t-2 border-slate-300 font-bold">
                                    <td class="py-4 px-5 font-black text-amber-950 flex items-center space-x-2">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm"></i>
                                        <span class="text-sm">Balance Due</span>
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono font-black text-purple-950 text-sm">
                                        ₹{{ number_format($financialSummary['balance_due']['loan'], 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono font-black text-sky-950 text-sm">
                                        ₹{{ number_format($financialSummary['balance_due']['taxable'], 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono font-black text-amber-950 text-sm">
                                        ₹{{ number_format($financialSummary['balance_due']['cash'], 2) }}
                                    </td>
                                    <td class="py-4 px-5 text-right font-mono font-black text-rose-700 text-base">
                                        ₹{{ number_format($financialSummary['balance_due']['gross'], 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400">
            <i class="fa-solid fa-folder-open text-4xl mb-3 block text-slate-300"></i>
            <p class="text-base font-semibold text-slate-600">No Booking Selected</p>
            <p class="text-xs text-slate-400 mt-1">Please select a project booking from the dropdown above to view the full 360° financial summary.</p>
        </div>
    @endif

</div>
@endsection
