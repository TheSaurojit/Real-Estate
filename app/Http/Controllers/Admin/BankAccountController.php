<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Project;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    public function index(): View
    {
        $bankAccounts = BankAccount::with(['company', 'project'])->latest()->get();
        return view('admin.bank_accounts.index', compact('bankAccounts'));
    }

    public function create(): View
    {
        $company = Company::first();
        $companies = Company::all();
        $projects = Project::where('is_active', true)->get();
        $nextAccountCode = $this->autoNumberService->peekNextNumber('bank_account', $company?->id);

        return view('admin.bank_accounts.create', compact('company', 'companies', 'projects', 'nextAccountCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id'        => ['required', 'exists:companies,id'],
            'account_type'      => ['required', 'in:general,project_linked,cash_account'],
            'project_id'        => ['nullable', 'required_if:account_type,project_linked', 'exists:projects,id'],
            'account_nick_name' => ['required', 'string', 'max:255', 'unique:bank_accounts,account_nick_name'],
            'account_name'      => ['required', 'string', 'max:255'],
            'account_number'    => ['nullable', 'string', 'max:100'],
            'bank_name'         => ['required', 'string', 'max:255'],
            'branch'            => ['nullable', 'string', 'max:255'],
            'ifsc_code'         => ['nullable', 'string', 'max:50'],
            'is_default'        => ['boolean'],
        ]);

        $companyId = (int)$validated['company_id'];
        $accountCode = $this->autoNumberService->getNextNumber('bank_account', $companyId, true);

        $validated['account_code'] = $accountCode;
        $validated['is_default'] = $request->boolean('is_default', false);

        if ($validated['account_type'] === 'general' || $validated['account_type'] === 'cash_account') {
            $validated['project_id'] = null;
        }

        $account = BankAccount::create($validated);

        return redirect()->route('admin.bank-accounts.index')->with('created_account', [
            'code' => $account->account_code,
            'name' => $account->account_nick_name,
        ])->with('success', "Bank Account created successfully! Account ID: {$account->account_code}");
    }

    public function edit(BankAccount $bankAccount): View
    {
        $companies = Company::all();
        $projects = Project::where('is_active', true)->get();

        return view('admin.bank_accounts.edit', compact('bankAccount', 'companies', 'projects'));
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $validated = $request->validate([
            'company_id'        => ['required', 'exists:companies,id'],
            'account_type'      => ['required', 'in:general,project_linked,cash_account'],
            'project_id'        => ['nullable', 'required_if:account_type,project_linked', 'exists:projects,id'],
            'account_nick_name' => ['required', 'string', 'max:255', 'unique:bank_accounts,account_nick_name,' . $bankAccount->id],
            'account_name'      => ['required', 'string', 'max:255'],
            'account_number'    => ['nullable', 'string', 'max:100'],
            'bank_name'         => ['required', 'string', 'max:255'],
            'branch'            => ['nullable', 'string', 'max:255'],
            'ifsc_code'         => ['nullable', 'string', 'max:50'],
            'is_default'        => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default', false);

        if ($validated['account_type'] === 'general' || $validated['account_type'] === 'cash_account') {
            $validated['project_id'] = null;
        }

        $bankAccount->update($validated);

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank Account updated successfully!');
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        if (!$bankAccount->canBeDeleted()) {
            return back()->withErrors([
                'error' => "Bank Account '{$bankAccount->account_nick_name}' cannot be deleted because transactions have been recorded against it.",
            ]);
        }

        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank Account deleted successfully!');
    }
}
