@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <span class="text-violet-600">Staff Management</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Staff & Operator Accounts</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage employee operator credentials, designation roles, and assigned project permissions.</p>
        </div>
        @if(auth()->user()->hasPermission('create_users'))
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-violet-600/20 transition">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add New Staff User</span>
            </a>
        @endif
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs font-bold uppercase text-slate-600 tracking-wider">
                Active Staff & Admins ({{ $users->count() }})
            </div>
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-user-shield text-violet-500 mr-1"></i> Auto-Incremented Staff IDs & Credentials
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">User ID & Name</th>
                        <th class="py-3.5 px-4">Designation & Role</th>
                        <th class="py-3.5 px-4">Contact (Mobile & Email)</th>
                        <th class="py-3.5 px-4">Assigned Projects</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $usr)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- User ID & Name -->
                            <td class="py-4 px-4 align-top">
                                <span class="px-2.5 py-1 rounded-md bg-violet-50 text-violet-800 font-mono font-bold text-xs border border-violet-200">
                                    {{ $usr->user_code ?? ('User-' . $usr->id) }}
                                </span>
                                <div class="mt-1 font-bold text-slate-800 text-sm">
                                    {{ $usr->name }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    Company: <span class="text-slate-600 font-medium">{{ $usr->company->name ?? 'SSI' }}</span>
                                </div>
                            </td>

                            <!-- Designation & Role -->
                            <td class="py-4 px-4 align-top text-xs">
                                <div class="font-bold text-slate-800">{{ $usr->designation ?? 'Staff' }}</div>
                                <div class="mt-1">
                                    @if($usr->role === 'super_admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            Super Admin
                                        </span>
                                    @elseif($usr->role === 'admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-300">
                                            Admin
                                        </span>
                                    @elseif($usr->role === 'accountant')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            Accountant
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                            {{ ucfirst(str_replace('_', ' ', $usr->role)) }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-4 px-4 align-top text-xs space-y-0.5">
                                <div class="font-semibold text-slate-800"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $usr->mobile ?? 'N/A' }}</div>
                                <div class="text-slate-500"><i class="fa-solid fa-envelope text-slate-400 mr-1"></i> {{ $usr->email }}</div>
                            </td>

                            <!-- Assigned Projects -->
                            <td class="py-4 px-4 align-top text-xs">
                                @if($usr->role === 'super_admin' || $usr->role === 'admin')
                                    <span class="text-slate-600 font-medium italic">All Company Projects</span>
                                @elseif(!empty($usr->assigned_project_ids))
                                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-800 text-[11px] font-bold">
                                        {{ count($usr->assigned_project_ids) }} Assigned
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">None</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $usr->id) }}" class="inline-flex items-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs transition" title="Edit User">
                                    <i class="fa-solid fa-user-pen"></i>
                                </a>
                                @if($usr->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $usr->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition" title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                No users created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
