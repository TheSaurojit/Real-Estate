<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Supplier;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Display project construction site expenses ledger
     */
    public function index(Request $request, Project $project): View
    {
        $query = Expense::where('project_id', $project->id)
            ->with(['supplier', 'bankAccount', 'creator'])
            ->latest('expense_date')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('expense_code', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category')) {
            $query->where('expense_category', $request->input('category'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->input('to_date'));
        }

        $expenses = $query->paginate(15)->withQueryString();

        // Financial Stats
        $stats = [
            'total_material' => (float)Expense::where('project_id', $project->id)->where('expense_category', 'material_purchase')->sum('gross_amount'),
            'total_labor'    => (float)Expense::where('project_id', $project->id)->where('expense_category', 'labor_contractor')->sum('gross_amount'),
            'total_overhead' => (float)Expense::where('project_id', $project->id)->whereIn('expense_category', ['site_overheads', 'machinery_equipment', 'statutory_permits', 'administrative'])->sum('gross_amount'),
            'total_expenses' => (float)Expense::where('project_id', $project->id)->sum('gross_amount'),
        ];

        return view('project.expenses.index', compact('project', 'expenses', 'stats'));
    }

    /**
     * Show the form for creating a new site expense
     */
    public function create(Request $request, Project $project): View
    {
        $companyId = $project->company_id;
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->get();
        $nextExpenseCode = $this->autoNumberService->peekNextNumber('expense', $companyId);

        return view('project.expenses.create', compact('project', 'suppliers', 'bankAccounts', 'nextExpenseCode'));
    }

    /**
     * Store a newly recorded site expense
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category' => ['required', 'in:material_purchase,labor_contractor,site_overheads,administrative,machinery_equipment,statutory_permits'],
            'expense_date'     => ['required', 'date'],
            'item_name'        => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string', 'max:500'],
            'supplier_id'      => ['nullable', 'exists:suppliers,id'],
            'quantity'         => ['required', 'numeric', 'min:0.01'],
            'unit_measure'     => ['required', 'string', 'max:30'],
            'unit_rate'        => ['required', 'numeric', 'min:0'],
            'tax_rate'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_no'       => ['nullable', 'string', 'max:100'],
            'invoice_date'     => ['nullable', 'date'],
            'payment_mode'     => ['required', 'in:cash,bank_transfer,cheque,neft,rtgs,upi'],
            'bank_account_id'  => ['nullable', 'exists:bank_accounts,id'],
            'payment_ref_no'   => ['nullable', 'string', 'max:100'],
            'payment_status'   => ['required', 'in:paid,partial,pending'],
        ]);

        $companyId = $project->company_id;
        $qty = (float)$validated['quantity'];
        $rate = (float)$validated['unit_rate'];
        $taxRate = (float)($validated['tax_rate'] ?? 0);

        $subTotal = round($qty * $rate, 2);
        $taxAmount = round(($subTotal * $taxRate) / 100, 2);
        $grossAmount = round($subTotal + $taxAmount, 2);

        $expenseCode = $this->autoNumberService->getNextNumber('expense', $companyId, true);

        Expense::create([
            'project_id'       => $project->id,
            'company_id'       => $companyId,
            'supplier_id'      => $validated['supplier_id'] ?? null,
            'bank_account_id'  => $validated['bank_account_id'] ?? null,
            'expense_code'     => $expenseCode,
            'expense_date'     => $validated['expense_date'],
            'expense_category' => $validated['expense_category'],
            'item_name'        => $validated['item_name'],
            'description'      => $validated['description'] ?? null,
            'quantity'         => $qty,
            'unit_measure'     => $validated['unit_measure'],
            'unit_rate'        => $rate,
            'sub_total'        => $subTotal,
            'tax_rate'         => $taxRate,
            'tax_amount'       => $taxAmount,
            'gross_amount'     => $grossAmount,
            'invoice_no'       => $validated['invoice_no'] ?? null,
            'invoice_date'     => $validated['invoice_date'] ?? null,
            'payment_mode'     => $validated['payment_mode'],
            'payment_status'   => $validated['payment_status'],
            'payment_ref_no'   => $validated['payment_ref_no'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        return redirect()->route('project.expenses.index', $project->id)
            ->with('success', "Site expense recorded successfully! Code: {$expenseCode}");
    }

    /**
     * Delete an expense record
     */
    public function destroy(Project $project, Expense $expense): RedirectResponse
    {
        $code = $expense->expense_code;
        $expense->delete();

        return redirect()->route('project.expenses.index', $project->id)
            ->with('success', "Expense {$code} deleted successfully.");
    }
}
