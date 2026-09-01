@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.companies.index') }}" class="hover:text-sky-600">Companies</a>
                <span>/</span>
                <span class="text-rose-600">Edit Company</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Modify Developer Company</h1>
            <p class="text-xs text-slate-500 mt-0.5">Used for official report headings, booking letters, money receipts, and invoices.</p>
        </div>
        <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Company Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-briefcase text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">COMPANY MASTER SETTINGS</h3>
                    <p class="text-[11px] text-rose-100">{{ $company->name }}</p>
                </div>
            </div>
            <span class="text-xs font-mono bg-black/20 px-2.5 py-1 rounded-md">CODE: {{ $company->company_code }}</span>
        </div>

        <form action="{{ route('admin.companies.update', $company->id) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $company->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                </div>

                <!-- Company Short Code -->
                <div>
                    <label for="company_code" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Code (Prefix) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="company_code" name="company_code" value="{{ old('company_code', $company->company_code) }}" required maxlength="10"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                    <p class="text-[11px] text-slate-400 mt-1">Used as prefix in Project, Booking, and Voucher auto-numbering.</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $company->email) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                </div>

                <!-- Primary Contact -->
                <div>
                    <label for="contact_primary" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Primary Contact / Mobile <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="contact_primary" name="contact_primary" value="{{ old('contact_primary', $company->contact_primary) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                </div>

                <!-- Secondary Contact -->
                <div>
                    <label for="contact_secondary" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Secondary Contact
                    </label>
                    <input type="text" id="contact_secondary" name="contact_secondary" value="{{ old('contact_secondary', $company->contact_secondary) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                </div>

                <!-- PAN -->
                <div>
                    <label for="pan_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company PAN No.
                    </label>
                    <input type="text" id="pan_number" name="pan_number" value="{{ old('pan_number', $company->pan_number) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                </div>

                <!-- GSTIN -->
                <div>
                    <label for="gstin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company GSTIN
                    </label>
                    <input type="text" id="gstin" name="gstin" value="{{ old('gstin', $company->gstin) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                </div>

                <!-- Full Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Full Registered Address <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="address" name="address" rows="3" required
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">{{ old('address', $company->address) }}</textarea>
                </div>

                <!-- Upload Logo -->
                <div class="md:col-span-2" x-data="{ photoName: null, photoPreview: null }">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Official Logo
                    </label>
                    
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            @if($company->logo_path)
                                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Logo" class="h-20 w-20 object-contain rounded-xl border border-slate-200 bg-slate-50 p-1" x-show="!photoPreview">
                            @else
                                <div class="h-20 w-20 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-400" x-show="!photoPreview">
                                    <i class="fa-solid fa-image text-2xl"></i>
                                </div>
                            @endif
                            <div class="h-20 w-20 rounded-xl border border-slate-200 bg-slate-50 p-1" x-show="photoPreview" style="display: none;">
                                <img :src="photoPreview" class="h-full w-full object-contain rounded-lg">
                            </div>
                        </div>

                        <div class="flex-1">
                            <input type="file" name="logo" id="logo" class="hidden" accept="image/*"
                                   x-ref="photo"
                                   @change="
                                       photoName = $refs.photo.files[0].name;
                                       const reader = new FileReader();
                                       reader.onload = (e) => {
                                           photoPreview = e.target.result;
                                       };
                                       reader.readAsDataURL($refs.photo.files[0]);
                                   ">
                            
                            <button type="button" @click="$refs.photo.click()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition">
                                <i class="fa-solid fa-upload mr-1.5"></i> Change Logo
                            </button>
                            <p class="text-[11px] text-slate-400 mt-1.5">PNG, JPG, WebP up to 2MB. Printed on official letters & money receipts.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Submit Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.companies.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-rose-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Update Company</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
