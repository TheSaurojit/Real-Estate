<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoNumberSequence;
use App\Models\Company;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Display a listing of all companies (Super Admin access all; staff access own company)
     */
    public function index(): View
    {
        $user = auth()->user();
        $query = Company::withCount(['projects', 'bankAccounts', 'users']);

        if (!$user->isSuperAdmin()) {
            $query->where('id', $user->company_id);
        }

        $companies = $query->latest()->get();
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company
     */
    public function create(): View
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Admin can create new companies.');
        }

        return view('admin.companies.create');
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Admin can create new companies.');
        }

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'company_code'      => ['required', 'string', 'max:20', 'unique:companies,company_code', 'regex:/^[a-zA-Z0-9]+$/'],
            'contact_primary'   => ['required', 'string', 'max:50'],
            'contact_secondary' => ['nullable', 'string', 'max:50'],
            'email'             => ['nullable', 'email', 'max:255'],
            'address'           => ['required', 'string'],
            'pan_number'        => ['nullable', 'string', 'max:20'],
            'gstin'             => ['nullable', 'string', 'max:30'],
            'logo'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        $validated['company_code'] = strtoupper($validated['company_code']);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('companies', 'public');
        }

        $company = Company::create($validated);

        // Initialize default auto-number sequences for this new company
        $defaultTypes = [
            'bank_account' => ['prefix' => 'BANK-00', 'next_number' => 1, 'padding' => 1, 'description' => 'Bank Accounts (e.g. BANK-001)'],
            'user'         => ['prefix' => $company->company_code . '/USR-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Staff Users (e.g. ' . $company->company_code . '/USR-1001)'],
            'project'      => ['prefix' => $company->company_code . '/PRJ-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Projects (e.g. ' . $company->company_code . '/PRJ-1001)'],
            'booking'      => ['prefix' => $company->company_code . '/BK-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Bookings (e.g. ' . $company->company_code . '/BK-1001)'],
            'receipt'      => ['prefix' => $company->company_code . '/RCD', 'next_number' => 1001, 'padding' => 0, 'description' => 'Money Receipts (e.g. ' . $company->company_code . '/RCD1001)'],
            'payment'      => ['prefix' => $company->company_code . '/PMT', 'next_number' => 1001, 'padding' => 0, 'description' => 'Payment Vouchers (e.g. ' . $company->company_code . '/PMT1001)'],
        ];

        foreach ($defaultTypes as $type => $config) {
            $this->autoNumberService->configureSequence(
                $type,
                $config['prefix'],
                $config['next_number'],
                $config['padding'],
                $company->id,
                $config['description']
            );
        }

        return redirect()->route('admin.companies.index')->with('success', "Company '{$company->name}' created successfully with Auto-Numbering initialized!");
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company): View
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $company->id !== $user->company_id) {
            abort(403, 'Unauthorized. You can only view and modify your own company.');
        }

        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $company->id !== $user->company_id) {
            abort(403, 'Unauthorized. You can only modify your own company.');
        }

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'company_code'      => ['required', 'string', 'max:20', 'unique:companies,company_code,' . $company->id, 'regex:/^[a-zA-Z0-9]+$/'],
            'contact_primary'   => ['required', 'string', 'max:50'],
            'contact_secondary' => ['nullable', 'string', 'max:50'],
            'email'             => ['nullable', 'email', 'max:255'],
            'address'           => ['required', 'string'],
            'pan_number'        => ['nullable', 'string', 'max:20'],
            'gstin'             => ['nullable', 'string', 'max:30'],
            'logo'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        $validated['company_code'] = strtoupper($validated['company_code']);

        if ($request->hasFile('logo')) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('companies', 'public');
        }

        $company->update($validated);

        return redirect()->route('admin.companies.index')->with('success', "Company '{$company->name}' updated successfully!");
    }

    /**
     * Delete a company (with safeguards - Super Admin only)
     */
    public function destroy(Company $company): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Admin can delete developer companies.');
        }

        if ($company->projects()->count() > 0 || $company->bankAccounts()->count() > 0) {
            return back()->withErrors([
                'error' => "Company '{$company->name}' cannot be deleted because it is linked to active projects or bank accounts.",
            ]);
        }

        if (Company::count() <= 1) {
            return back()->withErrors([
                'error' => 'Cannot delete the only remaining company in the system.',
            ]);
        }

        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Company deleted successfully!');
    }
}
