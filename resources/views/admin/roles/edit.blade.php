@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.roles.index') }}" class="hover:text-sky-600">Roles</a>
                <span>/</span>
                <span class="text-indigo-600">Edit Role</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Modify Role & Permissions</h1>
            <p class="text-xs text-slate-500 mt-0.5">Update role permissions. Changes apply immediately to all assigned staff members.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Edit Role Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="{
        toggleModule(moduleId) {
            const checkboxes = document.querySelectorAll('.module-' + moduleId);
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            checkboxes.forEach(c => c.checked = !allChecked);
        },
        selectAll() {
            document.querySelectorAll('.perm-checkbox').forEach(c => c.checked = true);
        },
        deselectAll() {
            document.querySelectorAll('.perm-checkbox').forEach(c => c.checked = false);
        }
    }">
        
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-sliders text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">CONFIGURE ROLE: {{ $role->name }}</h3>
                    <p class="text-[11px] text-indigo-100">Slug: {{ $role->slug }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" @click="selectAll()" class="px-2.5 py-1 rounded bg-white/20 hover:bg-white/30 text-white text-xs font-semibold transition">
                    Select All
                </button>
                <button type="button" @click="deselectAll()" class="px-2.5 py-1 rounded bg-black/20 hover:bg-black/30 text-white text-xs font-semibold transition">
                    Clear All
                </button>
            </div>
        </div>

        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Role Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-200/80">
                
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Role Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Role Description / Purpose
                    </label>
                    <input type="text" id="description" name="description" value="{{ old('description', $role->description) }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>

            </div>

            <!-- Permission Modules Grid -->
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center space-x-2">
                        <i class="fa-solid fa-key text-indigo-600"></i>
                        <span>Modify Module Permissions</span>
                    </h3>
                    <span class="text-xs text-slate-400">Toggle permissions to adjust access</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($permissionModules as $moduleName => $perms)
                        @php $modId = Str::slug($moduleName, '_'); @endphp
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 hover:border-indigo-300 transition">
                            
                            <!-- Module Header -->
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                                <div class="font-bold text-slate-800 text-xs uppercase tracking-wide flex items-center space-x-1.5">
                                    <i class="fa-solid fa-layer-group text-indigo-500"></i>
                                    <span>{{ $moduleName }}</span>
                                </div>
                                <button type="button" @click="toggleModule('{{ $modId }}')" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                                    Toggle All
                                </button>
                            </div>

                            <!-- Permissions in this Module -->
                            <div class="space-y-2">
                                @foreach($perms as $perm)
                                    <label class="flex items-start space-x-2.5 p-1.5 rounded-lg hover:bg-slate-50 transition cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}
                                               class="perm-checkbox module-{{ $modId }} mt-0.5 w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                        <div class="text-xs">
                                            <div class="font-semibold text-slate-800">{{ $perm->name }}</div>
                                            <div class="text-[11px] text-slate-400 leading-tight">{{ $perm->description }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Update Role Permissions</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
