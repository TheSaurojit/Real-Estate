@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
        <div>
            <h2 class="text-xl font-black uppercase tracking-tight text-indigo-900">TECHNICAL CUSTOMIZATION JOB SHEET</h2>
            <p class="text-xs text-slate-500">Engineering Work Order for Purchaser Civil & Finishing Alterations</p>
        </div>
        <div class="text-right text-xs font-mono">
            <div>Job Sheet No: <strong>JS/{{ $booking->booking_code }}</strong></div>
            <div>Date: <strong>{{ date('d M, Y') }}</strong></div>
        </div>
    </div>

    <!-- Customer & Unit Quick Info -->
    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs grid grid-cols-3 gap-2">
        <div>Client Name: <strong class="text-slate-900">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong></div>
        <div>Unit No: <strong class="text-indigo-900 font-mono">{{ $booking->unit_no }}</strong></div>
        <div>Super Built-up: <strong class="text-slate-900 font-mono">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.</strong></div>
    </div>

    <!-- Job Sheet Line Items Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
        <table class="w-full">
            <thead class="bg-slate-100 font-bold uppercase text-slate-700 border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-3 text-left">Type</th>
                    <th class="py-2.5 px-3 text-left">Work Particulars / Description</th>
                    <th class="py-2.5 px-3 text-center">Unit / Qty</th>
                    <th class="py-2.5 px-3 text-right">Material (₹)</th>
                    <th class="py-2.5 px-3 text-right">Labour (₹)</th>
                    <th class="py-2.5 px-3 text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                @forelse($booking->customizations as $c)
                    <tr class="{{ $c->job_type === 'addon' ? 'hover:bg-emerald-50/50' : 'hover:bg-amber-50/50' }}">
                        <td class="py-2 px-3 align-top">
                            @if($c->job_type === 'addon')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">ADDON (+)</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">DISLODGE (-)</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 align-top font-sans">
                            <div class="font-bold text-slate-900">{{ $c->particular }}</div>
                            @if($c->description)
                                <div class="text-slate-500 text-[11px]">{{ $c->description }}</div>
                            @endif
                        </td>
                        <td class="py-2 px-3 align-top text-center text-slate-700">
                            {{ $c->quantity }} {{ $c->unit_measure ?? 'LS' }}
                        </td>
                        <td class="py-2 px-3 align-top text-right text-slate-700">
                            ₹{{ number_format($c->material_rate, 2) }}
                        </td>
                        <td class="py-2 px-3 align-top text-right text-slate-700">
                            ₹{{ number_format($c->labour_rate, 2) }}
                        </td>
                        <td class="py-2 px-3 align-top text-right font-bold {{ $c->job_type === 'addon' ? 'text-emerald-700' : 'text-amber-800' }}">
                            {{ $c->job_type === 'addon' ? '+' : '-' }}₹{{ number_format($c->job_total, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400 font-sans">
                            No custom alterations or job sheets requested for this property booking.
                        </td>
                    </tr>
                @endforelse

                <!-- Summary Row -->
                <tr class="bg-slate-100 font-black font-sans text-sm">
                    <td colspan="5" class="py-3 px-4 uppercase text-slate-900">
                        Net Supplementary Customization Value
                    </td>
                    <td class="py-3 px-4 text-right font-mono text-indigo-900">
                        {{ $booking->supplementary_value >= 0 ? '+' : '' }}₹{{ number_format($booking->supplementary_value, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Site Engineer Sign-off -->
    <div class="pt-8 grid grid-cols-2 gap-8 text-xs text-slate-500">
        <div class="space-y-1">
            <div class="w-48 border-b border-slate-400 pb-1"></div>
            <span class="font-bold uppercase tracking-wider text-slate-800 block">Site Project Engineer</span>
            <span class="text-[10px] text-slate-400">Technical Approval</span>
        </div>
        <div class="space-y-1 text-right">
            <div class="w-48 border-b border-slate-400 pb-1 ml-auto"></div>
            <span class="font-bold uppercase tracking-wider text-slate-800 block">Purchaser Approval</span>
            <span class="text-[10px] text-slate-400">{{ $booking->customer_name }}</span>
        </div>
    </div>

</div>
@endsection
