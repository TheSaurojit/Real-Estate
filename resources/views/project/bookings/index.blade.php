@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-sky-600">Bookings Master</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Project Flat & Property Bookings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer property allotments, job sheet customizations, sale agreements, and dual-ledger splits.</p>
        </div>
        <a href="{{ route('project.bookings.create', $project->id) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-sky-600/20 transition">
            <i class="fa-solid fa-file-circle-plus"></i>
            <span>New Booking</span>
        </a>
    </div>

    <!-- Financial Stats Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Active Bookings -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Active Bookings</span>
                    <h3 class="text-xl font-bold text-slate-800 mt-1">{{ $stats['active_bookings'] }} <span class="text-xs font-normal text-slate-400">/ {{ $stats['total_bookings'] }} Total</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-door-open"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Cancelled: <span class="text-rose-500 font-semibold">{{ $stats['cancelled_bookings'] }}</span>
            </div>
        </div>

        <!-- Total Consideration -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Base Consideration</span>
                    <h3 class="text-xl font-bold text-emerald-700 mt-1">₹{{ number_format($stats['total_consideration'], 2) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Unit base cost before GST
            </div>
        </div>

        <!-- Supplementary Total -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Job Sheet Rollup</span>
                    <h3 class="text-xl font-bold text-indigo-700 mt-1">₹{{ number_format($stats['total_supplementary'], 2) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-sliders"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Net Add-ons & Dislodges
            </div>
        </div>

        <!-- Total Booking Value -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Gross Booking Value</span>
                    <h3 class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($stats['total_booking_value'], 2) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">
                Consideration + GST + Job Sheets
            </div>
        </div>

    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Search Input -->
        <form action="{{ route('project.bookings.index', $project->id) }}" method="GET" class="w-full md:w-96 flex items-center">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by Customer, Unit No, Booking ID..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
            </div>
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        </form>

        <!-- Status Filter Tabs -->
        <div class="flex items-center space-x-1.5 overflow-x-auto w-full md:w-auto text-xs font-medium">
            <a href="{{ route('project.bookings.index', ['project' => $project->id, 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ !request('status') ? 'bg-sky-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All ({{ $stats['total_bookings'] }})
            </a>
            <a href="{{ route('project.bookings.index', ['project' => $project->id, 'status' => 'live', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'live' ? 'bg-sky-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Live
            </a>
            <a href="{{ route('project.bookings.index', ['project' => $project->id, 'status' => 'executed_agreement', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'executed_agreement' ? 'bg-sky-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Agreement Done
            </a>
            <a href="{{ route('project.bookings.index', ['project' => $project->id, 'status' => 'registered_deed', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'registered_deed' ? 'bg-sky-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Deed Registered
            </a>
            <a href="{{ route('project.bookings.index', ['project' => $project->id, 'status' => 'cancelled', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Cancelled
            </a>
        </div>

    </div>

    <!-- Bookings Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Booking ID & Date</th>
                        <th class="py-3.5 px-4">Customer & Contact</th>
                        <th class="py-3.5 px-4">Property / Flat Info</th>
                        <th class="py-3.5 px-4">Financials & Value</th>
                        <th class="py-3.5 px-4">Status & Agreement</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- Booking ID & Date -->
                            <td class="py-4 px-4 align-top">
                                <a href="{{ route('project.bookings.show', [$project->id, $b->id]) }}" class="font-mono font-bold text-sky-700 hover:underline text-xs bg-sky-50 px-2 py-0.5 rounded border border-sky-200 inline-block">
                                    {{ $b->booking_code }}
                                </a>
                                <div class="text-xs text-slate-400 mt-1">
                                    <i class="fa-regular fa-calendar text-[11px] mr-1"></i> {{ $b->booking_date->format('d M Y') }}
                                </div>
                                @if($b->is_landowner_allocation)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Landowner Allotment
                                    </span>
                                @endif
                            </td>

                            <!-- Customer & Contact -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $b->customer_salutation }} {{ $b->customer_name }}
                                </div>
                                @if($b->guardian_name)
                                    <div class="text-slate-400 text-[11px]">
                                        {{ str_replace('_', ' ', ucfirst($b->guardian_relation)) }}: {{ $b->guardian_name }}
                                    </div>
                                @endif
                                <div class="text-slate-600 font-medium pt-0.5">
                                    <i class="fa-solid fa-phone text-slate-400 mr-1 text-[10px]"></i> {{ $b->mobile_no }}
                                </div>
                            </td>

                            <!-- Property / Flat Info -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                <div class="font-bold text-slate-800">
                                    Unit No: <span class="text-indigo-700 font-mono text-sm font-black">{{ $b->unit_no }}</span>
                                    @if($b->block_name) (Blk: {{ $b->block_name }}) @endif
                                </div>
                                <div class="text-slate-500">
                                    {{ $b->super_built_up_area }} sq.ft. @ ₹{{ number_format($b->rate_per_sqft, 2) }}/sqft
                                </div>
                                <div class="text-slate-400 text-[11px]">
                                    {{ $b->property_type }}
                                </div>
                            </td>

                            <!-- Financials & Value -->
                            <td class="py-4 px-4 align-top text-xs space-y-1 font-mono">
                                <div>Total: <span class="font-black text-slate-900 text-sm">₹{{ number_format($b->total_booking_value, 2) }}</span></div>
                                <div class="text-[11px] text-slate-500">
                                    Base: ₹{{ number_format($b->consideration_value, 2) }} + GST: ₹{{ number_format($b->tax_amount, 2) }}
                                </div>
                                @if($b->supplementary_value != 0)
                                    <div class="text-[11px] {{ $b->supplementary_value > 0 ? 'text-indigo-600' : 'text-amber-600' }} font-semibold">
                                        Job Sheet: {{ $b->supplementary_value > 0 ? '+' : '' }}₹{{ number_format($b->supplementary_value, 2) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status & Agreement -->
                            <td class="py-4 px-4 align-top text-xs space-y-1">
                                <div>
                                    @if($b->status === 'live')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 mr-1.5 animate-pulse"></span> Live Booking
                                        </span>
                                    @elseif($b->status === 'executed_agreement')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-file-contract mr-1 text-[10px]"></i> Agreement Executed
                                        </span>
                                    @elseif($b->status === 'registered_deed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i class="fa-solid fa-certificate mr-1 text-[10px]"></i> Deed Registered
                                        </span>
                                    @elseif($b->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-ban mr-1 text-[10px]"></i> Cancelled
                                        </span>
                                    @endif
                                </div>

                                @if($b->saleAgreement && $b->saleAgreement->agreement_status === 'executed')
                                    <div class="text-[11px] font-mono text-slate-500">
                                        Agr Val: <span class="font-semibold text-slate-800">₹{{ number_format($b->taxable_agreement_value, 2) }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-1.5">
                                <a href="{{ route('project.bookings.show', [$project->id, $b->id]) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-900 hover:bg-sky-600 text-white rounded-lg text-xs font-medium transition shadow-sm" title="View 360 Overview">
                                    <i class="fa-solid fa-eye mr-1"></i> Overview
                                </a>
                                <a href="{{ route('project.bookings.edit', [$project->id, $b->id]) }}" class="inline-flex items-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs transition" title="Edit Booking">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-file-invoice text-3xl text-slate-300 mb-2 block"></i>
                                No property bookings found for <strong>{{ $project->name }}</strong>.
                                <div class="mt-2">
                                    <a href="{{ route('project.bookings.create', $project->id) }}" class="inline-flex items-center space-x-1 text-xs font-bold text-sky-600 hover:underline">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Create First Booking</span>
                                    </a>
                                </div>
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
