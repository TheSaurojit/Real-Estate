@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.users.index') }}" class="hover:text-sky-600">Users</a>
                <span>/</span>
                <span class="text-violet-600">New User</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create User Account</h1>
            <p class="text-xs text-slate-500 mt-0.5">Register operator login credentials and configure administrative roles.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Create User Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-violet-600 to-indigo-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-user-plus text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">1.1.2. USER INFORMATION</h3>
                    <p class="text-[11px] text-violet-100">User Master Setup</p>
                </div>
            </div>
        </div>

        @php
            $defaultCompanyId = (string)old('company_id', $companies->count() === 1 ? $companies->first()->id : '');
            $initialProjects = array_map('intval', (array)old('assigned_project_ids', []));
            $projectsJson = $projects->map(fn($p) => [
                'id' => $p->id,
                'company_id' => (string)$p->company_id,
                'name' => $p->name,
                'project_code' => $p->project_code,
            ]);
        @endphp

        <form action="{{ route('admin.users.store') }}" 
              method="POST" 
              class="p-6 sm:p-8 space-y-6"
              x-data="{
                  selectedCompany: '{{ $defaultCompanyId }}',
                  selectedProjects: {{ json_encode($initialProjects) }},
                  allProjects: {{ $projectsJson->toJson() }},
                  get companyProjects() {
                      return this.allProjects.filter(p => String(p.company_id) === String(this.selectedCompany));
                  },
                  onCompanyChange() {
                      const validIds = this.companyProjects.map(p => p.id);
                      this.selectedProjects = this.selectedProjects.filter(id => validIds.includes(Number(id)));
                  }
              }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div>
                    <label for="company_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <select id="company_id" 
                            name="company_id" 
                            required
                            x-model="selectedCompany"
                            @change="onCompanyChange()"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition cursor-pointer">
                        <option value="" disabled {{ old('company_id') ? '' : 'selected' }}>-- Select Company --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('company_id', $defaultCompanyId) == (string)$comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} ({{ $comp->company_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee Name -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Employee Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="e.g. Subhasish Das">
                </div>

                <!-- Designation -->
                <div>
                    <label for="designation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Designation <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="designation" name="designation" value="{{ old('designation') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="e.g. Manager, Accountant, Director">
                </div>

                <!-- Role (Dynamic) -->
                <div>
                    <label for="role_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Assigned Role <span class="text-rose-500">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id', $roles->firstWhere('slug', 'admin')?->id) == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->slug }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Role determines modular permissions across the ERP.</p>
                </div>

                <!-- Mobile -->
                <div>
                    <label for="mobile" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="e.g. +91-9382445935">
                </div>

                <!-- Email ID -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Email ID <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="e.g. s4subhasish@gmail.com">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="Minimum 6 characters">
                </div>

                <!-- Re-Enter Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Re-Enter Password <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="Repeat password">
                </div>

                <!-- Assigned Projects -->
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Assigned Projects Permission (Multi-Select) <span class="text-rose-500">*</span>
                        </label>
                        <div x-show="selectedCompany && companyProjects.length > 0">
                            <span x-show="selectedProjects.length === 0" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-700 flex items-center space-x-1">
                                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                <span>At least 1 project required</span>
                            </span>
                            <span x-show="selectedProjects.length > 0" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800" 
                                  x-text="selectedProjects.length + ' project' + (selectedProjects.length > 1 ? 's' : '') + ' selected'">
                            </span>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 min-h-[80px] flex items-center justify-center">
                        
                        <!-- No Company Selected -->
                        <div x-show="!selectedCompany" class="py-3 text-center text-xs text-slate-400">
                            <i class="fa-solid fa-arrow-up mr-1 text-slate-400"></i> Please select a company above to view and assign its projects.
                        </div>

                        <!-- Selected Company Has No Projects -->
                        <div x-show="selectedCompany && companyProjects.length === 0" class="py-3 text-center text-xs text-slate-500">
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                                <i class="fa-solid fa-circle-exclamation mr-1 text-rose-600"></i>
                                <strong>No active projects found for this company.</strong>
                                <p class="mt-1 text-rose-600 text-[11px]">Users must be assigned to at least one project. Please create a project under this company first.</p>
                            </div>
                        </div>

                        <!-- Projects Grid for Selected Company -->
                        <div x-show="selectedCompany && companyProjects.length > 0" class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="proj in companyProjects" :key="proj.id">
                                <label class="flex items-center space-x-2.5 p-2.5 rounded-lg bg-white hover:bg-slate-100/80 border border-slate-200/80 shadow-xs transition cursor-pointer">
                                    <input type="checkbox" 
                                           name="assigned_project_ids[]" 
                                           :value="proj.id"
                                           x-model.number="selectedProjects"
                                           class="w-4 h-4 rounded text-violet-600 border-slate-300 focus:ring-violet-500 cursor-pointer">
                                    <span class="text-xs font-semibold text-slate-700">
                                        🏢 <span x-text="proj.name"></span> 
                                        <span class="text-slate-400 font-mono" x-text="'(' + proj.project_code + ')'"></span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>

                    @error('assigned_project_ids')
                        <p class="text-xs text-rose-500 font-semibold mt-1.5 flex items-center space-x-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Exit Page
                </a>
                <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-violet-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Create User</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
