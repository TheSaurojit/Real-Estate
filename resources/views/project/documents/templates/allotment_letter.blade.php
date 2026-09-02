@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Subject -->
    <div class="text-center space-y-1 pb-3 border-b-2 border-slate-900">
        <h2 class="text-lg font-black uppercase tracking-wider text-slate-900">FORMAL PROPERTY ALLOTMENT LETTER</h2>
        <p class="text-xs text-slate-500 font-medium">Issued under Real Estate (Regulation and Development) Act Compliant Standards</p>
    </div>

    <!-- Allottee Details -->
    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-xs">
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Allottee Name:</span>
            <span class="col-span-2 font-black text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Father / Guardian:</span>
            <span class="col-span-2 text-slate-800">{{ $booking->guardian_name ?? 'N/A' }}</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Residential Address:</span>
            <span class="col-span-2 text-slate-800">{{ $booking->address ?? 'N/A' }}</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">PAN / Identification:</span>
            <span class="col-span-2 font-mono text-slate-800">{{ $booking->pan_number ?? $booking->photo_id_no ?? 'N/A' }}</span>
        </div>
    </div>

    <p>
        In accordance with your application and receipt of the requisite booking consideration, <strong>{{ $company->name }}</strong> hereby confirms the formal allotment of the apartment described in the Schedule below:
    </p>

    <!-- Schedule of Property Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <div class="bg-slate-100 px-4 py-2 font-bold uppercase text-xs text-slate-800 border-b border-slate-300">
            Schedule of Allotted Property ("The Demised Premises")
        </div>
        <table class="w-full text-xs">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-600 w-1/3">Project Name & Location</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900">{{ $project->name }}, {{ $project->full_address }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-600">Allotted Flat / Unit No.</td>
                    <td class="py-2.5 px-4 font-black text-indigo-900 font-mono text-sm">{{ $booking->unit_no }} ({{ $booking->property_type }})</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-600">Block / Floor Location</td>
                    <td class="py-2.5 px-4 text-slate-800">Block: {{ $booking->block_name ?? '-' }}, Floor: {{ $booking->floor_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-600">Super Built-up Area</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900 font-mono">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-600">Car Parking Space</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }} (Bay: {{ $booking->parking_no ?? 'Designated' }})</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-bold text-slate-600">Total Consideration Value</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900 font-mono">₹{{ number_format($booking->total_booking_value, 2) }} (Including GST)</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Statutory Clauses -->
    <div class="text-xs text-slate-700 space-y-2">
        <h4 class="font-bold text-slate-900 uppercase">Terms & Conditions of Allotment:</h4>
        <ol class="list-decimal list-inside space-y-1 text-slate-600">
            <li>The allottee agrees to make milestone stage payments as per the agreed construction schedule.</li>
            <li>Possession of the premises shall be delivered upon clearance of all outstanding dues, completion of registry deed, and execution of final handover protocols.</li>
            <li>This allotment is non-transferable without prior written consent and clearance from {{ $company->name }}.</li>
        </ol>
    </div>

</div>
@endsection
