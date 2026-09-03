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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    public function index(): View
    {
        $authUser = auth()->user();
        // Display staff users and admins, excluding the primary Super Admin system account
        $query = User::with(['company', 'roleRelation'])
            ->where('role', '!=', 'super_admin')
            ->whereDoesntHave('roleRelation', function ($q) {
                $q->where('slug', 'super_admin');
            });

        if (!$authUser->isSuperAdmin()) {
            $query->where('company_id', $authUser->company_id);
        }

        $users = $query->latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $authUser = auth()->user();
        $company = $authUser->isSuperAdmin() ? Company::first() : $authUser->company;
        $companies = $authUser->isSuperAdmin() ? Company::all() : Company::where('id', $authUser->company_id)->get();
        $projects = $authUser->isSuperAdmin()
            ? Project::where('is_active', true)->get()
            : Project::where('company_id', $authUser->company_id)->where('is_active', true)->get();

        // Only 1 Super Admin allowed in system; all other staff are Admin or custom roles
        $roles = Role::where('slug', '!=', 'super_admin')->get();

        return view('admin.users.create', compact('company', 'companies', 'projects', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = auth()->user();

        $validated = $request->validate([
            'company_id'             => ['required', 'exists:companies,id'],
            'role_id'                => ['required', 'exists:roles,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'designation'            => ['required', 'string', 'max:100'],
            'mobile'                 => ['required', 'string', 'max:50'],
            'email'                  => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'               => ['required', 'string', 'min:6', 'confirmed'],
            'assigned_project_ids'   => ['required', 'array', 'min:1'],
            'assigned_project_ids.*' => ['required', 'exists:projects,id'],
        ], [
            'assigned_project_ids.required' => 'Please assign the user to at least one project.',
            'assigned_project_ids.min'      => 'Please assign the user to at least one project.',
            'assigned_project_ids.*.exists' => 'The selected project is invalid.',
        ]);

        if (!$authUser->isSuperAdmin() && (int)$validated['company_id'] !== (int)$authUser->company_id) {
            abort(403, 'Unauthorized. You can only create staff users for your own company.');
        }

        $role = Role::findOrFail($validated['role_id']);

        // Strict rule: Only 1 Super Admin in system
        if ($role->slug === 'super_admin') {
            return back()->withInput()->withErrors(['role_id' => 'There can only be one Super Admin in the system. You can create multiple Admins or custom roles.']);
        }

        $companyId = (int)$validated['company_id'];
        
        // Filter assigned_project_ids to ensure they strictly belong to the chosen company
        $assignedProjectIds = Project::where('company_id', $companyId)
            ->whereIn('id', $validated['assigned_project_ids'])
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        if (empty($assignedProjectIds)) {
            return back()->withInput()->withErrors([
                'assigned_project_ids' => 'The user must be assigned to at least one project belonging to the selected company.'
            ]);
        }

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
            'assigned_project_ids' => $assignedProjectIds,
            'is_active'            => true,
        ]);

        return redirect()->route('admin.users.index')->with('created_user', [
            'code' => $user->user_code,
            'name' => $user->name,
        ])->with('success', "User created successfully!");
    }

    public function edit(User $user): View
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && $user->company_id !== $authUser->company_id) {
            abort(403, 'Unauthorized. You cannot view or edit staff belonging to another company.');
        }

        $companies = $authUser->isSuperAdmin() ? Company::all() : Company::where('id', $authUser->company_id)->get();
        $projects = $authUser->isSuperAdmin()
            ? Project::where('is_active', true)->get()
            : Project::where('company_id', $authUser->company_id)->where('is_active', true)->get();
        
        // If editing the Super Admin, show their role; otherwise only non-superadmin roles
        $roles = $user->isSuperAdmin()
            ? Role::all()
            : Role::where('slug', '!=', 'super_admin')->get();

        return view('admin.users.edit', compact('user', 'companies', 'projects', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && $user->company_id !== $authUser->company_id) {
            abort(403, 'Unauthorized. You cannot modify staff belonging to another company.');
        }

        $validated = $request->validate([
            'company_id'             => ['required', 'exists:companies,id'],
            'role_id'                => ['required', 'exists:roles,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'designation'            => ['required', 'string', 'max:100'],
            'mobile'                 => ['required', 'string', 'max:50'],
            'email'                  => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'               => ['nullable', 'string', 'min:6', 'confirmed'],
            'assigned_project_ids'   => ['required', 'array', 'min:1'],
            'assigned_project_ids.*' => ['required', 'exists:projects,id'],
            'is_active'              => ['boolean'],
        ], [
            'assigned_project_ids.required' => 'Please assign the user to at least one project.',
            'assigned_project_ids.min'      => 'Please assign the user to at least one project.',
            'assigned_project_ids.*.exists' => 'The selected project is invalid.',
        ]);

        if (!$authUser->isSuperAdmin() && (int)$validated['company_id'] !== (int)$authUser->company_id) {
            abort(403, 'Unauthorized. You cannot transfer a staff user to another company.');
        }

        $role = Role::findOrFail($validated['role_id']);

        // Prevent creating a second super admin or demoting the single super admin
        if ($role->slug === 'super_admin' && !$user->isSuperAdmin()) {
            return back()->withInput()->withErrors(['role_id' => 'There can only be one Super Admin in the system.']);
        }

        if ($user->isSuperAdmin()) {
            // Keep the primary super admin as super admin
            $superAdminRole = Role::where('slug', 'super_admin')->first();
            $role = $superAdminRole ?? $role;
        }

        $companyId = (int)$validated['company_id'];
        $assignedProjectIds = Project::where('company_id', $companyId)
            ->whereIn('id', $validated['assigned_project_ids'])
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        if (empty($assignedProjectIds)) {
            return back()->withInput()->withErrors([
                'assigned_project_ids' => 'The user must be assigned to at least one project belonging to the selected company.'
            ]);
        }

        $updateData = [
            'company_id'           => $companyId,
            'role_id'              => $role->id,
            'name'                 => $validated['name'],
            'designation'          => $validated['designation'],
            'mobile'               => $validated['mobile'],
            'email'                => $validated['email'],
            'role'                 => $role->slug,
            'assigned_project_ids' => $assignedProjectIds,
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
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && $user->company_id !== $authUser->company_id) {
            abort(403, 'Unauthorized. You cannot delete staff belonging to another company.');
        }

        if ($user->isSuperAdmin()) {
            return back()->withErrors(['error' => 'The primary Super Admin account cannot be deleted.']);
        }

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete your own logged-in user account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
