@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.bank-accounts.index') }}" class="hover:text-sky-600">Bank Accounts</a>
                <span>/</span>
                <span class="text-emerald-600">New Account</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Add Bank / Escrow Account</h1>
            <p class="text-xs text-slate-500 mt-0.5">Register banking ledgers for receiving customer payments, loan disbursements, or cash.</p>
        </div>
        <a href="{{ route('admin.bank-accounts.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Create Bank Account Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden"
         x-data="{
            accountType: '{{ old('account_type', 'general') }}',
            accountName: '{{ old('account_name', $company->name ?? '') }}',
            accountNumber: '{{ old('account_number', '') }}',
            bankName: '{{ old('bank_name', '') }}',
            branch: '{{ old('branch', '') }}',
            ifsc: '{{ old('ifsc_code', '') }}',
            accountNick: '{{ old('account_nick_name', '') }}',
            suggestNick() {
                if (!this.accountNick) {
                    if (this.accountType === 'project_linked') {
                        this.accountNick = (this.bankName ? this.bankName + ' - RERA Escrow' : 'RERA Escrow');
                    } else if (this.accountType === 'cash_account') {
                        this.accountNick = 'Cash Account';
                        this.bankName = 'Cash in Hand';
                    }
                }
            }
         }">
        
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-building-columns text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">1.1.3. CREATING BANK ACCOUNTS</h3>
                    <p class="text-[11px] text-emerald-100">Bank Account Master Setup</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-emerald-200 uppercase tracking-wider block">Auto-Generated ID</span>
                <span class="text-sm font-mono font-bold bg-black/25 px-2.5 py-0.5 rounded text-white">{{ $nextAccountCode }}</span>
            </div>
        </div>

        <form action="{{ route('admin.bank-accounts.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div>
                    <label for="company_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <select id="company_id" name="company_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition cursor-pointer">
                        <option value="" disabled {{ old('company_id') ? '' : 'selected' }}>-- Select Company --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('company_id') == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} ({{ $comp->company_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Account Type -->
                <div>
                    <label for="account_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Account Type <span class="text-rose-500">*</span>
                    </label>
                    <select id="account_type" name="account_type" x-model="accountType" @change="suggestNick()" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        <option value="general">General Account (Company Wide)</option>
                        <option value="project_linked">Project Linked (RERA Escrow Account)</option>
                        <option value="cash_account">Cash in Hand / Cash Account</option>
                    </select>
                </div>

                <!-- Select Linked Project (Conditional) -->
                <div x-show="accountType === 'project_linked'" class="md:col-span-2 bg-sky-50/70 p-4 rounded-xl border border-sky-200">
                    <label for="project_id" class="block text-xs font-bold uppercase tracking-wider text-sky-900 mb-1.5 flex items-center">
                        <i class="fa-solid fa-link mr-1.5 text-sky-600"></i>
                        Select Linked Project <span class="text-rose-500 ml-1">*</span>
                    </label>
                    <select id="project_id" name="project_id" :required="accountType === 'project_linked'"
                            class="w-full px-3.5 py-2.5 bg-white border border-sky-300 rounded-xl text-sm font-semibold text-sky-900 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                        <option value="">-- Choose Project for Escrow --</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>
                                🏢 {{ $proj->name }} ({{ $proj->project_code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-sky-700 mt-1">Exclusively receives payments particularly for the respected project only.</p>
                </div>

                <!-- Account Nick Name (Unique) -->
                <div class="md:col-span-2">
                    <label for="account_nick_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nick Name of the Account <span class="text-rose-500">*</span> <span class="text-[11px] font-normal text-slate-400">(UNIQUE - displayed in transaction dropdowns)</span>
                    </label>
                    <input type="text" id="account_nick_name" name="account_nick_name" x-model="accountNick" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="e.g. S & S Infra - SBI Account or Imperial Grand - HDFC Rera Account">
                </div>

                <!-- Account Holder Name -->
                <div>
                    <label for="account_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Account Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="account_name" name="account_name" x-model="accountName" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="e.g. S & S Infrastructure">
                </div>

                <!-- Account Number -->
                <div>
                    <label for="account_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Account Number
                    </label>
                    <input type="text" id="account_number" name="account_number" x-model="accountNumber"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="e.g. 5467755222 or 123456789">
                </div>

                <!-- Bank Name -->
                <div>
                    <label for="bank_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Bank Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="bank_name" name="bank_name" x-model="bankName" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="e.g. Indian Bank or HDFC Bank Limited">
                </div>

                <!-- Branch -->
                <div>
                    <label for="branch" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Branch
                    </label>
                    <input type="text" id="branch" name="branch" x-model="branch"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="e.g. Karimganj">
                </div>

                <!-- IFSC Code -->
                <div>
                    <label for="ifsc_code" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        IFSC Code
                    </label>
                    <input type="text" id="ifsc_code" name="ifsc_code" x-model="ifsc"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="e.g. IDIB000K601 or HDFC0004589">
                </div>

                <!-- Default Account Checkbox -->
                <div class="flex items-center pt-6">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-slate-700">Set as Default Account</span>
                    </label>
                </div>

                <!-- Live Readonly Summary Card (Exact requirement from PDF) -->
                <div class="md:col-span-2 bg-slate-100/90 rounded-xl p-4 border border-slate-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center">
                        <i class="fa-solid fa-receipt mr-1.5 text-emerald-600"></i>
                        Account Details (Readonly preview fetched from entered data)
                    </div>
                    <div class="bg-white p-3.5 rounded-lg border border-slate-200 font-mono text-xs text-slate-700 leading-relaxed space-y-1">
                        <div>Account name : <span class="font-bold text-slate-900" x-text="accountName || '—'"></span></div>
                        <div>Account number : <span class="font-bold text-slate-900" x-text="accountNumber || '—'"></span></div>
                        <div>Bank name : <span class="font-bold text-slate-900" x-text="bankName || '—'"></span></div>
                        <div>Branch : <span class="font-bold text-slate-900" x-text="branch || '—'"></span></div>
                        <div>IFSC : <span class="font-bold text-slate-900" x-text="ifsc || '—'"></span></div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.bank-accounts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Exit Page
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Bank Account</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
