@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Recipient & Reference Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-slate-200 pb-4 text-xs">
        <div class="space-y-1">
            <p class="font-bold text-slate-400 uppercase text-[10px]">To,</p>
            <p class="font-bold text-slate-900 text-sm">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</p>
            @if($booking->guardian_name)
                <p class="text-slate-600">{{ str_replace('_', ' ', ucfirst($booking->guardian_relation)) }}: {{ $booking->guardian_name }}</p>
            @endif
            @if($booking->address)
                <p class="text-slate-600 max-w-sm">{{ $booking->address }}</p>
            @endif
        </div>
        <div class="text-left sm:text-right space-y-1">
            <p class="text-slate-600"><strong>Ref. no. :</strong> <span class="font-mono">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Date :</strong> {{ date('d F Y') }}</p>
        </div>
    </div>

    <!-- Subject & Welcome -->
    <div class="space-y-2">
        <div class="inline-block px-3 py-1 bg-indigo-50 text-indigo-900 font-bold uppercase text-xs tracking-wider rounded-md border border-indigo-200">
            Subject : Allotment letter
        </div>
        <p class="font-bold text-emerald-800 text-base">Congratulations..!</p>
        <p class="text-xs text-slate-600">Greetings of the day! Dear sir/ma'am,</p>
        <p class="text-xs text-slate-700 leading-relaxed">
            We are delighted to inform you that, we have the exclusive transferable rights to the property described below, which has been allotted to your favour by us against your application/expression of interest towards the property.
        </p>
    </div>

    <!-- Property Information Card -->
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
        <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[11px] text-slate-700 border-b border-slate-200 flex items-center space-x-1.5">
            <i class="fa-solid fa-hotel text-indigo-600 text-xs"></i>
            <span>Property Information</span>
        </div>
        <table class="w-full text-xs">
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold w-1/3">Project / Property Name & Address :</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900">
                        {{ $project->name }}<br>
                        <span class="text-[11px] font-normal text-slate-600">
                            Dag No.: {{ $project->daag_no ?? '-' }} | Patta No.: {{ $project->patta_no ?? '-' }} [2nd RS]<br>
                            Holding No.: {{ $project->holding_no ?? '-' }}<br>
                            {{ $project->full_address }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Booking ID :</td>
                    <td class="py-2.5 px-4 font-mono font-bold text-indigo-700">{{ $booking->booking_code }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Booking Date :</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ $booking->booking_date ? $booking->booking_date->format('d F Y') : date('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Block / Tower No. :</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ $booking->block_name ?? 'Block-I' }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Floor Number :</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ $booking->floor_no ?? 'Ground Floor' }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Unit Number :</td>
                    <td class="py-2.5 px-4 font-bold text-slate-900 font-mono text-sm">{{ $booking->unit_no }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">*Built-up area :</td>
                    <td class="py-2.5 px-4 font-mono text-slate-800">{{ number_format($booking->built_up_area, 2) }} Sq. Ft.*</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">*Super Built-up area :</td>
                    <td class="py-2.5 px-4 font-mono font-bold text-slate-900">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.*</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">RERA Reg. no. :</td>
                    <td class="py-2.5 px-4 font-mono text-emerald-800 font-bold">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 text-slate-500 font-semibold">Property Description :</td>
                    <td class="py-2.5 px-4 text-slate-800">{{ $booking->property_type }} with {{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Statutory & Allotment Conditions -->
    <div class="space-y-2 text-xs text-slate-700 leading-relaxed bg-slate-50 border border-slate-200 rounded-xl p-4">
        <p>
            The allotment shall become final and binding upon the builder only after you sign and execute the aforesaid Sale Agreement in respect to the above property.
        </p>
        <p>
            This letter of allotment is subject to the terms & conditions set out in the annexure forming part hereof, as well as those contained in the Agreement to Sell to be executed.
        </p>
        <p>
            This Allotment Letter is construed in accordance with Act, Rules and regulations made thereunder including other applicable Laws of India for the time being in force.
        </p>
        <p class="text-[10px] text-slate-400 italic">* More or Less / Approximately</p>
    </div>

    <!-- Page Break for Clean 2-Page Print -->
    <div class="page-break pt-4"></div>

    <!-- Page 2: 45-Day Agreement Clause & Undertaking -->
    <div class="border border-slate-200 rounded-xl p-5 text-xs text-slate-700 space-y-3 bg-white">
        <p class="font-bold uppercase tracking-wide text-slate-800 text-[11px]">Execution of Agreement to Sell & Cancellation Clause :</p>
        <p class="leading-relaxed text-justify text-slate-600">
            The above provisional allotment of the Unit in your favour, is further subject to you making timely payment to us as per the Payment Plan and execution of the standard Flat buyer's agreement/Agreement to Sell with us within 45 (forty-five only) days from the date of its dispatch by us at your address as notified by you in the said application. The Agreement to Sell stipulates the detailed terms and conditions of the contemplated sale of the Unit in your favour.
        </p>
        <p class="leading-relaxed text-justify text-slate-600">
            If you fail to sign and return the executed copy of the Agreement to Sell within the stipulated period of 45 (forty-five only) days and/or if you fail to comply with any of your obligations as per application form or this provisional allotment including but not limited to making of timely payments as aforesaid, then we shall be fully entitled, at its sole discretion, at any stage, to cancel the allotment of the Unit and forfeit the entire Earnest Money. In such an event you will also not be entitled for the refund of amounts paid towards statutory charges, interest on delayed payment etc.
        </p>
    </div>

    <!-- Builder Closing Note -->
    <div class="pt-2 text-xs text-slate-600 space-y-1">
        <p class="font-bold text-slate-800">Thank you for business with us !</p>
        <p>Assuring you the best of our services at all times.</p>
        <p>For any clarification feel free to contact our team anytime!</p>
    </div>

    <!-- Signatures -->
    <div class="pt-4 flex items-end justify-between text-xs text-slate-600">
        <div>
            <p><strong>Place :</strong> {{ $project->mouza ?? 'Silchar' }}</p>
            <p><strong>Date :</strong> {{ date('d F Y') }}</p>
        </div>
        <div class="text-right space-y-1">
            <p class="font-bold text-slate-900">For, {{ $company->name }}</p>
            <div class="h-10"></div>
            <p class="text-[11px] text-slate-400">Authorized Signatory</p>
        </div>
    </div>

</div>
@endsection
