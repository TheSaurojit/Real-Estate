@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    ratePerSqft: {{ old('rate_per_sqft', 0) }},
    superBuiltup: {{ old('super_built_up_area', 0) }},
    builtup: {{ old('built_up_area', 0) }},
    parkingCost: {{ old('parking_cost', 0) }},
    transformerCost: {{ old('transformer_cost', 0) }},
    amenitiesCost: {{ old('amenities_cost', 0) }},
    discountApplied: {{ old('discount_applied', 0) }},
    taxRate: {{ old('tax_rate', 5.00) }},
    taxName: '{{ old('tax_name', 'GST - 5.00%') }}',

    get unitCost() {
        return Math.round((parseFloat(this.ratePerSqft || 0) * parseFloat(this.superBuiltup || 0)) * 100) / 100;
    },
    get grossTotal() {
        return Math.round((
            this.unitCost +
            parseFloat(this.parkingCost || 0) +
            parseFloat(this.transformerCost || 0) +
            parseFloat(this.amenitiesCost || 0)
        ) * 100) / 100;
    },
    get considerationValue() {
        const val = this.grossTotal - parseFloat(this.discountApplied || 0);
        return Math.max(0, Math.round(val * 100) / 100);
    },
    get taxAmount() {
        return Math.round(((this.considerationValue * parseFloat(this.taxRate || 0)) / 100) * 100) / 100;
    },
    get totalBookingValue() {
        return Math.round((this.considerationValue + this.taxAmount) * 100) / 100;
    },
    formatNumber(num) {
        return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);
    },
    setTaxPreset(name, rate) {
        this.taxName = name;
        this.taxRate = rate;
    }
}">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <a href="{{ route('project.bookings.index', $project->id) }}" class="hover:text-sky-600">Bookings</a>
                <span>/</span>
                <span class="text-sky-600">New Booking</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">New Property Booking & Allotment</h1>
            <p class="text-xs text-slate-500 mt-0.5">Enter property configuration on the fly with live pricing, consideration, and GST computation.</p>
        </div>
        <a href="{{ route('project.bookings.index', $project->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Booking Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Form Header Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-sky-500/20 border border-sky-400/30 flex items-center justify-center">
                    <i class="fa-solid fa-building text-sky-400"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white">PROPERTY BOOKING: {{ $project->name }}</h3>
                    <p class="text-[11px] text-sky-300">
                        Daag: {{ $project->daag_no ?? '-' }} | Patta: {{ $project->patta_no ?? '-' }} | Mouza: {{ $project->mouza ?? '-' }}
                    </p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] text-sky-300 uppercase tracking-wider block font-semibold">Auto Booking Code</span>
                <span class="text-sm font-mono font-bold bg-black/40 px-2.5 py-0.5 rounded border border-sky-600/40 text-white">{{ $nextBookingCode }}</span>
            </div>
        </div>

        <form action="{{ route('project.bookings.store', $project->id) }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf

            <!-- SECTION 1: PROPERTY SPECIFICATIONS -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <div class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-xs font-bold">1</div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Property & Allotment Specifications</h2>
                    <span class="text-xs text-slate-400">(Configured dynamically)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- Booking Date -->
                    <div>
                        <label for="booking_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Booking Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="booking_date" name="booking_date" value="{{ old('booking_date', date('Y-m-d')) }}" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Flat / Unit No -->
                    <div>
                        <label for="unit_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Unit / Flat No <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="unit_no" name="unit_no" value="{{ old('unit_no') }}" required
                               placeholder="e.g. Flat-3A or Shop-02"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Block Name -->
                    <div>
                        <label for="block_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Block / Wing Name
                        </label>
                        <input type="text" id="block_name" name="block_name" value="{{ old('block_name') }}"
                               placeholder="e.g. Block A or Tower 1"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Floor No -->
                    <div>
                        <label for="floor_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Floor Number
                        </label>
                        <input type="text" id="floor_no" name="floor_no" value="{{ old('floor_no') }}"
                               placeholder="e.g. 3rd Floor"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Built-up Area -->
                    <div>
                        <label for="built_up_area" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Built-up Area (Sq.Ft.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" id="built_up_area" name="built_up_area" x-model.number="builtup" required
                               placeholder="e.g. 950.00"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Super Built-up Area -->
                    <div>
                        <label for="super_built_up_area" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Super Built-up (Sq.Ft.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" id="super_built_up_area" name="super_built_up_area" x-model.number="superBuiltup" required
                               placeholder="e.g. 1200.00"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold font-mono text-indigo-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Property Type -->
                    <div>
                        <label for="property_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Property Type <span class="text-rose-500">*</span>
                        </label>
                        <select id="property_type" name="property_type" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                            <option value="Residential 2BHK Flat" {{ old('property_type') === 'Residential 2BHK Flat' ? 'selected' : '' }}>Residential 2BHK Flat</option>
                            <option value="Residential 3BHK Flat" {{ old('property_type') === 'Residential 3BHK Flat' ? 'selected' : '' }}>Residential 3BHK Flat</option>
                            <option value="Residential 1BHK Flat" {{ old('property_type') === 'Residential 1BHK Flat' ? 'selected' : '' }}>Residential 1BHK Flat</option>
                            <option value="Penthouse / Duplex" {{ old('property_type') === 'Penthouse / Duplex' ? 'selected' : '' }}>Penthouse / Duplex</option>
                            <option value="Commercial Shop / Showroom" {{ old('property_type') === 'Commercial Shop / Showroom' ? 'selected' : '' }}>Commercial Shop / Showroom</option>
                            <option value="Office Space" {{ old('property_type') === 'Office Space' ? 'selected' : '' }}>Office Space</option>
                        </select>
                    </div>

                    <!-- Parking Type -->
                    <div>
                        <label for="parking_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Parking Option <span class="text-rose-500">*</span>
                        </label>
                        <select id="parking_type" name="parking_type" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                            <option value="no_parking" {{ old('parking_type') === 'no_parking' ? 'selected' : '' }}>No Parking</option>
                            <option value="covered_garage" {{ old('parking_type') === 'covered_garage' ? 'selected' : '' }}>Covered Garage</option>
                            <option value="open_dedicated_bay" {{ old('parking_type') === 'open_dedicated_bay' ? 'selected' : '' }}>Open Dedicated Bay</option>
                            <option value="shared_parking" {{ old('parking_type') === 'shared_parking' ? 'selected' : '' }}>Shared Parking</option>
                            <option value="open_dedicated_parking" {{ old('parking_type') === 'open_dedicated_parking' ? 'selected' : '' }}>Open Dedicated Parking</option>
                        </select>
                    </div>

                    <!-- Parking Bay No -->
                    <div>
                        <label for="parking_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Parking Bay / Slot No
                        </label>
                        <input type="text" id="parking_no" name="parking_no" value="{{ old('parking_no') }}"
                               placeholder="e.g. Bay-G05"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Reference Source -->
                    <div>
                        <label for="reference_source" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Reference / Broker Source
                        </label>
                        <input type="text" id="reference_source" name="reference_source" value="{{ old('reference_source') }}"
                               placeholder="e.g. Direct / Broker Name"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    </div>

                    <!-- Landowner Allotment Checkbox -->
                    <div class="sm:col-span-2 flex items-center pt-4">
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="is_landowner_allocation" value="1" {{ old('is_landowner_allocation') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-amber-600 border-slate-300 focus:ring-amber-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800">Landowner Allocation Allotment</span>
                                <p class="text-[11px] text-slate-400">Check if this unit belongs to Landowner share agreement</p>
                            </div>
                        </label>
                    </div>

                </div>
            </div>

            <!-- SECTION 2: CUSTOMER INFORMATION -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">2</div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Customer & Purchaser Profile</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- Salutation -->
                    <div>
                        <label for="customer_salutation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Salutation <span class="text-rose-500">*</span>
                        </label>
                        <select id="customer_salutation" name="customer_salutation" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            <option value="Mr." {{ old('customer_salutation') === 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs." {{ old('customer_salutation') === 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Ms." {{ old('customer_salutation') === 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Dr." {{ old('customer_salutation') === 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="M/s" {{ old('customer_salutation') === 'M/s' ? 'selected' : '' }}>M/s (Company)</option>
                        </select>
                    </div>

                    <!-- Customer Full Name -->
                    <div class="sm:col-span-2">
                        <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Full Customer Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required
                               placeholder="e.g. Subhasish Das"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Guardian Relation -->
                    <div>
                        <label for="guardian_relation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Relation <span class="text-rose-500">*</span>
                        </label>
                        <select id="guardian_relation" name="guardian_relation" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            <option value="son_of" {{ old('guardian_relation') === 'son_of' ? 'selected' : '' }}>Son of (S/O)</option>
                            <option value="daughter_of" {{ old('guardian_relation') === 'daughter_of' ? 'selected' : '' }}>Daughter of (D/O)</option>
                            <option value="wife_of" {{ old('guardian_relation') === 'wife_of' ? 'selected' : '' }}>Wife of (W/O)</option>
                            <option value="care_of" {{ old('guardian_relation') === 'care_of' ? 'selected' : '' }}>Care of (C/O)</option>
                        </select>
                    </div>

                    <!-- Guardian Name -->
                    <div class="sm:col-span-2">
                        <label for="guardian_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Father / Husband / Guardian Name
                        </label>
                        <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}"
                               placeholder="e.g. Late Subodh Das"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Primary Mobile -->
                    <div>
                        <label for="mobile_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Primary Mobile <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}" required
                               placeholder="e.g. +91-9876543210"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Alternate Mobile -->
                    <div>
                        <label for="alt_mobile_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Alternate Mobile / WhatsApp
                        </label>
                        <input type="text" id="alt_mobile_no" name="alt_mobile_no" value="{{ old('alt_mobile_no') }}"
                               placeholder="e.g. +91-9876543211"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Email ID -->
                    <div class="sm:col-span-2">
                        <label for="email_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Email Address
                        </label>
                        <input type="email" id="email_id" name="email_id" value="{{ old('email_id') }}"
                               placeholder="e.g. customer@example.com"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Customer PAN -->
                    <div>
                        <label for="pan_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Customer PAN Card
                        </label>
                        <input type="text" id="pan_number" name="pan_number" value="{{ old('pan_number') }}"
                               placeholder="e.g. ABCDE1234F"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Customer GSTIN -->
                    <div>
                        <label for="gstin" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            GSTIN (if commercial)
                        </label>
                        <input type="text" id="gstin" name="gstin" value="{{ old('gstin') }}"
                               placeholder="e.g. 18ABCDE1234F1Z5"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Photo ID Type -->
                    <div>
                        <label for="photo_id_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Photo ID Verification <span class="text-rose-500">*</span>
                        </label>
                        <select id="photo_id_type" name="photo_id_type" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                            <option value="aadhaar" {{ old('photo_id_type') === 'aadhaar' ? 'selected' : '' }}>Aadhaar Card</option>
                            <option value="pan" {{ old('photo_id_type') === 'pan' ? 'selected' : '' }}>PAN Card</option>
                            <option value="voter_id" {{ old('photo_id_type') === 'voter_id' ? 'selected' : '' }}>Voter ID</option>
                            <option value="passport" {{ old('photo_id_type') === 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="driving_license" {{ old('photo_id_type') === 'driving_license' ? 'selected' : '' }}>Driving License</option>
                        </select>
                    </div>

                    <!-- Photo ID Number -->
                    <div>
                        <label for="photo_id_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Photo ID Card Number
                        </label>
                        <input type="text" id="photo_id_no" name="photo_id_no" value="{{ old('photo_id_no') }}"
                               placeholder="e.g. 1234-5678-9012"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Permanent Postal Address -->
                    <div class="sm:col-span-4">
                        <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Permanent Residential Address
                        </label>
                        <textarea id="address" name="address" rows="2"
                                  placeholder="e.g. Flat 204, Riverview Apts, Silchar, Assam - 788001"
                                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">{{ old('address') }}</textarea>
                    </div>

                </div>
            </div>

            <!-- SECTION 3: LIVE PRICING & CONSIDERATION CALCULATOR -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3</div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Pricing & Consideration Calculator</h2>
                    </div>
                    <span class="text-xs text-indigo-600 font-semibold"><i class="fa-solid fa-calculator mr-1"></i> Real-time Evaluation</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left 2 Cols: Input Parameters -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        
                        <!-- Rate Per Sq.Ft. -->
                        <div>
                            <label for="rate_per_sqft" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Rate Per Sq.Ft. (₹) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" id="rate_per_sqft" name="rate_per_sqft" x-model.number="ratePerSqft" required
                                   placeholder="e.g. 3200.00"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>

                        <!-- Unit Cost (Readonly Preview) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                Base Unit Cost (₹)
                            </label>
                            <div class="px-3.5 py-2.5 bg-slate-200/80 rounded-xl text-sm font-mono font-bold text-slate-700 border border-slate-300">
                                ₹<span x-text="formatNumber(unitCost)">0.00</span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Rate × Super Built-up Area</span>
                        </div>

                        <!-- Parking Cost -->
                        <div>
                            <label for="parking_cost" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Parking Charges (₹)
                            </label>
                            <input type="number" step="0.01" id="parking_cost" name="parking_cost" x-model.number="parkingCost"
                                   placeholder="0.00"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>

                        <!-- Transformer Cost -->
                        <div>
                            <label for="transformer_cost" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Transformer & Electrical (₹)
                            </label>
                            <input type="number" step="0.01" id="transformer_cost" name="transformer_cost" x-model.number="transformerCost"
                                   placeholder="0.00"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>

                        <!-- Amenities Cost -->
                        <div>
                            <label for="amenities_cost" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Club / Amenities (₹)
                            </label>
                            <input type="number" step="0.01" id="amenities_cost" name="amenities_cost" x-model.number="amenitiesCost"
                                   placeholder="0.00"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>

                        <!-- Discount Applied -->
                        <div>
                            <label for="discount_applied" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Special Discount (₹)
                            </label>
                            <input type="number" step="0.01" id="discount_applied" name="discount_applied" x-model.number="discountApplied"
                                   placeholder="0.00"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-rose-600 font-bold focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>

                        <!-- Tax Category -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Applicable GST Scheme <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="setTaxPreset('GST - 5.00%', 5.00)"
                                        :class="taxRate == 5 ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100'"
                                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs transition">
                                    GST 5.00% (Standard)
                                </button>
                                <button type="button" @click="setTaxPreset('GST - 1.00%', 1.00)"
                                        :class="taxRate == 1 ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100'"
                                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs transition">
                                    GST 1.00% (Affordable)
                                </button>
                                <button type="button" @click="setTaxPreset('Exempted / Zero Tax', 0.00)"
                                        :class="taxRate == 0 ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100'"
                                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs transition">
                                    0.00% (Exempted)
                                </button>
                            </div>
                            <input type="hidden" name="tax_name" :value="taxName">
                            <input type="hidden" name="tax_rate" :value="taxRate">
                        </div>

                    </div>

                    <!-- Right 1 Col: Live Financial Summary Breakdown Card -->
                    <div class="bg-gradient-to-br from-slate-900 to-sky-950 text-white p-5 rounded-2xl shadow-lg border border-slate-800 space-y-4 flex flex-col justify-between">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-sky-400 mb-3 flex items-center justify-between">
                                <span>Financial Summary</span>
                                <span class="text-[10px] bg-sky-900/80 px-2 py-0.5 rounded text-sky-200 font-mono" x-text="taxName">GST - 5%</span>
                            </div>

                            <div class="space-y-2.5 text-xs">
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>Unit Cost:</span>
                                    <span class="font-mono font-semibold" x-text="'₹' + formatNumber(unitCost)">₹0.00</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>Add-on Costs (Prk/Trans/Amen):</span>
                                    <span class="font-mono font-semibold" x-text="'₹' + formatNumber(parseFloat(parkingCost || 0) + parseFloat(transformerCost || 0) + parseFloat(amenitiesCost || 0))">₹0.00</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>Discount:</span>
                                    <span class="font-mono font-semibold text-rose-300" x-text="'-₹' + formatNumber(discountApplied)">-₹0.00</span>
                                </div>
                                <div class="pt-2 border-t border-slate-700/80 flex items-center justify-between font-bold text-sky-300">
                                    <span>Consideration Value:</span>
                                    <span class="font-mono text-sm" x-text="'₹' + formatNumber(considerationValue)">₹0.00</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>GST Tax (<span x-text="taxRate + '%'">5%</span>):</span>
                                    <span class="font-mono font-semibold text-emerald-400" x-text="'+₹' + formatNumber(taxAmount)">+₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total Highlight -->
                        <div class="pt-3 border-t border-slate-700/80">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">Total Booking Value</span>
                            <div class="text-2xl font-black font-mono text-white tracking-tight" x-text="'₹' + formatNumber(totalBookingValue)">
                                ₹0.00
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Subject to future Job Sheet customization rollups</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SUBMIT BUTTONS -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('project.bookings.index', $project->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-sky-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirm & Create Booking</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
