@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    qty: {{ old('quantity', 1) }},
    rate: {{ old('unit_rate', 0) }},
    taxRate: {{ old('tax_rate', 0) }},

    get subTotal() {
        return Math.round((parseFloat(this.qty || 0) * parseFloat(this.rate || 0)) * 100) / 100;
    },

    get taxAmount() {
        return Math.round(((this.subTotal * parseFloat(this.taxRate || 0)) / 100) * 100) / 100;
    },

    get grossAmount() {
        return Math.round((this.subTotal + this.taxAmount) * 100) / 100;
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
                <a href="{{ route('project.expenses.index', $project->id) }}" class="hover:text-sky-600">Expenses</a>
                <span>/</span>
                <span class="text-cyan-600">Record Expense</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Record Site Purchase / Expense</h1>
            <p class="text-xs text-slate-500 mt-0.5">Enter material purchases, labor contractor bills, machinery rent, or direct site expenses.</p>
        </div>
        <a href="{{ route('project.expenses.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Expenses
        </a>
    </div>

    <!-- Expense Entry Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-cyan-700 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice-dollar text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">NEW SITE EXPENSE VOUCHER</h3>
                    <p class="text-[11px] text-cyan-200">{{ $project->name }} ({{ $project->project_code }})</p>
                </div>
            </div>
            <span class="text-sm font-mono font-bold bg-black/40 px-2.5 py-0.5 rounded border border-cyan-500/40 text-white">
                {{ $nextExpenseCode }}
            </span>
        </div>

        <form action="{{ route('project.expenses.store', $project->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- 1. Category & Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Category -->
                <div>
                    <label for="expense_category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Expense Category <span class="text-rose-500">*</span>
                    </label>
                    <select id="expense_category" name="expense_category" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                        <option value="material_purchase" {{ old('expense_category') == 'material_purchase' ? 'selected' : '' }}>🧱 Raw Material Purchase (Cement, Steel, Bricks)</option>
                        <option value="labor_contractor" {{ old('expense_category') == 'labor_contractor' ? 'selected' : '' }}>👷 Labor & Contractor Payouts (Civil, Shuttering)</option>
                        <option value="site_overheads" {{ old('expense_category') == 'site_overheads' ? 'selected' : '' }}>⚡ Site Overheads (Fuel, Water Tankers)</option>
                        <option value="machinery_equipment" {{ old('expense_category') == 'machinery_equipment' ? 'selected' : '' }}>🚜 Machinery Rental & Equipment</option>
                        <option value="statutory_permits" {{ old('expense_category') == 'statutory_permits' ? 'selected' : '' }}>🏛️ Municipal Permits & Statutory Fees</option>
                        <option value="administrative" {{ old('expense_category') == 'administrative' ? 'selected' : '' }}>📁 Administrative / Office Expenses</option>
                    </select>
                </div>

                <!-- Expense Date -->
                <div>
                    <label for="expense_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Expense Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                </div>

                <!-- Supplier / Vendor -->
                <div>
                    <label for="supplier_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Supplier / Vendor
                    </label>
                    <select id="supplier_id" name="supplier_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                        <option value="">-- Direct Site Worker / No Vendor --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                🏭 {{ $s->name }} ({{ $s->category }})
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- 2. Item Name & Description -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <div class="sm:col-span-2">
                    <label for="item_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Item / Work Particulars <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="item_name" name="item_name" value="{{ old('item_name') }}" required
                           placeholder="e.g. UltraTech PPC Cement 50kg Bags / 12mm TMT Steel Rods"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                </div>

                <div>
                    <label for="unit_measure" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Unit of Measurement <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="unit_measure" name="unit_measure" value="{{ old('unit_measure', 'Bags') }}" required
                           placeholder="e.g. Bags, Tonnes, Sq.Ft, LS, Days, Litres"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                </div>

                <div class="sm:col-span-3">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Technical Specs / Quality Grade / Remarks
                    </label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}"
                           placeholder="e.g. 53-grade OPC cement for 3rd floor roof casting work"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                </div>

            </div>

            <!-- 3. Reactive Pricing & Quantity Engine -->
            <div class="p-5 bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900 text-white rounded-2xl shadow-md border border-slate-800 space-y-4 font-mono">
                
                <div class="text-xs uppercase font-bold text-cyan-400 font-sans tracking-wider pb-2 border-b border-slate-700 flex items-center justify-between">
                    <span>Expense Cost & Tax Calculator</span>
                    <span class="text-xs font-normal text-slate-300">Auto-Computed</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-[11px] font-bold uppercase tracking-wider text-cyan-300 mb-1 font-sans">
                            Quantity <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" step="0.01" id="quantity" name="quantity" x-model.number="qty" required min="0.01"
                               class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                    </div>

                    <!-- Unit Rate -->
                    <div>
                        <label for="unit_rate" class="block text-[11px] font-bold uppercase tracking-wider text-cyan-300 mb-1 font-sans">
                            Unit Rate (₹) <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" step="0.01" id="unit_rate" name="unit_rate" x-model.number="rate" required min="0"
                               class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                    </div>

                    <!-- GST Tax Rate -->
                    <div>
                        <label for="tax_rate" class="block text-[11px] font-bold uppercase tracking-wider text-cyan-300 mb-1 font-sans">
                            GST Tax Rate (%)
                        </label>
                        <select id="tax_rate" name="tax_rate" x-model.number="taxRate"
                                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs font-bold text-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                            <option value="0">0.00% (Exempt / Labor)</option>
                            <option value="5">5.00% (Standard Material)</option>
                            <option value="12">12.00% (Pipes & Fixtures)</option>
                            <option value="18">18.00% (Steel, Cement, Paint)</option>
                            <option value="28">28.00% (Luxury Finishing)</option>
                        </select>
                    </div>

                </div>

                <!-- Computed Summary Tiles -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-800 text-xs">
                    <div>
                        <span class="text-slate-400 font-sans block text-[11px]">Sub-Total (Before Tax)</span>
                        <span class="text-base font-bold text-slate-200" x-text="'₹' + formatNumber(subTotal)"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-sans block text-[11px]">Tax Amount</span>
                        <span class="text-base font-bold text-cyan-300" x-text="'₹' + formatNumber(taxAmount)"></span>
                    </div>
                    <div class="bg-cyan-950/80 p-2.5 rounded-xl border border-cyan-800 text-right">
                        <span class="text-cyan-400 font-sans block text-[11px] uppercase font-bold">Total Gross Expense</span>
                        <span class="text-xl font-black text-white" x-text="'₹' + formatNumber(grossAmount)"></span>
                    </div>
                </div>

            </div>

            <!-- 4. Invoice & Payment Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div>
                    <label for="invoice_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Supplier Invoice / Bill No
                    </label>
                    <input type="text" id="invoice_no" name="invoice_no" value="{{ old('invoice_no') }}"
                           placeholder="e.g. INV-2026-887"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                </div>

                <div>
                    <label for="payment_mode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payment Mode <span class="text-rose-500">*</span>
                    </label>
                    <select id="payment_mode" name="payment_mode" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash in Hand</option>
                        <option value="upi">UPI / Online</option>
                    </select>
                </div>

                <div>
                    <label for="bank_account_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Debiting Bank Account
                    </label>
                    <select id="bank_account_id" name="bank_account_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                        <option value="">-- Cash Account / Cash in Hand --</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                🏦 {{ $acc->account_nick_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="payment_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Payment Status <span class="text-rose-500">*</span>
                    </label>
                    <select id="payment_status" name="payment_status" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none transition">
                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid / Cleared</option>
                        <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Credit / Pending Payment</option>
                        <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                    </select>
                </div>

            </div>

            <!-- Submit Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('project.expenses.index', $project->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-cyan-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Record & Generate Expense Voucher</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
