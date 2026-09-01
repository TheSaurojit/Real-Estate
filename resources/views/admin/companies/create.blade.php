@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-sky-600">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.companies.index') }}" class="hover:text-sky-600">Companies</a>
                <span>/</span>
                <span class="text-rose-600">New Company</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create Developer Company</h1>
            <p class="text-xs text-slate-500 mt-0.5">Register a new real estate legal entity/firm with dedicated PAN, GSTIN, and auto-numbering.</p>
        </div>
        <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-building text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">NEW DEVELOPER COMPANY</h3>
                    <p class="text-[11px] text-rose-100">Super Admin Corporate Setup</p>
                </div>
            </div>
            <span class="text-xs font-semibold bg-black/20 px-2.5 py-1 rounded-md">Multi-Entity Architecture</span>
        </div>

        <form action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Company Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company / Entity Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. S & S Builders & Developers Pvt Ltd">
                </div>

                <!-- Company Short Code -->
                <div>
                    <label for="company_code" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Code (Prefix) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="company_code" name="company_code" value="{{ old('company_code') }}" required maxlength="10"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. SSD or SSB">
                    <p class="text-[11px] text-slate-400 mt-1">Unique 2-6 character code used in project and voucher auto-numbering.</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. contact@ssdevelopers.com">
                </div>

                <!-- Primary Contact -->
                <div>
                    <label for="contact_primary" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Primary Contact / Mobile <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="contact_primary" name="contact_primary" value="{{ old('contact_primary') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. +91-9876543210">
                </div>

                <!-- Secondary Contact -->
                <div>
                    <label for="contact_secondary" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Secondary Contact
                    </label>
                    <input type="text" id="contact_secondary" name="contact_secondary" value="{{ old('contact_secondary') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. +91-9876543211">
                </div>

                <!-- PAN -->
                <div>
                    <label for="pan_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company PAN No.
                    </label>
                    <input type="text" id="pan_number" name="pan_number" value="{{ old('pan_number') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. AABCS1234F">
                </div>

                <!-- GSTIN -->
                <div>
                    <label for="gstin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company GSTIN
                    </label>
                    <input type="text" id="gstin" name="gstin" value="{{ old('gstin') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                           placeholder="e.g. 18AABCS1234F1Z5">
                </div>

                <!-- Full Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Full Registered Address <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="address" name="address" rows="3" required
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                              placeholder="e.g. Silchar Road, Karimganj, Assam, Pin - 788713">{{ old('address') }}</textarea>
                </div>

                <!-- Upload Logo -->
                <div class="md:col-span-2" x-data="{ photoName: null, photoPreview: null }">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Company Official Logo
                    </label>
                    
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            <div class="h-20 w-20 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-400" x-show="!photoPreview">
                                <i class="fa-solid fa-image text-2xl"></i>
                            </div>
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
                                <i class="fa-solid fa-upload mr-1.5"></i> Select Logo Image
                            </button>
                            <p class="text-[11px] text-slate-400 mt-1.5">PNG, JPG, WebP up to 2MB. Displayed on money receipts & official letters.</p>
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
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Company</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
