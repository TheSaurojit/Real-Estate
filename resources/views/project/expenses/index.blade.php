@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-cyan-600">Expenses</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Construction Site Expenses & Purchases</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track raw materials (Cement, Steel, Bricks), labor contractor payouts, machinery, and site overheads.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('project.stock-transfers.index', $project->id) }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition border border-slate-200 flex items-center space-x-1.5">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Stock Transfers</span>
            </a>
            <a href="{{ route('project.expenses.create', $project->id) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-cyan-600/20 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Record Site Expense</span>
            </a>
        </div>
    </div>

    <!-- Expense Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Material Purchases -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-cyan-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-trowel-bricks text-[10px]"></i>
                <span>Material Purchases</span>
            </span>
            <h3 class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($stats['total_material'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Cement, TMT Steel, Aggregates
            </div>
        </div>

        <!-- Total Labor / Contractors -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-amber-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-person-digging text-[10px]"></i>
                <span>Labor & Contractors</span>
            </span>
            <h3 class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($stats['total_labor'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Masonry, Shuttering, Fabrication
            </div>
        </div>

        <!-- Total Overheads & Admin -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase text-purple-600 tracking-wider flex items-center space-x-1">
                <i class="fa-solid fa-truck-monster text-[10px]"></i>
                <span>Overheads & Equipment</span>
            </span>
            <h3 class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($stats['total_overhead'], 2) }}</h3>
            <div class="mt-2 text-[11px] text-slate-400">
                Fuel, Water, Machinery Rental
            </div>
        </div>

        <!-- Total Direct Site Expenses -->
        <div class="bg-gradient-to-br from-slate-900 to-cyan-950 text-white p-4 rounded-2xl shadow-md border border-slate-800 flex flex-col justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-cyan-400 tracking-wider block">Total Site Expenses</span>
                <h3 class="text-xl font-black font-mono mt-1 text-white">₹{{ number_format($stats['total_expenses'], 2) }}</h3>
            </div>
            <div class="text-[11px] text-cyan-200 mt-2">
                Cumulative Site Cost
            </div>
        </div>

    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Search Input -->
        <form action="{{ route('project.expenses.index', $project->id) }}" method="GET" class="w-full md:w-96 flex items-center">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search Expense Code, Item, Supplier, Invoice..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
            </div>
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
        </form>

        <!-- Category Tabs -->
        <div class="flex items-center space-x-1.5 overflow-x-auto w-full md:w-auto text-xs font-medium">
            <a href="{{ route('project.expenses.index', ['project' => $project->id, 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ !request('category') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Categories
            </a>
            <a href="{{ route('project.expenses.index', ['project' => $project->id, 'category' => 'material_purchase', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('category') === 'material_purchase' ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Materials
            </a>
            <a href="{{ route('project.expenses.index', ['project' => $project->id, 'category' => 'labor_contractor', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('category') === 'labor_contractor' ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Labor
            </a>
            <a href="{{ route('project.expenses.index', ['project' => $project->id, 'category' => 'site_overheads', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('category') === 'site_overheads' ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Overheads
            </a>
        </div>

    </div>

    <!-- Expenses Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Expense Code & Date</th>
                        <th class="py-3.5 px-4">Category & Item</th>
                        <th class="py-3.5 px-4">Supplier / Vendor</th>
                        <th class="py-3.5 px-4">Quantity & Rate</th>
                        <th class="py-3.5 px-4">Gross Amount</th>
                        <th class="py-3.5 px-4">Payment & Invoice</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- Expense Code & Date -->
                            <td class="py-4 px-4 align-top">
                                <span class="font-mono font-bold text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-300 text-slate-800">
                                    {{ $e->expense_code }}
                                </span>
                                <div class="text-xs text-slate-400 mt-1">
                                    <i class="fa-regular fa-calendar text-[11px] mr-1"></i> {{ $e->expense_date->format('d M Y') }}
                                </div>
                            </td>

                            <!-- Category & Item -->
                            <td class="py-4 px-4 align-top text-xs space-y-1">
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $e->expense_category === 'material_purchase' ? 'bg-cyan-100 text-cyan-800' : ($e->expense_category === 'labor_contractor' ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800') }}">
                                        {{ str_replace('_', ' ', $e->expense_category) }}
                                    </span>
                                </div>
                                <div class="font-bold text-slate-900 text-sm">
                                    {{ $e->item_name }}
                                </div>
                                @if($e->description)
                                    <div class="text-slate-500 text-[11px]">{{ $e->description }}</div>
                                @endif
                            </td>

                            <!-- Supplier / Vendor -->
                            <td class="py-4 px-4 align-top text-xs">
                                @if($e->supplier)
                                    <div class="font-bold text-slate-800">{{ $e->supplier->name }}</div>
                                    <div class="text-slate-500 text-[11px]">{{ $e->supplier->contact_person }} ({{ $e->supplier->mobile_no }})</div>
                                @else
                                    <div class="text-slate-400 italic">Direct Site Payment / Worker</div>
                                @endif
                            </td>

                            <!-- Quantity & Rate -->
                            <td class="py-4 px-4 align-top text-xs font-mono text-slate-700">
                                <div>{{ number_format($e->quantity, 2) }} {{ $e->unit_measure }}</div>
                                <div class="text-[11px] text-slate-400">@ ₹{{ number_format($e->unit_rate, 2) }}</div>
                            </td>

                            <!-- Gross Amount -->
                            <td class="py-4 px-4 align-top font-mono">
                                <span class="text-base font-black text-rose-700">
                                    ₹{{ number_format($e->gross_amount, 2) }}
                                </span>
                                @if($e->tax_amount > 0)
                                    <div class="text-[10px] text-slate-400 font-sans">
                                        (Incl. GST: ₹{{ number_format($e->tax_amount, 2) }})
                                    </div>
                                @endif
                            </td>

                            <!-- Payment & Invoice -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                <div class="font-bold uppercase text-slate-700">
                                    {{ $e->payment_mode }} ({{ $e->payment_status }})
                                </div>
                                @if($e->invoice_no)
                                    <div class="font-mono text-slate-500 text-[11px]">Inv: {{ $e->invoice_no }}</div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right">
                                <form action="{{ route('project.expenses.destroy', [$project->id, $e->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-trowel-bricks text-3xl text-slate-300 mb-2 block"></i>
                                No construction site expenses recorded yet for <strong>{{ $project->name }}</strong>.
                                <div class="mt-2">
                                    <a href="{{ route('project.expenses.create', $project->id) }}" class="inline-flex items-center space-x-1 text-xs font-bold text-cyan-600 hover:underline">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Record First Material Purchase or Expense</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $expenses->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
