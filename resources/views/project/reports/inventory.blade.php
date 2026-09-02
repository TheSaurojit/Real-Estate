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
                <span class="text-sky-600">Inventory Velocity</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Unit Sales & Area Velocity Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track units booked across lifecycle stages, super built-up area sold, and average realization rates.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Report</span>
            </button>
            <a href="{{ route('project.reports.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Inventory KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
        
        <!-- Total Units Allotted -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-sky-600 tracking-wider font-sans block">Total Units Allotted</span>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $totalUnits }} <span class="text-xs font-normal text-slate-400 font-sans">Units</span></h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Cancelled: {{ $cancelledUnits }}
            </div>
        </div>

        <!-- Total S.B. Area Sold -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-indigo-600 tracking-wider font-sans block">Total S.B. Area Sold</span>
            <h3 class="text-2xl font-bold text-indigo-900 mt-1">{{ number_format($totalSoldArea, 0) }} <span class="text-xs font-normal text-slate-400 font-sans">Sq. Ft.</span></h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Active Booked Area
            </div>
        </div>

        <!-- Total Value Contracted -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-emerald-600 tracking-wider font-sans block">Total Contracted Value</span>
            <h3 class="text-xl font-bold text-emerald-800 mt-1">₹{{ number_format($totalSoldValue, 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Gross Bookings Total
            </div>
        </div>

        <!-- Average Realization Rate -->
        <div class="bg-gradient-to-br from-slate-900 to-sky-950 text-white p-5 rounded-2xl shadow-md border border-slate-800 flex flex-col justify-between font-sans">
            <div>
                <span class="text-[10px] uppercase font-bold text-sky-400 tracking-wider block">Average Realization Rate</span>
                <h3 class="text-2xl font-black font-mono mt-1 text-white">₹{{ number_format($avgRatePerSqft, 2) }}</h3>
            </div>
            <div class="text-[11px] text-sky-200 mt-2 font-mono">
                Per Sq. Ft. of Super Built-up Area
            </div>
        </div>

    </div>

    <!-- Units Lifecycle Distribution -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-3.5 border-b border-slate-200 font-bold uppercase text-xs text-slate-700 tracking-wider">
            Allotted Property Units Directory & Status
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Unit No & Location</th>
                        <th class="py-3.5 px-4">Customer & Phone</th>
                        <th class="py-3.5 px-4">S.B. Area</th>
                        <th class="py-3.5 px-4">Contract Value</th>
                        <th class="py-3.5 px-4">Current Lifecycle Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold font-mono text-slate-900">
                                {{ $b->unit_no }} (Block: {{ $b->block_name ?? '-' }}, Floor: {{ $b->floor_no ?? '-' }})
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $b->customer_name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $b->mobile_no }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">
                                {{ number_format($b->super_built_up_area, 2) }} Sq. Ft.
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                ₹{{ number_format($b->total_booking_value, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                @if($b->status === 'live')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-300">Live Booking</span>
                                @elseif($b->status === 'executed_agreement')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Agreement Done</span>
                                @elseif($b->status === 'registered_deed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-300">Deed Registered</span>
                                @elseif($b->status === 'cancelled')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No units allotted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
