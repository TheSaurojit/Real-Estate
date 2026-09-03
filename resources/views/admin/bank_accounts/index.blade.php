@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <span class="text-emerald-600">Bank Accounts</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Bank Accounts & Escrow Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage builder company bank accounts, RERA designated escrow accounts, and cash accounts.</p>
        </div>
        <a href="{{ route('admin.bank-accounts.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/20 transition">
            <i class="fa-solid fa-plus"></i>
            <span>Add Bank Account</span>
        </a>
    </div>

    <!-- Accounts Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs font-bold uppercase text-slate-600 tracking-wider">
                Configured Bank & Cash Ledgers ({{ $bankAccounts->count() }})
            </div>
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-lock text-emerald-500 mr-1"></i> Safeguarded against accidental deletion if transactions exist
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Account ID & Nick Name</th>
                        <th class="py-3.5 px-4">Company Name</th>
                        <th class="py-3.5 px-4">Account Type & Linked Project</th>
                        <th class="py-3.5 px-4">Bank & Branch</th>
                        <th class="py-3.5 px-4">Account Number & IFSC</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bankAccounts as $account)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- Account Code & Nick Name -->
                            <td class="py-4 px-4 align-top">
                                <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 font-mono font-bold text-xs border border-emerald-200">
                                    {{ $account->account_code }}
                                </span>
                                <div class="mt-1 font-bold text-slate-800 text-sm">
                                    {{ $account->account_nick_name }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    Holder: <span class="text-slate-600 font-medium">{{ $account->account_name }}</span>
                                </div>
                            </td>

                            <!-- Company Name -->
                            <td class="py-4 px-4 align-top text-xs">
                                @if($account->company)
                                    <div class="font-bold text-slate-800 text-sm">
                                        🏢 {{ $account->company->name }}
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        Company Code: <span class="font-semibold text-slate-600">{{ $account->company->company_code }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">N/A</span>
                                @endif
                            </td>

                            <!-- Account Type & Project -->
                            <td class="py-4 px-4 align-top text-xs">
                                @if($account->account_type === 'project_linked')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-300">
                                        <i class="fa-solid fa-link mr-1"></i> RERA / Project Linked
                                    </span>
                                    <div class="mt-1 font-semibold text-slate-700">
                                        🏗️ {{ $account->project->name ?? 'N/A' }}
                                    </div>
                                @elseif($account->account_type === 'cash_account')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-money-bill-wave mr-1"></i> Cash Account
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        General Company Account
                                    </span>
                                @endif

                                @if($account->is_default)
                                    <div class="mt-1">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                            ★ Default Account
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Bank & Branch -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                <div class="font-bold text-slate-800">{{ $account->bank_name }}</div>
                                <div class="text-slate-500">Branch: <span class="font-medium text-slate-700">{{ $account->branch ?? 'N/A' }}</span></div>
                            </td>

                            <!-- Account Number & IFSC -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5 font-mono">
                                <div>A/c: <span class="font-bold text-slate-800">{{ $account->account_number ?? 'N/A' }}</span></div>
                                <div class="text-slate-500">IFSC: <span class="font-semibold text-slate-700">{{ $account->ifsc_code ?? 'N/A' }}</span></div>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-2">
                                <a href="{{ route('admin.bank-accounts.edit', $account->id) }}" class="inline-flex items-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs transition" title="Edit Bank Account">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.bank-accounts.destroy', $account->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this bank account?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition" title="Delete Bank Account">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                No bank accounts configured yet. <a href="{{ route('admin.bank-accounts.create') }}" class="text-emerald-600 font-semibold underline">Add one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
