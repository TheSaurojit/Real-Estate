@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.projects.index') }}" class="hover:text-sky-600">Projects</a>
                <span>/</span>
                <span class="text-amber-600">New Project</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create Real Estate Project</h1>
            <p class="text-xs text-slate-500 mt-0.5">Define project title, land numbers, boundary mouza, and RERA compliance.</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Create Project Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" 
         x-data="{
            reraCat: '{{ old('rera_category', 'registered') }}',
            reraNo: '{{ old('rera_reg_no', '') }}',
            updateRera() {
                if (this.reraCat === 'unregistered') {
                    this.reraNo = 'Unregistered';
                } else if (this.reraCat === 'exempted') {
                    this.reraNo = 'Exempted';
                } else if (this.reraNo === 'Unregistered' || this.reraNo === 'Exempted') {
                    this.reraNo = '';
                }
            }
         }" x-init="updateRera()">
        
        <div class="bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-city text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">1.2.1. PROJECT CREATION</h3>
                    <p class="text-[11px] text-amber-100">Project Master Setup</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-amber-200 uppercase tracking-wider block">Auto-Generated Code</span>
                <span class="text-sm font-mono font-bold bg-black/25 px-2.5 py-0.5 rounded text-white">{{ $nextProjectCode }}</span>
            </div>
        </div>

        <form action="{{ route('admin.projects.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div>
                    <label for="company_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Name of the Company <span class="text-rose-500">*</span>
                    </label>
                    <select id="company_id" name="company_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition cursor-pointer">
                        <option value="" disabled {{ (old('company_id') || $companies->count() === 1) ? '' : 'selected' }}>-- Select Company --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ (old('company_id') == $comp->id || $companies->count() === 1) ? 'selected' : '' }}>
                                {{ $comp->name }} ({{ $comp->company_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Project Name -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Project Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. Ramkrishna Apartment or Imperial Grand">
                </div>

                <!-- Project Nick Name -->
                <div>
                    <label for="nick_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nick Name <span class="text-rose-500">*</span> <span class="text-[11px] font-normal text-slate-400">(UNIQUE & max 3-4 ALPHABETS)</span>
                    </label>
                    <input type="text" id="nick_name" name="nick_name" value="{{ old('nick_name') }}" required maxlength="6"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. RA or IG">
                </div>

                <!-- Daag No -->
                <div>
                    <label for="daag_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        DAG NO. of the Land
                    </label>
                    <input type="text" id="daag_no" name="daag_no" value="{{ old('daag_no') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. 156 or 287">
                </div>

                <!-- Patta No -->
                <div>
                    <label for="patta_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        PATTA NO. of the Land
                    </label>
                    <input type="text" id="patta_no" name="patta_no" value="{{ old('patta_no') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. 258 [2nd RS] or 385 [2nd RS]">
                </div>

                <!-- Holding No -->
                <div>
                    <label for="holding_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        HOLDING NO. of the Land
                    </label>
                    <input type="text" id="holding_no" name="holding_no" value="{{ old('holding_no') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. 129 or Unknown">
                </div>

                <!-- Mouza -->
                <div>
                    <label for="mouza" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mouza
                    </label>
                    <input type="text" id="mouza" name="mouza" value="{{ old('mouza') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. Ambicapur Part-X">
                </div>

                <!-- Pogonah -->
                <div>
                    <label for="pogonah" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Pogonah / Porgonah
                    </label>
                    <input type="text" id="pogonah" name="pogonah" value="{{ old('pogonah') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. Barakpar">
                </div>

                <!-- Full Project Address -->
                <div class="md:col-span-2">
                    <label for="full_address" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Full Address of the Property <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="full_address" name="full_address" rows="3" required
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                              placeholder="e.g. Krishna Charan Road (West), Rangirkhari, Silchar, Cachar, Assam, Pin - 788005">{{ old('full_address') }}</textarea>
                </div>

                <!-- RERA Registration Category -->
                <div>
                    <label for="rera_category" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        RERA Registration Category <span class="text-rose-500">*</span>
                    </label>
                    <select id="rera_category" name="rera_category" x-model="reraCat" @change="updateRera()" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                        <option value="unregistered">Unregistered</option>
                        <option value="exempted">Exempted</option>
                        <option value="registered">Registered</option>
                    </select>
                </div>

                <!-- RERA Reg No. -->
                <div>
                    <label for="rera_reg_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        RERA Registration No.
                    </label>
                    <input type="text" id="rera_reg_no" name="rera_reg_no" x-model="reraNo"
                           :readonly="reraCat !== 'registered'"
                           :class="reraCat !== 'registered' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-slate-50 text-slate-800'"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                           placeholder="e.g. RERA 1458 CA of 2025">
                    <p class="text-[11px] text-slate-400 mt-1" x-show="reraCat === 'registered'">
                        Required for RERA registered developments.
                    </p>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.projects.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Exit Page
                </a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-amber-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Project</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
