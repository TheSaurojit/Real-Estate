<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    public function index(): View
    {
        // Display staff users and admins, excluding the primary Super Admin system account
        $users = User::with(['company', 'roleRelation'])
            ->where('role', '!=', 'super_admin')
            ->whereDoesntHave('roleRelation', function ($q) {
                $q->where('slug', 'super_admin');
            })
            ->latest()
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $company = Company::first();
        $companies = Company::all();
        $projects = Project::where('is_active', true)->get();
        // Only 1 Super Admin allowed in system; all other staff are Admin or custom roles
        $roles = Role::where('slug', '!=', 'super_admin')->get();
        $nextUserCode = $this->autoNumberService->peekNextNumber('user', $company?->id);

        return view('admin.users.create', compact('company', 'companies', 'projects', 'roles', 'nextUserCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id'           => ['required', 'exists:companies,id'],
            'role_id'              => ['required', 'exists:roles,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'designation'          => ['required', 'string', 'max:100'],
            'mobile'               => ['required', 'string', 'max:50'],
            'email'                => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'             => ['required', 'string', 'min:6', 'confirmed'],
            'assigned_project_ids' => ['nullable', 'array'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        // Strict rule: Only 1 Super Admin in system
        if ($role->slug === 'super_admin') {
            return back()->withErrors(['role_id' => 'There can only be one Super Admin in the system. You can create multiple Admins or custom roles.']);
        }

        $companyId = (int)$validated['company_id'];
        $userCode = $this->autoNumberService->getNextNumber('user', $companyId, true);

        $user = User::create([
            'company_id'           => $companyId,
            'role_id'              => $role->id,
            'user_code'            => $userCode,
            'name'                 => $validated['name'],
            'designation'          => $validated['designation'],
            'mobile'               => $validated['mobile'],
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'role'                 => $role->slug,
            'assigned_project_ids' => $validated['assigned_project_ids'] ?? [],
            'is_active'            => true,
        ]);

        return redirect()->route('admin.users.index')->with('created_user', [
            'code' => $user->user_code,
            'name' => $user->name,
        ])->with('success', "User created successfully! User ID: {$user->user_code}");
    }

    public function edit(User $user): View
    {
        $companies = Company::all();
        $projects = Project::where('is_active', true)->get();
        
        // If editing the Super Admin, show their role; otherwise only non-superadmin roles
        $roles = $user->isSuperAdmin()
            ? Role::all()
            : Role::where('slug', '!=', 'super_admin')->get();

        return view('admin.users.edit', compact('user', 'companies', 'projects', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'company_id'           => ['required', 'exists:companies,id'],
            'role_id'              => ['required', 'exists:roles,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'designation'          => ['required', 'string', 'max:100'],
            'mobile'               => ['required', 'string', 'max:50'],
            'email'                => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'             => ['nullable', 'string', 'min:6', 'confirmed'],
            'assigned_project_ids' => ['nullable', 'array'],
            'is_active'            => ['boolean'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        // Prevent creating a second super admin or demoting the single super admin
        if ($role->slug === 'super_admin' && !$user->isSuperAdmin()) {
            return back()->withErrors(['role_id' => 'There can only be one Super Admin in the system.']);
        }

        if ($user->isSuperAdmin()) {
            // Keep the primary super admin as super admin
            $superAdminRole = Role::where('slug', 'super_admin')->first();
            $role = $superAdminRole ?? $role;
        }

        $updateData = [
            'company_id'           => $validated['company_id'],
            'role_id'              => $role->id,
            'name'                 => $validated['name'],
            'designation'          => $validated['designation'],
            'mobile'               => $validated['mobile'],
            'email'                => $validated['email'],
            'role'                 => $role->slug,
            'assigned_project_ids' => $validated['assigned_project_ids'] ?? [],
            'is_active'            => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->withErrors(['error' => 'The primary Super Admin account cannot be deleted.']);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own logged-in user account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
