@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <span class="text-sky-600">Projects Master</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Real Estate Projects Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage residential & commercial property projects under developer companies.</p>
        </div>
        @if(auth()->user()->hasPermission('create_projects'))
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-amber-600/20 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Create New Project</span>
            </a>
        @endif
    </div>

    <!-- Projects Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs font-bold uppercase text-slate-600 tracking-wider">
                All Projects List ({{ $projects->count() }})
            </div>
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-shield text-amber-500 mr-1"></i> RERA & Land Details Record
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/70 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Project ID & Nick</th>
                        <th class="py-3.5 px-4">Project Name & Address</th>
                        <th class="py-3.5 px-4">Land Records (Daag/Patta)</th>
                        <th class="py-3.5 px-4">Mouza & Pogonah</th>
                        <th class="py-3.5 px-4">RERA Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $project)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Project Code & Nick -->
                            <td class="py-4 px-4 align-top">
                                <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200">
                                    {{ $project->project_code }}
                                </span>
                                <div class="mt-1 text-xs font-bold text-slate-700">
                                    Nick: <span class="text-amber-600 font-mono">{{ $project->nick_name }}</span>
                                </div>
                            </td>

                            <!-- Project Name & Address -->
                            <td class="py-4 px-4 align-top">
                                <div class="font-bold text-slate-800 text-sm">
                                    <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">
                                        {{ $project->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-500 mt-1 max-w-sm leading-relaxed">
                                    {{ $project->full_address }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    Company: <span class="font-medium text-slate-600">{{ $project->company->name ?? 'SSI' }}</span>
                                </div>
                            </td>

                            <!-- Land Records -->
                            <td class="py-4 px-4 align-top text-xs space-y-1">
                                <div>Daag No: <span class="font-bold text-slate-800">{{ $project->daag_no ?? 'N/A' }}</span></div>
                                <div>Patta No: <span class="font-bold text-slate-800">{{ $project->patta_no ?? 'N/A' }}</span></div>
                                <div>Holding No: <span class="font-bold text-slate-800">{{ $project->holding_no ?? 'N/A' }}</span></div>
                            </td>

                            <!-- Mouza & Pogonah -->
                            <td class="py-4 px-4 align-top text-xs space-y-1">
                                <div>Mouza: <span class="font-medium text-slate-700">{{ $project->mouza ?? 'N/A' }}</span></div>
                                <div>Pogonah: <span class="font-medium text-slate-700">{{ $project->pogonah ?? 'N/A' }}</span></div>
                            </td>

                            <!-- RERA Status -->
                            <td class="py-4 px-4 align-top text-xs">
                                @if($project->rera_category === 'registered')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-check-circle mr-1"></i> Registered
                                    </span>
                                    <div class="mt-1 font-mono text-[10px] text-slate-600">{{ $project->rera_reg_no }}</div>
                                @elseif($project->rera_category === 'exempted')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        Exempted
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        Unregistered
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 align-top text-right space-x-2">
                                <a href="{{ route('project.dashboard', $project->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-900 hover:bg-sky-600 text-white rounded-lg text-xs font-semibold transition" title="Open Project Workspace">
                                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Open
                                </a>
                                <a href="{{ route('admin.projects.edit', $project->id) }}" class="inline-flex items-center p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs transition" title="Edit Project">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this project? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition" title="Delete Project">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                No projects created yet. <a href="{{ route('admin.projects.create') }}" class="text-amber-600 font-semibold underline">Create your first project</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
