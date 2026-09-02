@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedBookingId: '{{ $selectedBooking?->id ?? ($bookings->first()?->id ?? '') }}'
}">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center space-x-2">
                <a href="{{ route('project.dashboard', $project->id) }}" class="hover:text-sky-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-sky-600">Print Documents</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Print-Ready Document & Voucher Hub</h1>
            <p class="text-xs text-slate-500 mt-0.5">Generate and print all 12 statutory letters, tax invoices, bank NOCs, job sheets, and account statements.</p>
        </div>
    </div>

    <!-- Booking Selection Banner -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <label for="booking_selector" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                Select Customer / Property Unit for Document Generation
            </label>
            <p class="text-xs text-slate-500">All 12 templates will dynamically bind to this booking's land specs, pricing, and receipts.</p>
        </div>

        <div class="w-full sm:w-96">
            <select id="booking_selector" x-model="selectedBookingId"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition cursor-pointer">
                @foreach($bookings as $b)
                    <option value="{{ $b->id }}">
                        🏢 Unit: {{ $b->unit_no }} - {{ $b->customer_salutation }} {{ $b->customer_name }} ({{ $b->booking_code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- 12 Document Templates Grid Grouped by Category -->
    <div class="space-y-6">
        
        <!-- Category 1: Allotment & Confirmation Letters -->
        <div>
            <div class="flex items-center space-x-2 mb-3">
                <div class="w-2.5 h-2.5 rounded-full bg-sky-500"></div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Allotment & Confirmation Letters</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(['booking_confirmation', 'allotment_letter'] as $key)
                    @php $item = $documentTypes[$key]; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div class="flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $item['title'] }}</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400">Template #{{ $loop->iteration }}</span>
                            <a :href="'{{ url('project') }}/{{ $project->id }}/bookings/' + selectedBookingId + '/documents/{{ $key }}'"
                               target="_blank"
                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-sky-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                <i class="fa-solid fa-print text-[10px]"></i>
                                <span>Preview & Print</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Category 2: Billing & Statements -->
        <div>
            <div class="flex items-center space-x-2 mb-3">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Billing, Dues & Statements</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['demand_letter', 'final_bill', 'customer_account_statement'] as $key)
                    @php $item = $documentTypes[$key]; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div class="flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $item['title'] }}</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400">Template #{{ $loop->iteration + 2 }}</span>
                            <a :href="'{{ url('project') }}/{{ $project->id }}/bookings/' + selectedBookingId + '/documents/{{ $key }}'"
                               target="_blank"
                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                <i class="fa-solid fa-print text-[10px]"></i>
                                <span>Preview & Print</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Category 3: Vouchers & Receipts -->
        <div>
            <div class="flex items-center space-x-2 mb-3">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Vouchers & Receipts</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['money_receipt', 'cash_receipt_voucher', 'payment_voucher'] as $key)
                    @php $item = $documentTypes[$key]; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div class="flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $item['title'] }}</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400">Template #{{ $loop->iteration + 5 }}</span>
                            <a :href="'{{ url('project') }}/{{ $project->id }}/bookings/' + selectedBookingId + '/documents/{{ $key }}'"
                               target="_blank"
                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                <i class="fa-solid fa-print text-[10px]"></i>
                                <span>Preview & Print</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Category 4: Legal, Bank NOC & Technical -->
        <div>
            <div class="flex items-center space-x-2 mb-3">
                <div class="w-2.5 h-2.5 rounded-full bg-purple-500"></div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Legal, Bank NOC & Technical</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(['bank_noc', 'possession_letter', 'customization_jobsheet', 'cancellation_deed'] as $key)
                    @php $item = $documentTypes[$key]; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div class="flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $item['title'] }}</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400">Template #{{ $loop->iteration + 8 }}</span>
                            <a :href="'{{ url('project') }}/{{ $project->id }}/bookings/' + selectedBookingId + '/documents/{{ $key }}'"
                               target="_blank"
                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-900 hover:bg-purple-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                <i class="fa-solid fa-print text-[10px]"></i>
                                <span>Preview & Print</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
