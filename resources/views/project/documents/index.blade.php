@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedBookingId: '{{ $selectedBooking?->id ?? ($bookings->first()?->id ?? '') }}',
    selectedReceiptTxnId: '{{ $receiptTxns->first()?->id ?? '' }}',
    selectedPaymentTxnId: '{{ $paymentTxns->first()?->id ?? '' }}',
    showReceiptModal: false,
    showPaymentModal: false,
    docUrl(type, query = '') {
        let base = this.selectedBookingId 
            ? ('{{ url('project') }}/{{ $project->id }}/bookings/' + this.selectedBookingId + '/documents/' + type)
            : ('{{ url('project') }}/{{ $project->id }}/documents/' + type);
        return query ? (base + (base.includes('?') ? '&' : '?') + query) : base;
    }
}">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-sky-600">Print Documents</span>
            </nav>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Print-Ready Document & Voucher Hub</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                    Section 2.4 Standard
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Official printable instruments, confirmation & allotment letters, tax invoices, possession certificates, and transaction ledgers.</p>
        </div>
    </div>

    @if($bookings->isEmpty())
    <!-- No Customer Alert Banner -->
    <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-950">No Customers / Bookings Registered in this Project Yet</h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        To print documents with customer particulars, property dimensions, and payment schedules, please add a customer booking first. Document printing options are currently disabled.
                    </p>
                </div>
            </div>
            <a href="{{ route('project.bookings.create', $project->id) }}"
               class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center space-x-2 shrink-0">
                <i class="fa-solid fa-user-plus"></i>
                <span>Create Customer Booking</span>
            </a>
        </div>
    </div>
    @endif

    <!-- Booking Selection Banner -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-1">
            <label for="booking_selector" class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <i class="fa-solid fa-house-user text-sky-600"></i>
                <span>Select Customer / Property Unit</span>
            </label>
            <p class="text-xs text-slate-500">All 11 official templates dynamically bind to the chosen booking's legal profile, financial matrix, and payment ledgers.</p>
        </div>

        <div class="w-full md:w-[480px]">
            @if($bookings->isNotEmpty())
                <select id="booking_selector" x-model="selectedBookingId"
                        @change="window.location.href = '{{ route('project.documents.index', $project->id) }}?booking_id=' + $event.target.value"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition cursor-pointer">
                    @foreach($bookings as $b)
                        <option value="{{ $b->id }}" {{ ($selectedBooking?->id == $b->id) ? 'selected' : '' }}>
                            🏢 Unit: {{ $b->unit_no }} ({{ $b->block_name }}) — {{ $b->customer_salutation }} {{ $b->customer_name }} [{{ $b->booking_code }}]
                        </option>
                    @endforeach
                </select>
            @else
                <div class="px-3.5 py-2.5 bg-slate-100 border border-dashed border-slate-300 rounded-xl text-xs font-semibold text-slate-500 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-user-slash text-slate-400"></i>
                        <span>No customer bookings registered</span>
                    </span>
                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-600">Disabled</span>
                </div>
            @endif
        </div>
    </div>

    @if($selectedBooking)
    <!-- Active Unit Meta Snapshot Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-4 text-xs">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-bold text-sky-400">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider block">Selected Allotment</span>
                <span class="text-sm font-bold text-white">{{ $selectedBooking->unit_no }} ({{ $selectedBooking->block_name }})</span>
                <span class="text-slate-300 ml-1.5">— {{ $selectedBooking->customer_salutation }} {{ $selectedBooking->customer_name }}</span>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <div>
                <span class="text-slate-400 text-[10px] block">Agreement Value</span>
                <span class="font-mono font-bold text-white">₹{{ number_format($selectedBooking->consideration_value, 2) }}</span>
            </div>
            <div>
                <span class="text-slate-400 text-[10px] block">Final Booking Value</span>
                <span class="font-mono font-bold text-emerald-400">₹{{ number_format($selectedBooking->final_booking_value ?: $selectedBooking->total_booking_value, 2) }}</span>
            </div>
            <div>
                <span class="text-slate-400 text-[10px] block">Status</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $selectedBooking->status === 'live' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                    {{ $selectedBooking->status }}
                </span>
            </div>
        </div>
    </div>
    @endif

    @php
        $hasCustomer = $bookings->isNotEmpty();
        $standardCardClass = $hasCustomer
            ? 'bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group'
            : 'bg-slate-50/70 rounded-2xl border border-dashed border-slate-300/80 p-5 opacity-50 grayscale cursor-not-allowed select-none flex flex-col justify-between';

        $receiptCardClass = $hasCustomer
            ? 'bg-white rounded-2xl border-2 border-emerald-300 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group bg-emerald-50/20'
            : 'bg-slate-50/70 rounded-2xl border border-dashed border-slate-300/80 p-5 opacity-50 grayscale cursor-not-allowed select-none flex flex-col justify-between';

        $paymentCardClass = $hasCustomer
            ? 'bg-white rounded-2xl border-2 border-rose-300 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group bg-rose-50/20'
            : 'bg-slate-50/70 rounded-2xl border border-dashed border-slate-300/80 p-5 opacity-50 grayscale cursor-not-allowed select-none flex flex-col justify-between';
    @endphp

    <!-- 11 Standard Statutory Print Documents Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <div class="w-2.5 h-2.5 rounded-full {{ $hasCustomer ? 'bg-sky-500' : 'bg-slate-400' }}"></div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Section 2.4 Official Printable Documents (1 to 11)</h3>
            </div>
            @if(!$hasCustomer)
                <span class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-[10px]"></i>
                    <span>Printing Disabled (Requires Customer)</span>
                </span>
            @else
                <span class="text-xs text-slate-400 font-semibold">11 Standard Templates</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- 1. Booking confirmation letter -->
            @php $doc1 = $documentTypes['booking_confirmation']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc1['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#1</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc1['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc1['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Customer Welcome Kit</span>
                    @if($hasCustomer)
                        <a :href="docUrl('booking_confirmation')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-sky-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 2. Property allotment Letter -->
            @php $doc2 = $documentTypes['allotment_letter']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc2['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#2</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc2['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc2['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Legal Allotment</span>
                    @if($hasCustomer)
                        <a :href="docUrl('allotment_letter')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-indigo-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 3. Demand Letter -->
            @php $doc3 = $documentTypes['demand_letter']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc3['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#3</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc3['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc3['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Milestone Payment Notice</span>
                    @if($hasCustomer)
                        <a :href="docUrl('demand_letter')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 4. Booking customization & Final bill -->
            @php $doc4 = $documentTypes['final_bill']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc4['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#4</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc4['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc4['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Job Sheet & Master Bill</span>
                    @if($hasCustomer)
                        <a :href="docUrl('final_bill')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 5. No objection/no due certificate -->
            @php $doc5 = $documentTypes['noc_certificate']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc5['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#5</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc5['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc5['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">NOC Clearance Certificate</span>
                    @if($hasCustomer)
                        <a :href="docUrl('noc_certificate')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-teal-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 6. Possession cum Key handover certificate -->
            @php $doc6 = $documentTypes['possession_certificate']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc6['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#6</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc6['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc6['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Handover & Key Delivery</span>
                    @if($hasCustomer)
                        <a :href="docUrl('possession_certificate')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-purple-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 7. Booking Summary -->
            @php $doc7 = $documentTypes['booking_summary']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc7['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#7</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc7['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc7['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">360° Financial Reconciliation</span>
                    @if($hasCustomer)
                        <a :href="docUrl('booking_summary')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-blue-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 8. Receipts (Interactive with 4 Sub-Options) -->
            @php $doc8 = $documentTypes['receipts']; @endphp
            <div class="{{ $receiptCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc8['icon'] }}"></i>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Sub-Options
                            </span>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#8</span>
                        </div>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc8['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc8['desc'] }}</p>

                    @if($hasCustomer)
                    <!-- Sub-option Selector Buttons -->
                    <div class="mt-3.5 space-y-2">
                        <div class="grid grid-cols-3 gap-1.5 text-[11px]">
                            <a :href="docUrl('receipts', 'mode=all_receipts')"
                               target="_blank"
                               class="text-center py-1.5 px-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">
                                All Receipts
                            </a>
                            <a :href="docUrl('receipts', 'mode=all_money_receipts')"
                               target="_blank"
                               class="text-center py-1.5 px-2 bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300 font-semibold rounded-lg transition"
                               title="Taxable Banking">
                                Money Receipts
                            </a>
                            <a :href="docUrl('receipts', 'mode=all_receipt_vouchers')"
                               target="_blank"
                               class="text-center py-1.5 px-2 bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300 font-semibold rounded-lg transition"
                               title="Non-Taxable Cash">
                                Receipt Vouchers
                            </a>
                        </div>

                        <!-- Single Receipt by Transaction ID -->
                        <div class="pt-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                                Receipt by Transaction ID
                            </label>
                            @if($receiptTxns->isNotEmpty())
                            <div class="flex items-center space-x-1.5">
                                <select x-model="selectedReceiptTxnId"
                                        class="flex-1 px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    @foreach($receiptTxns as $rtx)
                                        <option value="{{ $rtx->id }}">
                                            {{ $rtx->transaction_code }} ({{ $rtx->voucher_type === 'money_receipt' ? 'MR-Taxable' : 'RV-Cash' }}) — ₹{{ number_format($rtx->amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <a :href="docUrl('receipts', selectedReceiptTxnId ? ('mode=single&transaction_id=' + selectedReceiptTxnId) : 'mode=all_receipts')"
                                   target="_blank"
                                   class="px-3 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-xs font-semibold shrink-0 transition"
                                   title="Print this receipt">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                            @else
                            <div class="text-[11px] text-slate-400 italic bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                No receipt transactions recorded yet for this booking.
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="mt-3.5 py-3 px-3 bg-slate-100/80 rounded-xl text-xs text-slate-400 font-medium italic text-center border border-dashed border-slate-200 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-lock text-[11px] text-slate-400"></i>
                        <span>Printing options locked - add customer to enable</span>
                    </div>
                    @endif
                </div>

                @if(!$hasCustomer)
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Receipts & Vouchers</span>
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>Requires Customer</span>
                    </span>
                </div>
                @endif
            </div>

            <!-- 9. Payments (Interactive with 4 Sub-Options) -->
            @php $doc9 = $documentTypes['payments']; @endphp
            <div class="{{ $paymentCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc9['icon'] }}"></i>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-200">
                                Sub-Options
                            </span>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#9</span>
                        </div>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc9['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc9['desc'] }}</p>

                    @if($hasCustomer)
                    <!-- Sub-option Selector Buttons -->
                    <div class="mt-3.5 space-y-2">
                        <div class="grid grid-cols-3 gap-1.5 text-[11px]">
                            <a :href="docUrl('payments', 'mode=all_payments')"
                               target="_blank"
                               class="text-center py-1.5 px-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg shadow-sm transition">
                                All Payments
                            </a>
                            <a :href="docUrl('payments', 'mode=all_taxable')"
                               target="_blank"
                               class="text-center py-1.5 px-2 bg-white hover:bg-rose-50 text-rose-800 border border-rose-300 font-semibold rounded-lg transition"
                               title="All Taxable Payments">
                                All – Taxable
                            </a>
                            <a :href="docUrl('payments', 'mode=all_nontaxable')"
                               target="_blank"
                               class="text-center py-1.5 px-2 bg-white hover:bg-rose-50 text-rose-800 border border-rose-300 font-semibold rounded-lg transition"
                               title="All Non-Taxable Payments">
                                All – Non Taxable
                            </a>
                        </div>

                        <!-- Single Payment by Transaction ID -->
                        <div class="pt-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                                Payment by Transaction ID
                            </label>
                            @if($paymentTxns->isNotEmpty())
                            <div class="flex items-center space-x-1.5">
                                <select x-model="selectedPaymentTxnId"
                                        class="flex-1 px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-rose-500">
                                    @foreach($paymentTxns as $ptx)
                                        <option value="{{ $ptx->id }}">
                                            {{ $ptx->transaction_code }} ({{ $ptx->payment_category ?: ($ptx->is_taxable_transaction ? 'Taxable' : 'Non-Taxable') }}) — ₹{{ number_format($ptx->amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <a :href="docUrl('payments', selectedPaymentTxnId ? ('mode=single&transaction_id=' + selectedPaymentTxnId) : 'mode=all_payments')"
                                   target="_blank"
                                   class="px-3 py-1.5 bg-rose-800 hover:bg-rose-900 text-white rounded-lg text-xs font-semibold shrink-0 transition"
                                   title="Print this payment voucher">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                            @else
                            <div class="text-[11px] text-slate-400 italic bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                No payment outflows or refunds recorded yet for this booking.
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="mt-3.5 py-3 px-3 bg-slate-100/80 rounded-xl text-xs text-slate-400 font-medium italic text-center border border-dashed border-slate-200 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-lock text-[11px] text-slate-400"></i>
                        <span>Printing options locked - add customer to enable</span>
                    </div>
                    @endif
                </div>

                @if(!$hasCustomer)
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Payment Vouchers & Refunds</span>
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>Requires Customer</span>
                    </span>
                </div>
                @endif
            </div>

            <!-- 10. Proforma Invoice -->
            @php $doc10 = $documentTypes['proforma_invoice']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc10['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#10</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc10['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc10['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Commercial Tax Invoice (SAC 995411)</span>
                    @if($hasCustomer)
                        <a :href="docUrl('proforma_invoice')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-cyan-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 11. Booking closing summary -->
            @php $doc11 = $documentTypes['booking_closing_summary']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc11['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">#11</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc11['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc11['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Triple-Sign Final File Closing</span>
                    @if($hasCustomer)
                        <a :href="docUrl('booking_closing_summary')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Supplementary: 12. Booking Cancellation & Settlement Deed -->
            @if(isset($documentTypes['cancellation_deed']))
            @php $doc12 = $documentTypes['cancellation_deed']; @endphp
            <div class="{{ $standardCardClass }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $doc12['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">Deed</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mt-3">{{ $doc12['title'] }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $doc12['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Cancellation Settlement</span>
                    @if($hasCustomer)
                        <a :href="docUrl('cancellation_deed')"
                           target="_blank"
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-print text-[10px]"></i>
                            <span>Preview &amp; Print</span>
                        </a>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold cursor-not-allowed select-none" title="Printing disabled: requires customer booking">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Requires Customer</span>
                        </span>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
