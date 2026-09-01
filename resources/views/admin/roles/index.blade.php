@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <span class="text-indigo-600">Roles & Permissions</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dynamic Roles & Permissions Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Create custom operational roles and dynamically grant granular permissions across ERP modules.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-indigo-600/20 transition">
            <i class="fa-solid fa-shield-plus"></i>
            <span>Create Custom Role</span>
        </a>
    </div>

    <!-- Roles Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs font-bold uppercase text-slate-600 tracking-wider">
                Configured System & Custom Roles ({{ $roles->count() }})
            </div>
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-lock text-indigo-500 mr-1"></i> Dynamic Permission Matrix
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Role Name & Identifier</th>
                        <th class="py-3.5 px-4">Description</th>
                        <th class="py-3.5 px-4">Assigned Permissions</th>
                        <th class="py-3.5 px-4">Assigned Staff</th>
                        <th class="py-3.5 px-4">Role Type</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($roles as $r)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- Role Name & Slug -->
                            <td class="py-4 px-4 align-top">
                                <div class="font-bold text-slate-800 text-sm flex items-center space-x-2">
                                    <span>{{ $r->name }}</span>
                                </div>
                                <div class="text-xs font-mono text-slate-400 mt-0.5">
                                    {{ $r->slug }}
                                </div>
                            </td>

                            <!-- Description -->
                            <td class="py-4 px-4 align-top text-xs text-slate-500 max-w-sm">
                                {{ $r->description ?? 'No description provided.' }}
                            </td>

                            <!-- Assigned Permissions -->
                            <td class="py-4 px-4 align-top text-xs">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                    {{ $r->permissions_count }} Permissions Active
                                </span>
                            </td>

                            <!-- Assigned Staff Users -->
                            <td class="py-4 px-4 align-top text-xs">
                                <span class="font-bold text-slate-700">{{ $r->users_count }} Staff User(s)</span>
                            </td>

                            <!-- Role Type -->
                            <td class="py-4 px-4 align-top text-xs">
                                @if($r->is_system)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">
                                        <i class="fa-solid fa-lock mr-1 text-[9px]"></i> System Default
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-gear mr-1 text-[9px]"></i> Custom Role
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-2">
                                <a href="{{ route('admin.roles.edit', $r->id) }}" class="inline-flex items-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs transition" title="Edit Role & Permissions">
                                    <i class="fa-solid fa-sliders"></i>
                                </a>
                                @if(!$r->is_system && $r->users_count === 0)
                                    <form action="{{ route('admin.roles.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition" title="Delete Role">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                No roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
