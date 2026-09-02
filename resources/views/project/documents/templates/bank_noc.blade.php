@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Recipient Bank Address -->
    <div class="space-y-1">
        <p class="font-bold text-slate-900">To,</p>
        <p class="font-bold text-slate-800">The Branch Manager,</p>
        <p class="font-bold text-slate-900">{{ $booking->bankFinance?->lending_bank_name ?? 'Lending Bank / Financial Institution' }}</p>
        <p class="text-xs text-slate-600">{{ $booking->bankFinance?->branch_name ?? 'Branch Office' }}</p>
    </div>

    <!-- Subject Banner -->
    <div class="p-3 bg-teal-50 border border-teal-300 rounded-xl text-teal-900 font-bold uppercase text-xs tracking-wider">
        NO OBJECTION CERTIFICATE (NOC) FOR HOUSING LOAN SANCTION & MORTGAGE CREATION
    </div>

    <p>
        Dear Sir / Madam,
    </p>

    <p>
        This is to certify that we, <strong>{{ $company->name }}</strong>, the absolute promoters and developers of the real estate project known as <strong>"{{ $project->name }}"</strong> situated at <strong>{{ $project->full_address }}</strong> (RERA Reg No: {{ $project->rera_reg_no ?? 'Under Process' }}), have agreed to allot the apartment described below to:
    </p>

    <!-- Borrower / Property Specs -->
    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2 text-xs">
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Borrower / Allottee Name:</span>
            <span class="col-span-2 font-bold text-slate-900">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Allotted Flat / Unit No:</span>
            <span class="col-span-2 font-mono font-bold text-indigo-900">{{ $booking->unit_no }} (Block: {{ $booking->block_name ?? '-' }}, Floor: {{ $booking->floor_no ?? '-' }})</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Super Built-up Area:</span>
            <span class="col-span-2 font-mono font-bold text-slate-900">{{ number_format($booking->super_built_up_area, 2) }} Sq. Ft.</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <span class="font-bold text-slate-600">Total Agreed Consideration:</span>
            <span class="col-span-2 font-mono font-bold text-slate-900">₹{{ number_format($booking->total_booking_value, 2) }}</span>
        </div>
        @if($booking->bankFinance?->loan_account_no)
            <div class="grid grid-cols-3 gap-2">
                <span class="font-bold text-slate-600">Loan File / Account Reference:</span>
                <span class="col-span-2 font-mono font-bold text-teal-800">{{ $booking->bankFinance->loan_account_no }}</span>
            </div>
        @endif
    </div>

    <!-- Undertakings -->
    <div class="text-xs text-slate-700 space-y-2">
        <h4 class="font-bold text-slate-900 uppercase">We hereby confirm and undertake that:</h4>
        <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
            <li>We have <strong>No Objection</strong> to your bank granting a housing loan facility to the allottee and creating an equitable mortgage / charge on the aforementioned demised flat.</li>
            <li>The title of the land is clear, marketable, and free from encumbrances.</li>
            <li>All loan disbursement tranches must be remitted strictly via NEFT/RTGS into our designated project escrow account specified below:</li>
        </ol>
    </div>

    <!-- Bank Escrow Details -->
    <div class="bg-slate-900 text-white p-4 rounded-xl text-xs font-mono grid grid-cols-2 gap-2">
        <div>Developer A/C Name: <strong class="text-white">{{ $escrowAccount?->account_name ?? $company->name }}</strong></div>
        <div>Bank Name: <strong class="text-white">{{ $escrowAccount?->bank_name ?? 'HDFC Bank' }}</strong></div>
        <div>Account Number: <strong class="text-emerald-400 font-bold text-sm">{{ $escrowAccount?->account_number ?? '50200012345678' }}</strong></div>
        <div>IFSC Code: <strong class="text-white">{{ $escrowAccount?->ifsc_code ?? 'HDFC0001234' }}</strong></div>
    </div>

</div>
@endsection
