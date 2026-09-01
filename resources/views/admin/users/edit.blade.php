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
                <span class="text-violet-600">Edit User</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Modify User Information</h1>
            <p class="text-xs text-slate-500 mt-0.5">Update staff account particulars, roles, or reset login password.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Edit User Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-violet-600 to-indigo-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-user-pen text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">MODIFY USER DETAILS</h3>
                    <p class="text-[11px] text-violet-100">{{ $user->name }} ({{ $user->user_code ?? 'User-'.$user->id }})</p>
                </div>
            </div>
            <span class="text-xs font-mono bg-black/25 px-2.5 py-1 rounded text-white font-bold">{{ $user->user_code ?? 'User-'.$user->id }}</span>
        </div>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div>
                    <label for="company_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <select id="company_id" name="company_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('company_id', $user->company_id) == $comp->id ? 'selected' : '' }}>
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
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                </div>

                <!-- Designation -->
                <div>
                    <label for="designation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Designation <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="designation" name="designation" value="{{ old('designation', $user->designation) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                </div>

                <!-- Role (Dynamic) -->
                <div>
                    <label for="role_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Assigned Role <span class="text-rose-500">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id', $user->role_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mobile -->
                <div>
                    <label for="mobile" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                </div>

                <!-- Email ID -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Email ID <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition">
                </div>

                <!-- Password (Optional on Edit) -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        New Password <span class="text-slate-400 font-normal">(Leave blank to keep existing)</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="••••••••••••">
                </div>

                <!-- Re-Enter Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Confirm New Password
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                           placeholder="Repeat new password">
                </div>

                <!-- Assigned Projects -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Assigned Projects Permission (Multi-Select)
                    </label>
                    @php $assigned = $user->assigned_project_ids ?? []; @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        @foreach($projects as $proj)
                            <label class="flex items-center space-x-2.5 p-2 rounded-lg hover:bg-white transition cursor-pointer">
                                <input type="checkbox" name="assigned_project_ids[]" value="{{ $proj->id }}"
                                       {{ in_array($proj->id, $assigned) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-violet-600 border-slate-300 focus:ring-violet-500">
                                <span class="text-xs font-semibold text-slate-700">
                                    🏢 {{ $proj->name }} <span class="text-slate-400 font-mono">({{ $proj->project_code }})</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-violet-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Update User</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
