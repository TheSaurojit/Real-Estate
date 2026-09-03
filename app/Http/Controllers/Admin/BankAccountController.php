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
        $user = auth()->user();
        $query = BankAccount::with(['company', 'project']);

        if (!$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        $bankAccounts = $query->latest()->get();
        return view('admin.bank_accounts.index', compact('bankAccounts'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $company = $user->isSuperAdmin() ? Company::first() : $user->company;
        $companies = $user->isSuperAdmin() ? Company::all() : Company::where('id', $user->company_id)->get();
        $projects = $user->isSuperAdmin()
            ? Project::where('is_active', true)->get()
            : Project::where('company_id', $user->company_id)->where('is_active', true)->get();

        $nextAccountCode = $this->autoNumberService->peekNextNumber('bank_account', $company?->id);

        return view('admin.bank_accounts.create', compact('company', 'companies', 'projects', 'nextAccountCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

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

        if (!$user->isSuperAdmin() && (int)$validated['company_id'] !== (int)$user->company_id) {
            abort(403, 'Unauthorized. You can only create bank accounts for your own company.');
        }

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
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $bankAccount->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot view or modify bank accounts belonging to another company.');
        }

        $companies = $user->isSuperAdmin() ? Company::all() : Company::where('id', $user->company_id)->get();
        $projects = $user->isSuperAdmin()
            ? Project::where('is_active', true)->get()
            : Project::where('company_id', $user->company_id)->where('is_active', true)->get();

        return view('admin.bank_accounts.edit', compact('bankAccount', 'companies', 'projects'));
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $bankAccount->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot modify bank accounts belonging to another company.');
        }

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

        if (!$user->isSuperAdmin() && (int)$validated['company_id'] !== (int)$user->company_id) {
            abort(403, 'Unauthorized. You cannot transfer a bank account to another company.');
        }

        $validated['is_default'] = $request->boolean('is_default', false);

        if ($validated['account_type'] === 'general' || $validated['account_type'] === 'cash_account') {
            $validated['project_id'] = null;
        }

        $bankAccount->update($validated);

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank Account updated successfully!');
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $bankAccount->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot delete bank accounts belonging to another company.');
        }

        if (!$bankAccount->canBeDeleted()) {
            return back()->withErrors([
                'error' => "Bank Account '{$bankAccount->account_nick_name}' cannot be deleted because transactions have been recorded against it.",
            ]);
        }

        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank Account deleted successfully!');
    }
}
