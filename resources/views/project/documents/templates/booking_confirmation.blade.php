@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Recipient and Reference Header -->
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
            <p class="text-slate-600"><strong>Vide Booking id :</strong> <span class="font-mono">{{ $booking->booking_code }}</span></p>
            <p class="text-slate-600"><strong>Booking Date :</strong> {{ $booking->booking_date ? $booking->booking_date->format('d F Y') : date('d F Y') }}</p>
        </div>
    </div>

    <!-- Subject & Welcome -->
    <div class="space-y-2">
        <div class="inline-block px-3 py-1 bg-sky-50 text-sky-900 font-bold uppercase text-xs tracking-wider rounded-md border border-sky-200">
            Subject : Booking confirmation
        </div>
        <p class="font-bold text-emerald-800 text-base">Congratulations..!</p>
        <p class="text-xs text-slate-600">Greetings of the day! Dear sir/ma'am,</p>
        <p class="text-xs text-slate-700 leading-relaxed">
            In reference to your application/expression of interest, in response to the above, we are pleased to confirm your booking as per the particulars given below:
        </p>
    </div>

    <!-- Information Dual Grid (Customer Information & Property Information) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Customer Information Card -->
        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
            <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[11px] text-slate-700 border-b border-slate-200 flex items-center space-x-1.5">
                <i class="fa-solid fa-user text-sky-600 text-xs"></i>
                <span>Customer Information</span>
            </div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold w-1/3">Customer name</td>
                        <td class="py-2 px-3 font-bold text-slate-900">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Contact no.</td>
                        <td class="py-2 px-3 font-mono text-slate-800">{{ $booking->mobile_no }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Email id</td>
                        <td class="py-2 px-3 text-slate-800">{{ $booking->email_id ?? 'Not available' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Customer PAN</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-800">{{ $booking->pan_number ?? 'Not Applicable' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Customer GSTIN</td>
                        <td class="py-2 px-3 font-mono text-slate-800">{{ $booking->gstin ?? 'Not Applicable' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold align-top">Party address</td>
                        <td class="py-2 px-3 text-slate-700 leading-relaxed">{{ $booking->address ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Property Information Card -->
        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
            <div class="bg-slate-100 px-3.5 py-2 font-bold uppercase text-[11px] text-slate-700 border-b border-slate-200 flex items-center space-x-1.5">
                <i class="fa-solid fa-building text-sky-600 text-xs"></i>
                <span>Property Information</span>
            </div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold w-1/3">Project / Address</td>
                        <td class="py-2 px-3 text-slate-800 font-bold">
                            {{ $project->name }}<br>
                            <span class="text-[11px] font-normal text-slate-600">
                                Dag: {{ $project->daag_no ?? '-' }} | Patta: {{ $project->patta_no ?? '-' }}<br>
                                {{ $project->full_address }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Booking id</td>
                        <td class="py-2 px-3 font-mono font-bold text-sky-700">{{ $booking->booking_code }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Block / Floor</td>
                        <td class="py-2 px-3 text-slate-800">{{ $booking->block_name ?? 'Block-I' }} / {{ $booking->floor_no ?? 'Ground Floor' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Unit number</td>
                        <td class="py-2 px-3 font-bold text-slate-900 font-mono">{{ $booking->unit_no }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">*Built-up area</td>
                        <td class="py-2 px-3 font-mono text-slate-800">{{ number_format($booking->built_up_area, 2) }} Sq. Ft.*</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">*Super Built-up area</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.*</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">RERA Reg. no.</td>
                        <td class="py-2 px-3 font-mono text-emerald-800">{{ $project->rera_reg_no ?? 'RERAA CA 179 OF 2024-25' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 text-slate-500 font-semibold">Description</td>
                        <td class="py-2 px-3 text-slate-700">{{ $booking->property_type }} with {{ ucfirst(str_replace('_', ' ', $booking->parking_type)) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Page Break for Clean 2-Page Print -->
    <div class="page-break pt-4"></div>

    <!-- Page 2: Property Pricing & Breakups -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <div class="bg-slate-800 text-white px-4 py-2.5 font-bold uppercase text-xs tracking-wider flex items-center justify-between">
            <span>Property Pricing & Breakups</span>
            <span class="font-mono text-amber-300">{{ $booking->booking_code }}</span>
        </div>
        <table class="w-full text-xs font-mono">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Rate per sq. ft. :</td>
                    <td class="py-2 px-4 text-right font-bold text-slate-900">₹{{ number_format($booking->rate_per_sqft, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Unit cost :</td>
                    <td class="py-2 px-4 text-right font-semibold text-slate-800">₹{{ number_format($booking->unit_cost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Parking cost :</td>
                    <td class="py-2 px-4 text-right font-semibold text-slate-800">₹{{ number_format($booking->parking_cost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Transformer cost :</td>
                    <td class="py-2 px-4 text-right font-semibold text-slate-800">₹{{ number_format($booking->transformer_cost, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-sans text-slate-600">Amenities charges :</td>
                    <td class="py-2 px-4 text-right font-semibold text-slate-800">₹{{ number_format($booking->amenities_cost, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold">
                    <td class="py-2 px-4 font-sans text-slate-700">Gross booking value :</td>
                    <td class="py-2 px-4 text-right text-slate-900">₹{{ number_format($booking->gross_total, 2) }}</td>
                </tr>
                <tr class="text-rose-700">
                    <td class="py-2 px-4 font-sans font-semibold">Discount / adjustments :</td>
                    <td class="py-2 px-4 text-right font-bold">- ₹{{ number_format($totalDiscount, 2) }}</td>
                </tr>
                <tr class="bg-amber-50/70 font-bold border-t-2 border-slate-300">
                    <td class="py-2.5 px-4 font-sans text-amber-950 text-sm">Consideration value :</td>
                    <td class="py-2.5 px-4 text-right text-amber-950 text-sm">₹{{ number_format($consideration, 2) }}</td>
                </tr>
                <tr class="bg-slate-100 font-sans">
                    <td class="py-2.5 px-4 font-bold text-slate-700">Amount in words :</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900 text-xs italic">{{ $amountInWords }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Note & Instructions -->
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 space-y-2">
        <p class="font-bold text-slate-900">Note :</p>
        <p class="leading-relaxed">
            Kindly confirm the above booking details within seven working days from the date of booking and contact us immediately for any modification/change.
        </p>
        <p class="leading-relaxed text-slate-600">
            This confirmation letter is issued on the basis of understanding & acknowledging all the property schedule, specifications, terms, conditions, payment slab, cancellation & refund policy etc. by the customer properly as mentioned in the booking form.
        </p>
    </div>

    <!-- Terms & Conditions -->
    <div class="border border-slate-200 rounded-xl p-4 text-xs text-slate-700 space-y-1.5">
        <p class="font-bold uppercase tracking-wide text-slate-800 text-[11px] mb-1">Terms & Conditions :</p>
        <ol class="list-decimal list-inside space-y-1 text-slate-600">
            <li>GST / Taxes are extra as applicable.</li>
            <li>All legal expenses (registration, stamp duty, deed fees) are extra.</li>
            <li>Rates finalized for standard specification only.</li>
            <li>Actual unit size may vary during practical execution (*More or Less / Approximately).</li>
            <li>The refund policy is entirely up-to the Builder's sole discretion.</li>
        </ol>
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
