@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.reports.index', $project->id) }}" class="hover:text-sky-600">Reports</a>
                <span>/</span>
                <span class="text-amber-600">Customer Dues Aging</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Customer Dues & Aging Analysis</h1>
            <p class="text-xs text-slate-500 mt-0.5">Time-bucketed recovery analysis of outstanding customer dues and bounced banking instruments.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Aging Matrix</span>
            </button>
            <a href="{{ route('project.reports.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Aging Buckets Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
        
        <!-- 0-30 Days -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-emerald-600 tracking-wider font-sans block">0 - 30 Days (Current)</span>
            <h3 class="text-xl font-bold text-emerald-800 mt-1">₹{{ number_format($buckets['under_30'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">Recent bookings / dues</div>
        </div>

        <!-- 31-60 Days -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-sky-600 tracking-wider font-sans block">31 - 60 Days</span>
            <h3 class="text-xl font-bold text-sky-800 mt-1">₹{{ number_format($buckets['30_to_60'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">Follow-up reminder stage</div>
        </div>

        <!-- 61-90 Days -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-amber-600 tracking-wider font-sans block">61 - 90 Days</span>
            <h3 class="text-xl font-bold text-amber-800 mt-1">₹{{ number_format($buckets['60_to_90'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">Formal demand notice stage</div>
        </div>

        <!-- 90+ Days -->
        <div class="bg-rose-50 p-5 rounded-2xl border border-rose-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-rose-700 tracking-wider font-sans block">90+ Days (Critical)</span>
            <h3 class="text-xl font-black text-rose-800 mt-1">₹{{ number_format($buckets['above_90'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-rose-600 font-sans font-bold">Escalation & legal notice stage</div>
        </div>

    </div>

    <!-- Outstanding Due Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-3.5 border-b border-slate-200 font-bold uppercase text-xs text-slate-700 tracking-wider">
            Active Receivables by Customer & Unit
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Unit & Booking ID</th>
                        <th class="py-3.5 px-4">Customer & Mobile</th>
                        <th class="py-3.5 px-4">Taxable Bank Due</th>
                        <th class="py-3.5 px-4">Cash Due</th>
                        <th class="py-3.5 px-4">Total Outstanding</th>
                        <th class="py-3.5 px-4">Aging Bucket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($agedBookings as $row)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $row['booking']->unit_no }} ({{ $row['booking']->booking_code }})
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $row['booking']->customer_name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $row['booking']->mobile_no }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-emerald-800 font-bold">
                                ₹{{ number_format($row['taxable_due'], 2) }}
                            </td>
                            <td class="py-3 px-4 font-mono text-amber-800 font-bold">
                                ₹{{ number_format($row['cash_due'], 2) }}
                            </td>
                            <td class="py-3 px-4 font-mono font-black text-rose-700">
                                ₹{{ number_format($row['due'], 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $row['days'] > 90 ? 'bg-rose-100 text-rose-800' : ($row['days'] > 60 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $row['bucket'] }} ({{ $row['days'] }}d)
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">All customer dues are fully cleared!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
