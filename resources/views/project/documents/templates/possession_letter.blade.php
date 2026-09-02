@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="text-center space-y-1 pb-3 border-b-2 border-slate-900">
        <h2 class="text-xl font-black uppercase tracking-wider text-slate-900">PHYSICAL POSSESSION & KEY HANDOVER CERTIFICATE</h2>
        <p class="text-xs text-slate-500 font-medium">Final Completion & Handover Protocol</p>
    </div>

    <p>
        This Possession Certificate is issued on this <strong>{{ date('d') }}th</strong> day of <strong>{{ date('F, Y') }}</strong> by <strong>{{ $company->name }}</strong> ("The Developer/Promoter") in favor of:
    </p>

    <!-- Purchaser / Unit Specs -->
    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-xs">
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Allottee / Purchaser:</span>
            <span class="col-span-2 font-bold text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Flat / Apartment No:</span>
            <span class="col-span-2 font-mono font-bold text-indigo-900">{{ $booking->unit_no }} (Block: {{ $booking->block_name ?? '-' }}, Floor: {{ $booking->floor_no ?? '-' }})</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Super Built-up Area:</span>
            <span class="col-span-2 font-mono font-bold text-slate-900">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Car Parking Space:</span>
            <span class="col-span-2 text-slate-800">{{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }} (Bay No: {{ $booking->parking_no ?? 'Allocated' }})</span>
        </div>
    </div>

    <!-- Handover Declaration -->
    <div class="text-xs text-slate-700 space-y-2 leading-relaxed">
        <h4 class="font-bold text-slate-900 uppercase">Declaration & Handover Protocol:</h4>
        <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
            <li>The developer hereby hands over vacant, physical, and peaceful possession of the aforesaid apartment along with original keys to the purchaser.</li>
            <li>The purchaser confirms that they have thoroughly inspected the demised premises, civil construction, electrical fittings, plumbing, sanity fixtures, and joinery, and found them in complete, sound, and satisfactory condition as per agreed specifications.</li>
            <li>The purchaser confirms having received full financial clearance and acknowledges that all utility meter charges, maintenance deposits, and municipal property taxes from this date forward shall be borne by the purchaser.</li>
        </ol>
    </div>

    <!-- Handover Checklist Box -->
    <div class="border border-slate-300 rounded-xl p-4 bg-slate-50 text-xs space-y-2">
        <div class="font-bold text-slate-800 uppercase">Handover Deliverables Acknowledged:</div>
        <div class="grid grid-cols-2 gap-2 text-slate-700">
            <div><i class="fa-solid fa-check text-emerald-600 mr-1.5"></i> Main Door Keys (3 Sets)</div>
            <div><i class="fa-solid fa-check text-emerald-600 mr-1.5"></i> Internal Bedroom & Balcony Keys</div>
            <div><i class="fa-solid fa-check text-emerald-600 mr-1.5"></i> Electrical Sub-Meter Connected</div>
            <div><i class="fa-solid fa-check text-emerald-600 mr-1.5"></i> Sanitary & Water Connection Live</div>
        </div>
    </div>

</div>
@endsection
