<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Project;
use App\Models\StockTransfer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Reports Hub Index
     */
    public function index(Request $request, Project $project): View
    {
        return view('project.reports.index', compact('project'));
    }

    /**
     * Report 1: Project Profitability & Cost vs Revenue Analysis
     */
    public function profitability(Request $request, Project $project): View
    {
        // 1. Revenue
        $activeBookings = $project->bookings()->where('status', '!=', 'cancelled')->get();
        $projectedRevenue = (float)$activeBookings->sum('total_booking_value');
        $realizedBank = (float)Transaction::where('project_id', $project->id)
            ->where('voucher_type', 'money_receipt')
            ->whereIn('instrument_status', ['cleared', 'not_applicable'])
            ->sum('amount');
        $realizedCash = (float)Transaction::where('project_id', $project->id)
            ->where('voucher_type', 'receipt_voucher')
            ->whereIn('instrument_status', ['cleared', 'not_applicable'])
            ->sum('amount');
        $totalRealizedInflow = $realizedBank + $realizedCash;

        // 2. Expenses
        $materialCost = (float)Expense::where('project_id', $project->id)->where('expense_category', 'material_purchase')->sum('gross_amount');
        $laborCost = (float)Expense::where('project_id', $project->id)->where('expense_category', 'labor_contractor')->sum('gross_amount');
        $overheadCost = (float)Expense::where('project_id', $project->id)->whereIn('expense_category', ['site_overheads', 'machinery_equipment', 'statutory_permits', 'administrative'])->sum('gross_amount');

        // Stock Transfer net impact (Inward transfers add cost, Outward transfers reduce cost)
        $inwardStock = (float)StockTransfer::where('destination_project_id', $project->id)->sum('total_transfer_value');
        $outwardStock = (float)StockTransfer::where('source_project_id', $project->id)->sum('total_transfer_value');
        $netStockImpact = $inwardStock - $outwardStock;

        $totalDirectExpenses = $materialCost + $laborCost + $overheadCost + $netStockImpact;

        // Profit Margins
        $projectedGrossProfit = $projectedRevenue - $totalDirectExpenses;
        $projectedProfitMargin = $projectedRevenue > 0 ? round(($projectedGrossProfit / $projectedRevenue) * 100, 2) : 0;

        $realizedCashFlowNet = $totalRealizedInflow - $totalDirectExpenses;

        return view('project.reports.profitability', compact(
            'project',
            'projectedRevenue',
            'realizedBank',
            'realizedCash',
            'totalRealizedInflow',
            'materialCost',
            'laborCost',
            'overheadCost',
            'inwardStock',
            'outwardStock',
            'netStockImpact',
            'totalDirectExpenses',
            'projectedGrossProfit',
            'projectedProfitMargin',
            'realizedCashFlowNet'
        ));
    }

    /**
     * Report 2: Unit Sales & Inventory Velocity Report
     */
    public function inventory(Request $request, Project $project): View
    {
        $bookings = $project->bookings()->get();

        $totalUnits = $bookings->count();
        $liveUnits = $bookings->where('status', 'live')->count();
        $agreementDone = $bookings->where('status', 'executed_agreement')->count();
        $deedDone = $bookings->where('status', 'registered_deed')->count();
        $cancelledUnits = $bookings->where('status', 'cancelled')->count();

        $totalSoldArea = (float)$bookings->where('status', '!=', 'cancelled')->sum('super_built_up_area');
        $totalSoldValue = (float)$bookings->where('status', '!=', 'cancelled')->sum('total_booking_value');
        $avgRatePerSqft = $totalSoldArea > 0 ? round($totalSoldValue / $totalSoldArea, 2) : 0;

        return view('project.reports.inventory', compact(
            'project',
            'bookings',
            'totalUnits',
            'liveUnits',
            'agreementDone',
            'deedDone',
            'cancelledUnits',
            'totalSoldArea',
            'totalSoldValue',
            'avgRatePerSqft'
        ));
    }

    /**
     * Report 3: Customer Dues & Aging Analysis
     */
    public function aging(Request $request, Project $project): View
    {
        $bookings = $project->bookings()
            ->where('status', '!=', 'cancelled')
            ->with(['transactions', 'customizations'])
            ->get();

        $now = Carbon::now();
        $buckets = [
            'under_30'  => 0,
            '30_to_60'  => 0,
            '60_to_90'  => 0,
            'above_90'  => 0,
        ];

        $agedBookings = [];

        foreach ($bookings as $b) {
            $due = $b->total_outstanding_due;
            if ($due > 0) {
                $days = $now->diffInDays($b->booking_date);
                if ($days <= 30) {
                    $buckets['under_30'] += $due;
                    $bucketName = '0 - 30 Days';
                } elseif ($days <= 60) {
                    $buckets['30_to_60'] += $due;
                    $bucketName = '31 - 60 Days';
                } elseif ($days <= 90) {
                    $buckets['60_to_90'] += $due;
                    $bucketName = '61 - 90 Days';
                } else {
                    $buckets['above_90'] += $due;
                    $bucketName = '90+ Days';
                }

                $agedBookings[] = [
                    'booking'     => $b,
                    'due'         => $due,
                    'taxable_due' => $b->taxable_due_balance,
                    'cash_due'    => $b->cash_due_balance,
                    'days'        => $days,
                    'bucket'      => $bucketName,
                ];
            }
        }

        // Bounced Cheques Defaulters
        $dishonoredTx = Transaction::where('project_id', $project->id)
            ->where('instrument_status', 'dishonored')
            ->with('booking')
            ->get();

        return view('project.reports.aging', compact('project', 'buckets', 'agedBookings', 'dishonoredTx'));
    }

    /**
     * Report 4: Banking Escrow & Cash Account Flow Report
     */
    public function banking(Request $request, Project $project): View
    {
        $bankAccounts = BankAccount::where('company_id', $project->company_id)->get();

        $accountSummaries = [];
        foreach ($bankAccounts as $acc) {
            $inflows = (float)Transaction::where('bank_account_id', $acc->id)
                ->where('voucher_category', '!=', 'payment_refund')
                ->whereIn('instrument_status', ['cleared', 'not_applicable'])
                ->sum('amount');

            $outflows = (float)Transaction::where('bank_account_id', $acc->id)
                ->where('voucher_category', 'payment_refund')
                ->sum('amount');

            $expenseOutflows = (float)Expense::where('bank_account_id', $acc->id)
                ->where('payment_status', 'paid')
                ->sum('gross_amount');

            $netBalance = $inflows - ($outflows + $expenseOutflows);

            $accountSummaries[] = [
                'account'          => $acc,
                'inflows'          => $inflows,
                'refund_outflows'  => $outflows,
                'expense_outflows' => $expenseOutflows,
                'net_flow'         => $netBalance,
            ];
        }

        // Cash Account Summary
        $cashInflows = (float)Transaction::where('project_id', $project->id)
            ->whereNull('bank_account_id')
            ->where('voucher_category', '!=', 'payment_refund')
            ->sum('amount');

        $cashExpenses = (float)Expense::where('project_id', $project->id)
            ->where('payment_mode', 'cash')
            ->sum('gross_amount');

        $cashRefunds = (float)Transaction::where('project_id', $project->id)
            ->whereNull('bank_account_id')
            ->where('voucher_category', 'payment_refund')
            ->sum('amount');

        $netCash = $cashInflows - ($cashExpenses + $cashRefunds);

        return view('project.reports.banking', compact('project', 'accountSummaries', 'cashInflows', 'cashExpenses', 'cashRefunds', 'netCash'));
    }
}
