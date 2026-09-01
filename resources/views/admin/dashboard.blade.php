@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-sky-600 mb-1">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Admin Master Panel</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">System Overview & Master Setup</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage developer company profile, projects, auto-numbering, and bank accounts.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-sky-600/20 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Create New Project</span>
            </a>
            <a href="{{ route('admin.bank-accounts.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/20 transition">
                <i class="fa-solid fa-building-columns"></i>
                <span>Add Bank Account</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Projects Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Projects</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $projectsCount }}</h3>
                    <p class="text-xs text-emerald-600 font-medium mt-1">{{ $activeProjectsCount }} Active Sites</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-city"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.projects.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 flex items-center justify-between">
                    <span>Manage Projects</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Bank Accounts Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bank Accounts</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $bankAccountsCount }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">General & RERA Escrow</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.bank-accounts.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center justify-between">
                    <span>Manage Bank Accounts</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Users Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Team / Staff</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $usersCount }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">Authorized Users</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700 flex items-center justify-between">
                    <span>Manage Staff</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Company Master Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Company Code</p>
                    <h3 class="text-xl font-bold text-slate-800 mt-1">{{ $company->company_code ?? 'SSI' }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1 truncate max-w-[130px]">{{ $company->name ?? 'Developer' }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.companies.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center justify-between">
                    <span>Manage Companies</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Active Projects List & Quick Launch -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Real Estate Projects Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <i class="fa-solid fa-city text-sky-600"></i>
                    <span>Real Estate Projects</span>
                </h3>
                <a href="{{ route('admin.projects.create') }}" class="text-xs font-semibold text-sky-600 hover:underline">
                    + Add Project
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="text-xs font-semibold uppercase text-slate-400 bg-slate-50 border-y border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Project</th>
                            <th class="py-3 px-4">Code & Nick</th>
                            <th class="py-3 px-4">Land Details</th>
                            <th class="py-3 px-4">RERA Status</th>
                            <th class="py-3 px-4 text-right">Workspace</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentProjects as $proj)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800">{{ $proj->name }}</div>
                                    <div class="text-xs text-slate-400 truncate max-w-xs">{{ $proj->full_address }}</div>
                                </td>
                                <td class="py-3 px-4 font-mono text-xs">
                                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 font-bold border border-sky-200">{{ $proj->project_code }}</span>
                                    <span class="ml-1 text-slate-500 font-sans">({{ $proj->nick_name }})</span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <div>Dag: <span class="font-medium text-slate-700">{{ $proj->daag_no ?? '-' }}</span>, Patta: <span class="font-medium text-slate-700">{{ $proj->patta_no ?? '-' }}</span></div>
                                    <div class="text-slate-400">Mouza: {{ $proj->mouza ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if($proj->rera_category === 'registered')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            RERA Reg.
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600">
                                            {{ ucfirst($proj->rera_category) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('project.dashboard', $proj->id) }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-sky-600 text-white rounded-lg text-xs font-medium transition shadow-sm">
                                        <span>Open</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 text-sm">
                                    No projects created yet. <a href="{{ route('admin.projects.create') }}" class="text-sky-600 font-medium underline">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Col: Quick Links & Setup Guide -->
        <div class="space-y-6">
            
            <!-- Quick Settings Card -->
            <div class="bg-gradient-to-br from-slate-900 to-sky-950 rounded-2xl text-white p-6 shadow-md border border-slate-800">
                <h3 class="text-base font-bold mb-3 flex items-center space-x-2 text-sky-400">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Master Configuration</span>
                </h3>
                <p class="text-xs text-slate-300 mb-4 leading-relaxed">
                    Configure company letterheads, auto-numbering prefixes, and escrow bank accounts before launching live project bookings.
                </p>
                <div class="space-y-2 text-xs font-medium">
                    <a href="{{ route('admin.autonumber.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition border border-slate-700/60">
                        <span class="flex items-center space-x-2">
                            <i class="fa-solid fa-hashtag text-cyan-400 w-4"></i>
                            <span>Auto-Numbering Sequences</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                    </a>
                    <a href="{{ route('admin.bank-accounts.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition border border-slate-700/60">
                        <span class="flex items-center space-x-2">
                            <i class="fa-solid fa-building-columns text-emerald-400 w-4"></i>
                            <span>Bank Accounts & Escrow</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                    </a>
                    <a href="{{ route('admin.companies.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition border border-slate-700/60">
                        <span class="flex items-center space-x-2">
                            <i class="fa-solid fa-briefcase text-rose-400 w-4"></i>
                            <span>Developer Companies & Logos</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition border border-slate-700/60">
                        <span class="flex items-center space-x-2">
                            <i class="fa-solid fa-shield-halved text-indigo-400 w-4"></i>
                            <span>Roles & Permissions</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Bank Accounts -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center justify-between">
                    <span>Recent Bank Accounts</span>
                    <a href="{{ route('admin.bank-accounts.create') }}" class="text-emerald-600 hover:underline">+ Add</a>
                </h4>
                <div class="space-y-3">
                    @foreach($bankAccounts as $acc)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="font-semibold text-slate-800 flex items-center justify-between">
                                <span>{{ $acc->account_nick_name }}</span>
                                <span class="font-mono text-[10px] text-slate-500 font-bold">{{ $acc->account_code }}</span>
                            </div>
                            <div class="text-slate-500 mt-0.5">{{ $acc->bank_name }} - {{ $acc->account_number }}</div>
                            @if($acc->project)
                                <div class="mt-1.5 inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-medium">
                                    Linked: {{ $acc->project->name }}
                                </div>
                            @else
                                <div class="mt-1.5 inline-block px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-medium">
                                    General Account
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
