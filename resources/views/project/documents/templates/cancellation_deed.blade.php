@extends('project.documents.templates.layout')

@section('document_body')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="text-center space-y-1 pb-3 border-b-2 border-slate-900">
        <h2 class="text-xl font-black uppercase tracking-wider text-rose-800">MUTUAL CANCELLATION & SETTLEMENT DEED</h2>
        <p class="text-xs text-slate-500 font-medium">Formal Revocation of Unit Allotment & Full Financial Settlement</p>
    </div>

    <p>
        This Deed of Cancellation is executed on this <strong>{{ $booking->cancellation_date?->format('d M, Y') ?? date('d M, Y') }}</strong> between <strong>{{ $company->name }}</strong> ("The Developer") and <strong>{{ $booking->customer_salutation }} {{ $booking->customer_name }}</strong> ("The Purchaser").
    </p>

    <!-- Cancellation Financial Settlement Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
        <div class="bg-slate-100 px-4 py-2 font-bold uppercase text-xs text-slate-800 border-b border-slate-300">
            Cancellation Accounting & Payout Settlement
        </div>
        <table class="w-full font-mono">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-700">Allotted Flat / Unit Reference</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">{{ $booking->unit_no }} ({{ $booking->booking_code }})</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-700">Total Money Received in Bank Accounts</td>
                    <td class="py-2.5 px-4 text-right text-slate-900">₹{{ number_format($booking->total_taxable_received, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 px-4 font-sans text-slate-700">Total Money Received in Cash Accounts</td>
                    <td class="py-2.5 px-4 text-right text-slate-900">₹{{ number_format($booking->total_cash_received, 2) }}</td>
                </tr>
                <tr class="bg-rose-50/50 text-rose-700">
                    <td class="py-2.5 px-4 font-sans font-bold">Less: Agreed Cancellation Penalty Charge</td>
                    <td class="py-2.5 px-4 text-right font-bold">-₹{{ number_format($booking->cancellation_charge, 2) }}</td>
                </tr>
                <tr class="bg-slate-900 text-white font-bold font-sans text-sm">
                    <td class="py-3 px-4 uppercase text-emerald-300">Net Refund Payable to Purchaser / Lending Bank</td>
                    <td class="py-3 px-4 text-right font-mono text-emerald-400">
                        ₹{{ number_format(max(0, (float)$booking->total_taxable_received + (float)$booking->total_cash_received - (float)$booking->cancellation_charge), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Legal Release Terms -->
    <div class="text-xs text-slate-700 space-y-2 leading-relaxed">
        <h4 class="font-bold text-slate-900 uppercase">Terms of Termination & Mutual Release:</h4>
        <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
            <li>The allottee hereby surrenders all rights, claims, titles, and interests in the aforementioned apartment {{ $booking->unit_no }} back to {{ $company->name }}.</li>
            <li>The developer is now fully entitled to re-allot, market, or sell the aforesaid unit to any third-party buyer without requiring further consent from the purchaser.</li>
            <li>Upon completion of the refund disbursement mentioned above, both parties release and forever discharge each other from all liabilities, obligations, and claims arising under the original booking.</li>
        </ol>
    </div>

</div>
@endsection
