@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Certificate Header Banner -->
    <div class="text-center space-y-1.5 border-b border-slate-200 pb-4">
        <h2 class="text-xl font-black uppercase tracking-wider text-slate-900">NO OBJECTION / NO DUE CERTIFICATE</h2>
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">To Whom It May Concern</p>
    </div>

    <!-- Developer & Project Declaration -->
    <div class="text-xs text-slate-700 leading-relaxed space-y-3 text-justify">
        <p>
            This is to certify that, I am the authorized representative / owner of the developer enterprise named 
            <strong>{{ $company->name }}</strong>, having its registered office at <strong>{{ $company->address }}</strong>, 
            having PAN no. <strong>{{ $company->pan_number ?? 'N/A' }}</strong>, is the Developer/Builder of the residential/commercial 
            project named <strong>"{{ $project->name }}"</strong> located at <strong>{{ $project->full_address }}</strong>
            (RERA Reg. No.: <strong>{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</strong>).
        </p>

        <p>
            I/We, hereby declare that, I/we have sold the following property to 
            <strong>{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong> 
            [PAN : <strong>{{ $booking->pan_number ?? 'Not Available' }}</strong>], 
            residing at <strong>{{ $booking->address ?? 'Not Available' }}</strong>.
        </p>
    </div>

    <!-- Property Information Card -->
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[11px] text-slate-700 border-b border-slate-200 flex items-center space-x-1.5">
            <i class="fa-solid fa-hotel text-teal-600 text-xs"></i>
            <span>Allotted Property Information</span>
        </div>
        <table class="w-full text-xs">
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold w-1/3">Project Name & Address :</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900">
                        {{ $project->name }}, {{ $project->full_address }}
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Block & Floor No. :</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ $booking->block_name ?? 'Block-I' }} / {{ $booking->floor_no ?? 'First Floor' }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Unit / Flat Number :</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900 font-mono text-sm">{{ $booking->unit_no }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">*Built-up & Super Built-up Area :</td>
                    <td class="py-2.5 px-4 font-mono text-slate-800">
                        Built-up: {{ number_format($booking->built_up_area, 2) }} Sq. Ft. | Super Built-up: {{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.*
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Property Description :</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ $booking->property_type }} with {{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">RERA Reg. no. :</td>
                    <td class="py-2.5 px-4 font-mono font-bold text-emerald-800">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- No Objection Utilities Grant -->
    <div class="border border-teal-200 bg-teal-50/50 rounded-xl p-4 text-xs text-slate-800 space-y-2">
        <p class="font-bold text-teal-950 uppercase tracking-wide text-[11px]">
            Grant of No Objection for Utility Connections :
        </p>
        <p>
            I/We, further hereby declare that, I/We have no objection if the said purchaser 
            <strong>{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong> applies for and obtains:
        </p>
        <ol class="list-decimal list-inside space-y-1 text-slate-700 pl-2">
            <li>A New Individual Electricity Connection / Meter from the Concerned Department / APDCL / Electricity Authority.</li>
            <li>A New Individual Water Connection from the Concerned Municipal / Water Department.</li>
            <li>Any utility services such as telephone, broadband / internet connections, or piped gas from the Concerned Authorities.</li>
        </ol>
    </div>

    <!-- No Dues Certificate Text -->
    <div class="border border-slate-200 rounded-xl p-4 text-xs text-slate-700 space-y-2 bg-white leading-relaxed text-justify">
        <p class="font-bold uppercase tracking-wide text-slate-800 text-[11px]">Zero Outstanding Dues Certification :</p>
        <p>
            I/We, further certify that there are no outstanding dues payable to us by the respective purchaser regarding the aforementioned Property that would restrict the procurement of these utility services. I/We authorize the relevant departments and government authorities to process the application for the transfer or installation of these connections in the name of the purchaser(s).
        </p>
    </div>

    <!-- Closing Remarks & Signatures -->
    <div class="pt-4 flex items-end justify-between text-xs text-slate-600">
        <div>
            <p><strong>Place :</strong> {{ $project->mouza ?? 'Silchar' }}</p>
            <p><strong>Date :</strong> {{ date('d F Y') }}</p>
            <p class="pt-2 text-slate-400 text-[10px]">* More or Less / Approximately</p>
        </div>
        <div class="text-right space-y-1">
            <p class="font-bold text-slate-900">For, {{ $company->name }}</p>
            <div class="h-12"></div>
            <p class="text-[11px] text-slate-400">Authorized Signatory</p>
        </div>
    </div>

</div>
@endsection
