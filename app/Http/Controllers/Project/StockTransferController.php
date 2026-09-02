<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StockTransfer;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Display inter-project stock transfers ledger
     */
    public function index(Request $request, Project $project): View
    {
        // Outward transfers (Material moved from this project to another)
        $outwardTransfers = StockTransfer::where('source_project_id', $project->id)
            ->with(['destinationProject', 'transferrer'])
            ->latest('transfer_date')
            ->get();

        // Inward transfers (Material received from another project into this project)
        $inwardTransfers = StockTransfer::where('destination_project_id', $project->id)
            ->with(['sourceProject', 'transferrer'])
            ->latest('transfer_date')
            ->get();

        $stats = [
            'total_outward_value' => (float)$outwardTransfers->sum('total_transfer_value'),
            'total_inward_value'  => (float)$inwardTransfers->sum('total_transfer_value'),
            'net_transfer_impact' => (float)($inwardTransfers->sum('total_transfer_value') - $outwardTransfers->sum('total_transfer_value')),
        ];

        return view('project.stock_transfers.index', compact('project', 'outwardTransfers', 'inwardTransfers', 'stats'));
    }

    /**
     * Show form to initiate a new inter-project stock transfer
     */
    public function create(Request $request, Project $project): View
    {
        $companyId = $project->company_id;
        $otherProjects = Project::where('company_id', $companyId)
            ->where('id', '!=', $project->id)
            ->where('is_active', true)
            ->get();

        $nextTransferCode = $this->autoNumberService->peekNextNumber('stock_transfer', $companyId);

        return view('project.stock_transfers.create', compact('project', 'otherProjects', 'nextTransferCode'));
    }

    /**
     * Store inter-project stock transfer
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'destination_project_id' => ['required', 'exists:projects,id', 'different:source_project_id'],
            'transfer_date'          => ['required', 'date'],
            'material_name'          => ['required', 'string', 'max:200'],
            'quantity'               => ['required', 'numeric', 'min:0.01'],
            'unit_measure'           => ['required', 'string', 'max:30'],
            'unit_cost'              => ['required', 'numeric', 'min:0'],
            'vehicle_no'             => ['nullable', 'string', 'max:50'],
            'challan_no'             => ['nullable', 'string', 'max:100'],
            'remarks'                => ['nullable', 'string', 'max:500'],
        ]);

        $companyId = $project->company_id;
        $qty = (float)$validated['quantity'];
        $unitCost = (float)$validated['unit_cost'];
        $totalVal = round($qty * $unitCost, 2);

        $transferCode = $this->autoNumberService->getNextNumber('stock_transfer', $companyId, true);

        StockTransfer::create([
            'company_id'             => $companyId,
            'source_project_id'      => $project->id,
            'destination_project_id' => $validated['destination_project_id'],
            'transfer_code'          => $transferCode,
            'transfer_date'          => $validated['transfer_date'],
            'material_name'          => $validated['material_name'],
            'quantity'               => $qty,
            'unit_measure'           => $validated['unit_measure'],
            'unit_cost'              => $unitCost,
            'total_transfer_value'   => $totalVal,
            'vehicle_no'             => $validated['vehicle_no'] ?? null,
            'challan_no'             => $validated['challan_no'] ?? null,
            'remarks'                => $validated['remarks'] ?? null,
            'transferred_by'         => auth()->id(),
        ]);

        return redirect()->route('project.stock-transfers.index', $project->id)
            ->with('success', "Stock transfer recorded successfully! Transfer Code: {$transferCode}");
    }
}
