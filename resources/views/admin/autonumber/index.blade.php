@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <span>Settings</span>
                <span>/</span>
                <span class="text-sky-600">Auto-numbering</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Auto-Numbering Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure prefix formats and sequential starting counters for all ERP modules.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>

    <!-- Auto Numbering Rules Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-cyan-600 to-sky-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-hashtag text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">1.1.1. AUTO-NUMBERING RULES</h3>
                    <p class="text-[11px] text-cyan-100">Set reference prefixes and next auto-increment sequence</p>
                </div>
            </div>
            <span class="text-xs bg-black/20 px-2.5 py-1 rounded-md font-mono">{{ $company->name ?? 'Default' }}</span>
        </div>

        <form action="{{ route('admin.autonumber.update') }}" method="POST" class="p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="text-xs font-bold uppercase text-slate-500 bg-slate-100/80 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-1/4">Entity Type</th>
                            <th class="py-3 px-4 w-1/4">Reference (Prefix)</th>
                            <th class="py-3 px-4 w-1/6">Next Number</th>
                            <th class="py-3 px-4 w-1/12">Padding</th>
                            <th class="py-3 px-4 w-1/4 text-right">Auto-Generated Preview</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sequences as $index => $seq)
                            <tr class="hover:bg-slate-50/80 transition" 
                                x-data="{ 
                                    prefix: '{{ $seq->prefix }}', 
                                    nextNum: {{ $seq->next_number }}, 
                                    pad: {{ $seq->padding }},
                                    get formatted() {
                                        return this.prefix + String(this.nextNum).padStart(this.pad, '0');
                                    }
                                }">
                                <td class="py-3.5 px-4">
                                    <input type="hidden" name="sequences[{{ $index }}][id]" value="{{ $seq->id }}">
                                    <div class="font-bold text-slate-800 text-xs">
                                        {{ $seq->description ?? ucfirst(str_replace('_', ' ', $seq->entity_type)) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $seq->entity_type }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <input type="text" name="sequences[{{ $index }}][prefix]" x-model="prefix" required
                                           class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                </td>
                                <td class="py-3.5 px-4">
                                    <input type="number" name="sequences[{{ $index }}][next_number]" x-model.number="nextNum" min="1" required
                                           class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                </td>
                                <td class="py-3.5 px-4">
                                    <input type="number" name="sequences[{{ $index }}][padding]" x-model.number="pad" min="0" max="10" required
                                           class="w-16 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-center text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="inline-block px-3 py-1.5 rounded-lg bg-sky-50 text-sky-800 font-mono font-bold text-xs border border-sky-200 shadow-sm" x-text="formatted">
                                        {{ $seq->preview }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    <i class="fa-solid fa-circle-info mr-1 text-sky-500"></i>
                    Auto-numbering guarantees gapless incrementing when new records are saved.
                </p>
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-cyan-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Auto-Numbering Rules</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
