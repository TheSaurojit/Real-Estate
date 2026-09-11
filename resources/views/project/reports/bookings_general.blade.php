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
                <span class="text-sky-600 font-bold">2.3.1.1 Booking General Information</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Booking General Information Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive master registry of all customer allocations, unit specs, KYC credentials, and stage progression.</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Export to Excel Button -->
            <a href="{{ route('project.reports.bookings.general', array_merge(request()->query(), ['project' => $project->id, 'export' => 'excel'])) }}"
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

    <!-- Search & Keyword Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('project.reports.bookings.general', $project->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Keyword Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ID, Customer, Unit, PAN, Mobile..."
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Category</label>
                <select name="category" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    <option value="">All Categories</option>
                    <option value="purchaser" {{ request('category') === 'purchaser' ? 'selected' : '' }}>Purchaser</option>
                    <option value="landowner" {{ request('category') === 'landowner' ? 'selected' : '' }}>Landowner</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Booking Status</label>
                <select name="status" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    <option value="">All Statuses</option>
                    <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>Live Booking</option>
                    <option value="executed_agreement" {{ request('status') === 'executed_agreement' ? 'selected' : '' }}>Executed Agreement</option>
                    <option value="registered_deed" {{ request('status') === 'registered_deed' ? 'selected' : '' }}>Registered Deed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'category', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('project.reports.bookings.general', $project->id) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-table-list text-sky-600 text-sm"></i>
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">Booking General Information Registry</h3>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Total Records: <strong class="text-slate-800 font-mono">{{ $bookings->total() }}</strong>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="text-[11px] font-bold uppercase text-slate-600 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Booking ID</th>
                        <th class="py-3 px-4">Booking Date</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Contact Details</th>
                        <th class="py-3 px-4">PAN / GSTIN</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Block - Floor - Unit</th>
                        <th class="py-3 px-4">Unit Type</th>
                        <th class="py-3 px-4">Parking Details</th>
                        <th class="py-3 px-4 text-right">Built-up (Sq.Ft)</th>
                        <th class="py-3 px-4 text-right">Super Area (Sq.Ft)</th>
                        <th class="py-3 px-4">Reference/Source</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Gross Booking Val</th>
                        <th class="py-3 px-4">Sale Agreement Status</th>
                        <th class="py-3 px-4 text-right">Agreement Value</th>
                        <th class="py-3 px-4">Finance Status</th>
                        <th class="py-3 px-4 text-right">Sanctioned Amount</th>
                        <th class="py-3 px-4">Sale Deed Status</th>
                        <th class="py-3 px-4 text-right">Sale Deed Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Booking ID -->
                            <td class="py-3 px-4 font-mono font-bold text-sky-700">
                                <a href="{{ route('project.bookings.show', [$project->id, $b->id]) }}" class="hover:underline">
                                    {{ $b->booking_code }}
                                </a>
                            </td>

                            <!-- Booking Date -->
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $b->booking_date ? $b->booking_date->format('d M Y') : 'N/A' }}
                            </td>

                            <!-- Customer Name -->
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $b->customer_name }}
                            </td>

                            <!-- Contact No & Email -->
                            <td class="py-3 px-4">
                                <div>{{ $b->mobile_no }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->email_id ?? '-' }}</div>
                            </td>

                            <!-- PAN / GSTIN -->
                            <td class="py-3 px-4 font-mono">
                                <div>PAN: {{ $b->pan_number ?: 'N/A' }}</div>
                                @if($b->gstin)
                                    <div class="text-[10px] text-slate-400 font-semibold">GST: {{ $b->gstin }}</div>
                                @endif
                            </td>

                            <!-- Category -->
                            <td class="py-3 px-4">
                                @if($b->is_landowner_allocation)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                                        Landowner
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">
                                        Purchaser
                                    </span>
                                @endif
                            </td>

                            <!-- Block - Floor - Unit -->
                            <td class="py-3 px-4 font-bold text-slate-700">
                                {{ $b->block_name ?? '-' }} / Flr: {{ $b->floor_no ?? '-' }} / <span class="text-indigo-600 font-mono">{{ $b->unit_no }}</span>
                            </td>

                            <!-- Unit Type -->
                            <td class="py-3 px-4">
                                {{ $b->property_type ?? 'Residential Flat' }}
                            </td>

                            <!-- Parking -->
                            <td class="py-3 px-4">
                                @if($b->parking_type)
                                    <span class="font-medium text-slate-700">{{ ucfirst(str_replace('_', ' ', $b->parking_type)) }}</span>
                                    @if($b->parking_no)
                                        <span class="text-[10px] text-slate-400 font-mono">({{ $b->parking_no }})</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">No Parking</span>
                                @endif
                            </td>

                            <!-- Built-up Area -->
                            <td class="py-3 px-4 text-right font-mono">
                                {{ number_format($b->built_up_area, 2) }}
                            </td>

                            <!-- Super Area -->
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                                {{ number_format($b->super_built_up_area, 2) }}
                            </td>

                            <!-- Reference/Source -->
                            <td class="py-3 px-4">
                                {{ $b->reference_source ?: 'Direct Walk-in' }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if($b->status === 'live')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">LIVE</span>
                                @elseif($b->status === 'executed_agreement')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">AGREEMENT</span>
                                @elseif($b->status === 'registered_deed')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">DEED REGISTERED</span>
                                @elseif($b->status === 'cancelled')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">CANCELLED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ strtoupper($b->status) }}</span>
                                @endif
                            </td>

                            <!-- Gross Booking Value -->
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                ₹{{ number_format($b->gross_booking_value > 0 ? $b->gross_booking_value : $b->total_booking_value, 2) }}
                            </td>

                            <!-- Sale Agreement Status -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b->saleAgreement?->agreement_status === 'Executed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $b->saleAgreement?->agreement_status ?? 'Pending' }}
                                </span>
                            </td>

                            <!-- Sale Agreement Value -->
                            <td class="py-3 px-4 text-right font-mono">
                                ₹{{ number_format($b->saleAgreement?->agreement_value ?? 0, 2) }}
                            </td>

                            <!-- Finance Status -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b->bankFinance?->finance_status === 'Sanctioned' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $b->bankFinance?->finance_status ?? 'Not Applicable' }}
                                </span>
                            </td>

                            <!-- Sanctioned Amount -->
                            <td class="py-3 px-4 text-right font-mono">
                                ₹{{ number_format($b->bankFinance?->sanctioned_amount ?? 0, 2) }}
                            </td>

                            <!-- Sale Deed Status -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b->saleDeed?->status === 'Executed' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $b->saleDeed?->status ?? 'Pending' }}
                                </span>
                            </td>

                            <!-- Sale Deed Value -->
                            <td class="py-3 px-4 text-right font-mono">
                                ₹{{ number_format($b->saleDeed?->sale_deed_value ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="20" class="py-12 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                                No booking records found matching your filter criteria.
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
