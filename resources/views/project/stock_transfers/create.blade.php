@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    qty: {{ old('quantity', 1) }},
    unitCost: {{ old('unit_cost', 0) }},

    get totalValue() {
        return Math.round((parseFloat(this.qty || 0) * parseFloat(this.unitCost || 0)) * 100) / 100;
    },

    formatNumber(num) {
        return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);
    }
}">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.stock-transfers.index', $project->id) }}" class="hover:text-sky-600">Stock Transfers</a>
                <span>/</span>
                <span class="text-teal-600">Initiate Transfer</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Initiate Inter-Project Stock Transfer</h1>
            <p class="text-xs text-slate-500 mt-0.5">Move materials from <strong>{{ $project->name }}</strong> to another project site with automatic ledger cost adjustment.</p>
        </div>
        <a href="{{ route('project.stock-transfers.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <!-- Transfer Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-teal-700 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-truck-ramp-box text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">OUTWARD STOCK TRANSFER CHALLAN</h3>
                    <p class="text-[11px] text-teal-200">Source Project: {{ $project->name }}</p>
                </div>
            </div>
            <span class="text-sm font-mono font-bold bg-black/40 px-2.5 py-0.5 rounded border border-teal-500/40 text-white">
                {{ $nextTransferCode }}
            </span>
        </div>

        <form action="{{ route('project.stock-transfers.store', $project->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- 1. Destination & Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div>
                    <label for="destination_project_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Destination Project Site <span class="text-rose-500">*</span>
                    </label>
                    <select id="destination_project_id" name="destination_project_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                        <option value="" disabled selected>-- Select Destination Project --</option>
                        @foreach($otherProjects as $op)
                            <option value="{{ $op->id }}" {{ old('destination_project_id') == $op->id ? 'selected' : '' }}>
                                🏗️ {{ $op->name }} ({{ $op->project_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="transfer_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Transfer Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="transfer_date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                </div>

            </div>

            <!-- 2. Material Particulars -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <div class="sm:col-span-2">
                    <label for="material_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Material Name & Grade <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="material_name" name="material_name" value="{{ old('material_name') }}" required
                           placeholder="e.g. 500 Bags PPC Cement / 16mm TMT Rods (2.5 MT)"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                </div>

                <div>
                    <label for="unit_measure" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Unit of Measure <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="unit_measure" name="unit_measure" value="{{ old('unit_measure', 'Bags') }}" required
                           placeholder="e.g. Bags, Tonnes, Bundles, Pcs"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                </div>

            </div>

            <!-- 3. Math Block -->
            <div class="p-5 bg-gradient-to-br from-slate-900 to-teal-950 text-white rounded-2xl shadow-md border border-slate-800 space-y-3 font-mono">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-[11px] font-bold uppercase tracking-wider text-teal-300 mb-1 font-sans">
                            Transfer Quantity <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" step="0.01" id="quantity" name="quantity" x-model.number="qty" required min="0.01"
                               class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label for="unit_cost" class="block text-[11px] font-bold uppercase tracking-wider text-teal-300 mb-1 font-sans">
                            Unit Book Cost (₹) <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" step="0.01" id="unit_cost" name="unit_cost" x-model.number="unitCost" required min="0"
                               class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-sans text-slate-400">Total Material Transfer Value:</span>
                    <span class="text-xl font-black text-teal-400" x-text="'₹' + formatNumber(totalValue)"></span>
                </div>

            </div>

            <!-- 4. Logistics Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div>
                    <label for="vehicle_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Vehicle / Truck Registration No
                    </label>
                    <input type="text" id="vehicle_no" name="vehicle_no" value="{{ old('vehicle_no') }}"
                           placeholder="e.g. AS-11-CA-1234"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                </div>

                <div>
                    <label for="challan_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Gate Pass / Challan Ref No
                    </label>
                    <input type="text" id="challan_no" name="challan_no" value="{{ old('challan_no') }}"
                           placeholder="e.g. CHN-2026-90"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">
                </div>

                <div class="sm:col-span-2">
                    <label for="remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Transfer Remarks / Authorization Reason
                    </label>
                    <textarea id="remarks" name="remarks" rows="2"
                              placeholder="e.g. Excess stock moved to complete foundation slab casting at site 2"
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none transition">{{ old('remarks') }}</textarea>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('project.stock-transfers.index', $project->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-teal-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Execute Stock Transfer</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
