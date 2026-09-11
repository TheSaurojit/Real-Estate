@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.reports.index', $project->id) }}" class="hover:text-sky-600">Reports</a>
                <span>/</span>
                <span class="text-emerald-600 font-bold">2.3.1.2 Booking Financial Breakdown</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Booking Financial Breakdown Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">Dual-ledger calculations $(a)$ through $(L)$ across Consideration, Job sheets, GST, Bank Finance, Self-Taxable, and Cash streams.</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Export to Excel Button -->
            <a href="{{ route('project.reports.bookings.financial', array_merge(request()->query(), ['project' => $project->id, 'export' => 'excel'])) }}"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export to Excel</span>
            </a>
            <a href="{{ route('project.reports.index', $project->id) }}"
               class="px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Reports Hub</span>
            </a>
        </div>
    </div>

    <!-- Live vs Cancelled Bookings Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200">
        <a href="{{ route('project.reports.bookings.financial', array_merge(request()->except(['status', 'page']), ['project' => $project->id, 'status' => 'live'])) }}"
           class="px-5 py-2.5 text-xs font-bold border-b-2 transition flex items-center space-x-2 {{ $statusFilter !== 'cancelled' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <i class="fa-solid fa-circle-check {{ $statusFilter !== 'cancelled' ? 'text-emerald-600' : 'text-slate-400' }}"></i>
            <span>Live Bookings Registry</span>
        </a>

        <a href="{{ route('project.reports.bookings.financial', array_merge(request()->except(['status', 'page']), ['project' => $project->id, 'status' => 'cancelled'])) }}"
           class="px-5 py-2.5 text-xs font-bold border-b-2 transition flex items-center space-x-2 {{ $statusFilter === 'cancelled' ? 'border-rose-600 text-rose-700 bg-rose-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <i class="fa-solid fa-ban {{ $statusFilter === 'cancelled' ? 'text-rose-600' : 'text-slate-400' }}"></i>
            <span>Cancelled Bookings Audit</span>
        </a>
    </div>

    <!-- Keyword & Date Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('project.reports.bookings.financial', $project->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Booking Code, Customer, Unit, Block..."
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filter</span>
                </button>
                @if(request()->hasAny(['search', 'from_date', 'to_date']))
                    <a href="{{ route('project.reports.bookings.financial', ['project' => $project->id, 'status' => $statusFilter]) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Financial Matrix Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-calculator text-emerald-600 text-sm"></i>
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">
                    {{ $statusFilter === 'cancelled' ? 'Cancelled Bookings Financial Audit Statement' : 'Live Bookings Dual-Ledger Financial Statement' }}
                </h3>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Showing <strong class="text-slate-800 font-mono">{{ $bookings->total() }}</strong> Bookings
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="text-[10px] font-bold uppercase text-slate-700 bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3 sticky left-0 bg-slate-100 z-10">Booking ID</th>
                        <th class="py-3 px-3">Date</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">Category</th>
                        <th class="py-3 px-3">Unit Info</th>
                        <th class="py-3 px-3 text-right bg-sky-50 text-sky-900">Consideration (a)</th>
                        <th class="py-3 px-3 text-right">Addons (b)</th>
                        <th class="py-3 px-3 text-right">Dislodge (c)</th>
                        <th class="py-3 px-3 text-right font-bold">Suppl (d=b-c)</th>
                        <th class="py-3 px-3 text-right text-rose-700">Discount/Adj (e)</th>
                        <th class="py-3 px-3 text-right font-black bg-indigo-50 text-indigo-900">Booking Val (f=(a+d)-e)</th>
                        <th class="py-3 px-3 text-right">Sale Agr Val</th>
                        <th class="py-3 px-3 text-right">Taxable Val (g)</th>
                        <th class="py-3 px-3">Tax Rate</th>
                        <th class="py-3 px-3 text-right">Tax Val (h)</th>
                        <th class="py-3 px-3 text-right font-black bg-emerald-50 text-emerald-900">Gross Taxable (i=g+h)</th>
                        <th class="py-3 px-3 text-right font-black bg-slate-900 text-white">Final Val (j=f+h)</th>
                        <th class="py-3 px-3 text-right text-teal-700 font-bold">Bank Loan (k)</th>
                        <th class="py-3 px-3 text-right">Loan Recd</th>
                        <th class="py-3 px-3 text-right">Loan Adj</th>
                        <th class="py-3 px-3 text-right text-amber-700 font-bold">Loan Bal Due</th>
                        <th class="py-3 px-3 text-right text-sky-800 font-bold">Party Self-Tax (i-k)</th>
                        <th class="py-3 px-3 text-right">Self Recd</th>
                        <th class="py-3 px-3 text-right">Self Adj</th>
                        <th class="py-3 px-3 text-right text-amber-700 font-bold">Self Bal Due</th>
                        <th class="py-3 px-3 text-right text-amber-800 font-bold">Non-Tax Val (L=j-i)</th>
                        <th class="py-3 px-3 text-right">Cash Recd</th>
                        <th class="py-3 px-3 text-right">Cash Adj</th>
                        <th class="py-3 px-3 text-right text-amber-700 font-bold">Cash Bal Due</th>
                        <th class="py-3 px-3 text-right font-bold bg-slate-50">Total Taxable Val</th>
                        <th class="py-3 px-3 text-right text-emerald-700 font-bold bg-slate-50">Taxable Recd</th>
                        <th class="py-3 px-3 text-right text-amber-700 font-bold bg-slate-50">Taxable Due</th>
                        <th class="py-3 px-3 text-right font-bold bg-slate-50">Total Non-Tax Val</th>
                        <th class="py-3 px-3 text-right text-amber-700 font-bold bg-slate-50">Non-Tax Recd</th>
                        <th class="py-3 px-3 text-right text-amber-700 font-bold bg-slate-50">Non-Tax Due</th>
                        <th class="py-3 px-3 text-right font-black bg-emerald-100 text-emerald-950">Total Receipts</th>
                        <th class="py-3 px-3 text-right font-black bg-amber-100 text-amber-950">Net Balance Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($bookings as $b)
                        @php
                            $consideration_a = (float)$b->consideration_value;
                            $addons_b = (float)$b->customizations->where('job_type', 'addon')->sum('job_total');
                            $dislodge_c = (float)$b->customizations->where('job_type', 'dislodge')->sum('job_total');
                            $supplement_d = $addons_b - $dislodge_c;
                            $discount_e = (float)$b->discount_applied + (float)$b->adjustments;
                            $booking_value_f = ($consideration_a + $supplement_d) - $discount_e;
                            $agreement_val = (float)($b->saleAgreement?->agreement_value ?? $consideration_a);
                            $taxable_val_g = (float)($b->taxable_agreement_value ?: $consideration_a);
                            $tax_name_duty = ($b->tax_name ?: 'GST') . ' (' . number_format($b->tax_rate, 2) . '%)';
                            $tax_val_h = (float)$b->tax_amount;
                            $gross_taxable_i = (float)$b->gross_taxable_value;
                            $final_booking_j = (float)$b->final_booking_value ?: ($booking_value_f + $tax_val_h);
                            $loan_k = (float)($b->bankFinance?->sanctioned_amount ?? 0);
                            $loan_recd = (float)$b->loan_received;
                            $loan_adj = (float)$b->loan_refunded;
                            $loan_due = max(0, round($loan_k - $loan_recd, 2));
                            $party_self_target = max(0, round($gross_taxable_i - $loan_k, 2));
                            $self_tax_recd = (float)$b->taxable_self_received;
                            $self_tax_adj = (float)$b->taxable_self_refunded;
                            $self_tax_due = max(0, round($party_self_target - $self_tax_recd, 2));
                            $non_tax_val_L = max(0, round($final_booking_j - $gross_taxable_i, 2));
                            $non_tax_recd = (float)$b->total_cash_received;
                            $non_tax_adj = (float)$b->cash_refunded;
                            $non_tax_due = max(0, round($non_tax_val_L - $non_tax_recd, 2));
                            $total_tax_val = $gross_taxable_i;
                            $total_tax_recd = (float)$b->total_taxable_received;
                            $bal_due_tax = (float)$b->taxable_due_balance;
                            $total_nontax_val = (float)$b->gross_cash_value;
                            $total_nontax_recd = (float)$b->total_cash_received;
                            $bal_due_nontax = (float)$b->cash_due_balance;
                            $total_receipt = (float)$b->total_receipts_cleared;
                            $balance_due = (float)$b->total_outstanding_due;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3 font-bold text-sky-700 sticky left-0 bg-white z-10 border-r border-slate-100">
                                <a href="{{ route('project.bookings.show', [$project->id, $b->id]) }}" class="hover:underline">
                                    {{ $b->booking_code }}
                                </a>
                            </td>
                            <td class="py-2.5 px-3 font-sans text-slate-600">{{ $b->booking_date ? $b->booking_date->format('d/m/Y') : '-' }}</td>
                            <td class="py-2.5 px-3 font-sans font-bold text-slate-800">{{ $b->customer_name }}</td>
                            <td class="py-2.5 px-3 font-sans">{{ $b->is_landowner_allocation ? 'Landowner' : 'Purchaser' }}</td>
                            <td class="py-2.5 px-3 font-sans text-slate-700">{{ $b->block_name }} / {{ $b->floor_no }} / <strong>{{ $b->unit_no }}</strong></td>
                            <td class="py-2.5 px-3 text-right bg-sky-50/50">₹{{ number_format($consideration_a, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($addons_b, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($dislodge_c, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-bold">₹{{ number_format($supplement_d, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-rose-600">₹{{ number_format($discount_e, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-black bg-indigo-50/50 text-indigo-900">₹{{ number_format($booking_value_f, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($agreement_val, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($taxable_val_g, 2) }}</td>
                            <td class="py-2.5 px-3 font-sans text-[10px] text-slate-500">{{ $tax_name_duty }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($tax_val_h, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-black bg-emerald-50/50 text-emerald-900">₹{{ number_format($gross_taxable_i, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-black bg-slate-900 text-white">₹{{ number_format($final_booking_j, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-teal-700 font-bold">₹{{ number_format($loan_k, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($loan_recd, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($loan_adj, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-700 font-bold">₹{{ number_format($loan_due, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-sky-800 font-bold">₹{{ number_format($party_self_target, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($self_tax_recd, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($self_tax_adj, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-700 font-bold">₹{{ number_format($self_tax_due, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-800 font-bold">₹{{ number_format($non_tax_val_L, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($non_tax_recd, 2) }}</td>
                            <td class="py-2.5 px-3 text-right">₹{{ number_format($non_tax_adj, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-700 font-bold">₹{{ number_format($non_tax_due, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-bold bg-slate-50/50">₹{{ number_format($total_tax_val, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-emerald-700 font-bold bg-slate-50/50">₹{{ number_format($total_tax_recd, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-700 font-bold bg-slate-50/50">₹{{ number_format($bal_due_tax, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-bold bg-slate-50/50">₹{{ number_format($total_nontax_val, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-700 font-bold bg-slate-50/50">₹{{ number_format($total_nontax_recd, 2) }}</td>
                            <td class="py-2.5 px-3 text-right text-amber-700 font-bold bg-slate-50/50">₹{{ number_format($bal_due_nontax, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-black bg-emerald-100 text-emerald-950">₹{{ number_format($total_receipt, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-black bg-amber-100 text-amber-950">₹{{ number_format($balance_due, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="37" class="py-12 text-center text-slate-400 text-sm font-sans">
                                <i class="fa-solid fa-file-invoice text-3xl mb-2 text-slate-300 block"></i>
                                No financial records found for {{ $statusFilter === 'cancelled' ? 'cancelled' : 'live' }} bookings.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
