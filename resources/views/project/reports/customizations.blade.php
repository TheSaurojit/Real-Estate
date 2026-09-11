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
                <span class="text-violet-600 font-bold">2.3.5 Booking Customizations</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Booking Customization Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">Itemized engineering job sheets and alterations searchable by particular (Civil, Electrical, Plumbing, etc.) with rate and quantity breakdowns.</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Export to Excel Button -->
            <a href="{{ route('project.reports.customizations', array_merge(request()->query(), ['project' => $project->id, 'export' => 'excel'])) }}"
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

    <!-- Keyword & Dropdown Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('project.reports.customizations', $project->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Customer, Unit, Booking ID, Description..."
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Particulars Dropdown -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Particulars / Trade</label>
                <select name="particular" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:outline-none transition">
                    <option value="">All Particulars</option>
                    @foreach($availableParticulars as $p)
                        <option value="{{ $p }}" {{ request('particular') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Job Type Dropdown -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Job Type</label>
                <select name="job_type" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:outline-none transition">
                    <option value="">All Types</option>
                    <option value="addon" {{ request('job_type') === 'addon' ? 'selected' : '' }}>Add-on (+)</option>
                    <option value="dislodge" {{ request('job_type') === 'dislodge' ? 'selected' : '' }}>Dislodge (-)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filter</span>
                </button>
                @if(request()->hasAny(['search', 'particular', 'job_type']))
                    <a href="{{ route('project.reports.customizations', $project->id) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customizations Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-toolbox text-violet-600 text-sm"></i>
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">Customization Job Sheets ({{ $customizations->total() }} records)</h3>
            </div>
            <span class="text-xs text-slate-400 font-medium">Page {{ $customizations->currentPage() }} of {{ $customizations->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[10px] tracking-wider whitespace-nowrap">
                        <th class="py-3 px-3">Booking ID</th>
                        <th class="py-3 px-3">Customer Name</th>
                        <th class="py-3 px-3">Block - Floor - Unit</th>
                        <th class="py-3 px-3 text-center">Type</th>
                        <th class="py-3 px-3">Particulars</th>
                        <th class="py-3 px-3 min-w-[200px]">Description</th>
                        <th class="py-3 px-3 text-right">Material Rate</th>
                        <th class="py-3 px-3 text-right">Labour Rate</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-center">Unit</th>
                        <th class="py-3 px-3 text-right">Material Total</th>
                        <th class="py-3 px-3 text-right">Labour Total</th>
                        <th class="py-3 px-4 text-right">Item Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($customizations as $c)
                        @php
                            $b = $c->booking;
                            $unitInfo = $b ? trim("{$b->block_name} - {$b->floor_no} - {$b->unit_no}", ' -') : 'N/A';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                @if($b)
                                    <a href="{{ route('project.bookings.show', [$project->id, $b->id]) }}" class="text-sky-600 hover:underline">
                                        {{ $b->booking_code }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800 whitespace-nowrap">{{ $b?->customer_name ?? 'N/A' }}</td>
                            <td class="py-3 px-3 text-slate-600 whitespace-nowrap">{{ $unitInfo }}</td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($c->job_type === 'addon')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-plus mr-1"></i> Add-on
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-minus mr-1"></i> Dislodge
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold text-violet-700 whitespace-nowrap">
                                <span class="px-2 py-1 bg-violet-50 rounded-md border border-violet-200/60">{{ $c->particular }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">{{ $c->description }}</td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700 whitespace-nowrap">₹{{ number_format($c->material_rate, 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700 whitespace-nowrap">₹{{ number_format($c->labour_rate, 2) }}</td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-800 whitespace-nowrap">{{ number_format($c->quantity, 2) }}</td>
                            <td class="py-3 px-3 text-center text-slate-500 whitespace-nowrap">{{ $c->unit_measure ?? 'Units' }}</td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700 whitespace-nowrap">₹{{ number_format($c->material_total, 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700 whitespace-nowrap">₹{{ number_format($c->labour_total, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $c->job_type === 'addon' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $c->job_type === 'addon' ? '+' : '-' }}₹{{ number_format($c->job_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-toolbox text-4xl mb-3 block text-slate-300"></i>
                                <p class="text-sm font-semibold">No customization job sheets found.</p>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting your keyword, particular, or job type filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customizations->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $customizations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
