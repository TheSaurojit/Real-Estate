<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? 'Official Document' }} - {{ $booking->booking_code }}</title>

    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        @media print {
            body {
                background: white !important;
                color: #0f172a !important;
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .print-page {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                size: A4;
                margin: 15mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 font-sans py-8 print:py-0">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="max-w-4xl mx-auto mb-6 px-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div class="flex items-center space-x-3">
            <a href="{{ route('project.documents.index', ['project' => $project->id, 'booking_id' => $booking->id]) }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm border border-slate-200 transition flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Document Hub</span>
            </a>
            <a href="{{ route('project.bookings.show', [$project->id, $booking->id]) }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm border border-slate-200 transition flex items-center space-x-1.5">
                <i class="fa-solid fa-door-open"></i>
                <span>Booking Overview</span>
            </a>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Document Switcher Dropdown -->
            <select onchange="window.location.href = this.value" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none shadow-sm cursor-pointer">
                @foreach(\App\Http\Controllers\Project\DocumentController::DOCUMENT_TYPES as $typeKey => $typeInfo)
                    <option value="{{ route('project.documents.show', [$project->id, $booking->id, $typeKey]) }}" {{ $documentType === $typeKey ? 'selected' : '' }}>
                        {{ $typeInfo['title'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="window.print()" class="px-5 py-2 bg-slate-900 hover:bg-sky-600 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- Official Printable A4 Document Sheet -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl border border-slate-200/80 p-10 sm:p-14 print-page space-y-8">
        
        <!-- Corporate Letterhead Header -->
        <header class="border-b-2 border-slate-900 pb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="space-y-1 max-w-lg">
                @if($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="h-12 object-contain mb-2">
                @endif
                <h1 class="text-2xl font-black uppercase tracking-tight text-slate-900">{{ $company->name }}</h1>
                <p class="text-xs text-slate-600 leading-relaxed">{{ $company->address }}</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-mono text-slate-600 pt-1">
                    @if($company->gstin)<span>GSTIN: <strong>{{ $company->gstin }}</strong></span>@endif
                    @if($company->pan_number)<span>PAN: <strong>{{ $company->pan_number }}</strong></span>@endif
                    @if($company->contact_number)<span>Phone: <strong>{{ $company->contact_number }}</strong></span>@endif
                    @if($company->email)<span>Email: <strong>{{ $company->email }}</strong></span>@endif
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 shrink-0">
                <div class="inline-block px-3 py-1 rounded-lg font-black uppercase text-xs tracking-wider border border-slate-300 bg-slate-100 text-slate-800">
                    {{ $meta['title'] }}
                </div>
                <div class="text-xs font-mono font-bold text-slate-800 pt-1">
                    Ref: {{ $booking->booking_code }}/{{ strtoupper(substr($documentType, 0, 3)) }}
                </div>
                <div class="text-xs text-slate-500">
                    Date: <strong>{{ date('d M, Y') }}</strong>
                </div>
            </div>
        </header>

        <!-- Project Land Records Strip -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs text-slate-700 grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Project</span>
                <span class="font-bold text-slate-900">{{ $project->name }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Land Location</span>
                <span>Daag: <strong>{{ $project->daag_no ?? 'N/A' }}</strong>, Patta: <strong>{{ $project->patta_no ?? 'N/A' }}</strong></span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Mouza / Holding</span>
                <span>{{ $project->mouza ?? 'N/A' }} / {{ $project->holding_no ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">RERA Category</span>
                <span class="font-semibold text-emerald-800">{{ strtoupper($project->rera_category ?? 'Registered') }}</span>
            </div>
        </div>

        <!-- Dynamic Document Body Content -->
        <main class="space-y-6 text-sm text-slate-800 leading-relaxed">
            @yield('document_body')
        </main>

        <!-- Official Signatory Footer -->
        <footer class="pt-12 border-t border-slate-200 flex items-end justify-between text-xs text-slate-500">
            <div class="space-y-1">
                <div class="w-44 border-b border-slate-400 pb-1"></div>
                <span class="font-bold uppercase tracking-wider text-slate-800 text-[11px] block">Purchaser / Allottee</span>
                <span class="text-[10px] text-slate-500">{{ $booking->customer_salutation }} {{ $booking->customer_name }}</span>
            </div>

            <div class="text-right space-y-1">
                <div class="w-48 border-b border-slate-400 pb-1 ml-auto"></div>
                <span class="font-bold uppercase tracking-wider text-slate-800 text-[11px] block">Authorized Signatory</span>
                <span class="text-[10px] text-slate-500">For {{ $company->name }}</span>
            </div>
        </footer>

    </div>

</body>
</html>
