@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <span class="text-rose-600">Companies Master</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Developer Companies & Legal Entities</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage real estate corporate entities, letterhead logos, PAN, and GSTIN registration.</p>
        </div>
        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.companies.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-rose-600/20 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Add New Company</span>
            </a>
        @endif
    </div>

    <!-- Companies List Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs font-bold uppercase text-slate-600 tracking-wider">
                Registered Developer Companies ({{ $companies->count() }})
            </div>
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-shield-halved text-rose-500 mr-1"></i> Super Admin Multi-Company Registry
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Company & Logo</th>
                        <th class="py-3.5 px-4">Code (Prefix)</th>
                        <th class="py-3.5 px-4">Taxation IDs (PAN & GSTIN)</th>
                        <th class="py-3.5 px-4">Contact & Address</th>
                        <th class="py-3.5 px-4">Linked Projects</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($companies as $comp)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- Company Name & Logo -->
                            <td class="py-4 px-4 align-top">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($comp->logo_path)
                                            <img src="{{ asset('storage/' . $comp->logo_path) }}" alt="Logo" class="w-full h-full object-contain p-0.5">
                                        @else
                                            <i class="fa-solid fa-building text-slate-400 text-lg"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">{{ $comp->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $comp->email ?? 'No email specified' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Code -->
                            <td class="py-4 px-4 align-top">
                                <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-800 font-mono font-bold text-xs border border-rose-200">
                                    {{ $comp->company_code }}
                                </span>
                            </td>

                            <!-- PAN & GSTIN -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5 font-mono">
                                <div>PAN: <span class="font-bold text-slate-800">{{ $comp->pan_number ?? 'N/A' }}</span></div>
                                <div class="text-slate-500">GSTIN: <span class="font-semibold text-slate-700">{{ $comp->gstin ?? 'N/A' }}</span></div>
                            </td>

                            <!-- Contact & Address -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5 max-w-xs">
                                <div class="font-medium text-slate-800">{{ $comp->contact_primary }}</div>
                                <div class="text-slate-500 truncate">{{ $comp->address }}</div>
                            </td>

                            <!-- Linked Projects -->
                            <td class="py-4 px-4 align-top text-xs">
                                <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-800 font-bold text-[11px] border border-sky-200">
                                    {{ $comp->projects_count }} Projects
                                </span>
                                <div class="text-slate-400 text-[10px] mt-1">{{ $comp->bank_accounts_count }} Bank Accounts</div>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-2">
                                @if(auth()->user()->isSuperAdmin())
                                    <a href="{{ route('admin.companies.edit', $comp->id) }}" class="inline-flex items-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs transition" title="Edit Company">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @if($comp->projects_count === 0 && $comp->bank_accounts_count === 0 && $companies->count() > 1)
                                        <form action="{{ route('admin.companies.destroy', $comp->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this company?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition" title="Delete Company">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                No companies registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
