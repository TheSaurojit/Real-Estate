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
                <span class="text-amber-600">Edit Project</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Modify Project Information</h1>
            <p class="text-xs text-slate-500 mt-0.5">Edit project particulars. Changes will reflect across all linked transactions and reports.</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Edit Project Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden"
         x-data="{
            reraCat: '{{ old('rera_category', $project->rera_category) }}',
            reraNo: '{{ old('rera_reg_no', $project->rera_reg_no) }}',
            updateRera() {
                if (this.reraCat === 'unregistered') {
                    this.reraNo = 'Unregistered';
                } else if (this.reraCat === 'exempted') {
                    this.reraNo = 'Exempted';
                } else if (this.reraNo === 'Unregistered' || this.reraNo === 'Exempted') {
                    this.reraNo = '';
                }
            }
         }">
        
        <div class="bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-pen-to-square text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">MODIFY PROJECT PARTICULARS</h3>
                    <p class="text-[11px] text-amber-100">{{ $project->name }} ({{ $project->project_code }})</p>
                </div>
            </div>
            <span class="text-xs font-mono bg-black/25 px-2.5 py-1 rounded text-white font-bold">{{ $project->project_code }}</span>
        </div>

        <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div>
                    <label for="company_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Name of the Company <span class="text-rose-500">*</span>
                    </label>
                    <select id="company_id" name="company_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('company_id', $project->company_id) == $comp->id ? 'selected' : '' }}>
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
                    <input type="text" id="name" name="name" value="{{ old('name', $project->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Project Nick Name -->
                <div>
                    <label for="nick_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nick Name <span class="text-rose-500">*</span> <span class="text-[11px] font-normal text-slate-400">(UNIQUE & max 3-4 ALPHABETS)</span>
                    </label>
                    <input type="text" id="nick_name" name="nick_name" value="{{ old('nick_name', $project->nick_name) }}" required maxlength="6"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Daag No -->
                <div>
                    <label for="daag_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        DAG NO. of the Land
                    </label>
                    <input type="text" id="daag_no" name="daag_no" value="{{ old('daag_no', $project->daag_no) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Patta No -->
                <div>
                    <label for="patta_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        PATTA NO. of the Land
                    </label>
                    <input type="text" id="patta_no" name="patta_no" value="{{ old('patta_no', $project->patta_no) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Holding No -->
                <div>
                    <label for="holding_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        HOLDING NO. of the Land
                    </label>
                    <input type="text" id="holding_no" name="holding_no" value="{{ old('holding_no', $project->holding_no) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Mouza -->
                <div>
                    <label for="mouza" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mouza
                    </label>
                    <input type="text" id="mouza" name="mouza" value="{{ old('mouza', $project->mouza) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Pogonah -->
                <div>
                    <label for="pogonah" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Pogonah / Porgonah
                    </label>
                    <input type="text" id="pogonah" name="pogonah" value="{{ old('pogonah', $project->pogonah) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

                <!-- Full Project Address -->
                <div class="md:col-span-2">
                    <label for="full_address" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Full Address of the Property <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="full_address" name="full_address" rows="3" required
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">{{ old('full_address', $project->full_address) }}</textarea>
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
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.projects.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-amber-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Update Project</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
