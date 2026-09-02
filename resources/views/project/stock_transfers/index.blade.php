@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'outward' }">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.expenses.index', $project->id) }}" class="hover:text-sky-600">Expenses</a>
                <span>/</span>
                <span class="text-teal-600">Stock Transfers</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Inter-Project Material & Stock Transfers</h1>
            <p class="text-xs text-slate-500 mt-0.5">Move excess raw materials between construction sites with automated cost adjustment vouchers.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('project.expenses.index', $project->id) }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition border border-slate-200 flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Site Expenses</span>
            </a>
            <a href="{{ route('project.stock-transfers.create', $project->id) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-teal-600/20 transition">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Initiate Stock Transfer</span>
            </a>
        </div>
    </div>

    <!-- Stock Transfer Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-mono">
        
        <!-- Outward Transfers Value -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-amber-600 tracking-wider font-sans flex items-center space-x-1">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                <span>Outward Transferred Stock</span>
            </span>
            <h3 class="text-xl font-bold text-amber-800 mt-1">₹{{ number_format($stats['total_outward_value'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Material moved out to other sites (Cost Credit)
            </div>
        </div>

        <!-- Inward Transfers Value -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-teal-600 tracking-wider font-sans flex items-center space-x-1">
                <i class="fa-solid fa-arrow-down-left-and-up-right-to-center text-[10px]"></i>
                <span>Inward Received Stock</span>
            </span>
            <h3 class="text-xl font-bold text-teal-800 mt-1">₹{{ number_format($stats['total_inward_value'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400 font-sans">
                Material received from other sites (Cost Debit)
            </div>
        </div>

        <!-- Net Impact -->
        <div class="bg-gradient-to-br from-slate-900 to-teal-950 text-white p-4 rounded-2xl shadow-md border border-slate-800 flex flex-col justify-between font-sans">
            <div>
                <span class="text-[10px] uppercase font-bold text-teal-400 tracking-wider block">Net Stock Impact on Project</span>
                <h3 class="text-xl font-black font-mono mt-1 text-white">
                    {{ $stats['net_transfer_impact'] >= 0 ? '+' : '' }}₹{{ number_format($stats['net_transfer_impact'], 2) }}
                </h3>
            </div>
            <div class="text-[11px] text-teal-200 mt-2">
                Inward - Outward Transfer Balance
            </div>
        </div>

    </div>

    <!-- Transfers Table Card with Tabs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Tab Bar -->
        <div class="flex items-center space-x-2 p-3 bg-slate-50 border-b border-slate-200">
            <button @click="activeTab = 'outward'"
                    :class="activeTab === 'outward' ? 'bg-amber-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Outward Stock Sent ({{ $outwardTransfers->count() }})</span>
            </button>
            <button @click="activeTab = 'inward'"
                    :class="activeTab === 'inward' ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center space-x-2 transition">
                <i class="fa-solid fa-arrow-down-left-and-up-right-to-center"></i>
                <span>Inward Stock Received ({{ $inwardTransfers->count() }})</span>
            </button>
        </div>

        <!-- Outward Transfers List -->
        <div x-show="activeTab === 'outward'" class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Transfer Code & Date</th>
                        <th class="py-3 px-4">Destination Project</th>
                        <th class="py-3 px-4">Material & Specs</th>
                        <th class="py-3 px-4">Quantity & Rate</th>
                        <th class="py-3 px-4 text-right">Transfer Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($outwardTransfers as $st)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 align-top">
                                <span class="font-mono font-bold text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-300 text-slate-800">
                                    {{ $st->transfer_code }}
                                </span>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ $st->transfer_date->format('d M, Y') }}
                                </div>
                            </td>
                            <td class="py-3 px-4 align-top text-xs">
                                <span class="font-bold text-slate-900 block">{{ $st->destinationProject->name }}</span>
                                <span class="text-slate-500 text-[11px]">{{ $st->destinationProject->project_code }}</span>
                            </td>
                            <td class="py-3 px-4 align-top text-xs">
                                <span class="font-bold text-slate-900 block">{{ $st->material_name }}</span>
                                @if($st->remarks)<span class="text-slate-500 text-[11px]">{{ $st->remarks }}</span>@endif
                            </td>
                            <td class="py-3 px-4 align-top text-xs font-mono">
                                <div>{{ number_format($st->quantity, 2) }} {{ $st->unit_measure }}</div>
                                <div class="text-slate-400 text-[11px]">@ ₹{{ number_format($st->unit_cost, 2) }}</div>
                            </td>
                            <td class="py-3 px-4 align-top text-right font-mono font-bold text-amber-700">
                                ₹{{ number_format($st->total_transfer_value, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                No outward stock transfers recorded from this project site.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Inward Transfers List -->
        <div x-show="activeTab === 'inward'" class="overflow-x-auto" style="display: none;">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Transfer Code & Date</th>
                        <th class="py-3 px-4">Source Project</th>
                        <th class="py-3 px-4">Material & Specs</th>
                        <th class="py-3 px-4">Quantity & Rate</th>
                        <th class="py-3 px-4 text-right">Transfer Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inwardTransfers as $st)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 align-top">
                                <span class="font-mono font-bold text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-300 text-slate-800">
                                    {{ $st->transfer_code }}
                                </span>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ $st->transfer_date->format('d M, Y') }}
                                </div>
                            </td>
                            <td class="py-3 px-4 align-top text-xs">
                                <span class="font-bold text-slate-900 block">{{ $st->sourceProject->name }}</span>
                                <span class="text-slate-500 text-[11px]">{{ $st->sourceProject->project_code }}</span>
                            </td>
                            <td class="py-3 px-4 align-top text-xs">
                                <span class="font-bold text-slate-900 block">{{ $st->material_name }}</span>
                                @if($st->remarks)<span class="text-slate-500 text-[11px]">{{ $st->remarks }}</span>@endif
                            </td>
                            <td class="py-3 px-4 align-top text-xs font-mono">
                                <div>{{ number_format($st->quantity, 2) }} {{ $st->unit_measure }}</div>
                                <div class="text-slate-400 text-[11px]">@ ₹{{ number_format($st->unit_cost, 2) }}</div>
                            </td>
                            <td class="py-3 px-4 align-top text-right font-mono font-bold text-teal-700">
                                ₹{{ number_format($st->total_transfer_value, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                No inward stock transfers received into this project site.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
