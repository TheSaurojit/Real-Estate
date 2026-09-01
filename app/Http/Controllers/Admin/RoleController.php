<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of all roles
     */
    public function index(): View
    {
        // Display operational staff and admin roles (Super Admin has permanent full access by design)
        $roles = Role::withCount(['permissions', 'users'])
            ->where('slug', '!=', 'super_admin')
            ->orderBy('id')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new custom role
     */
    public function create(): View
    {
        // Group permissions by their respective functional module
        $permissionModules = Permission::all()->groupBy('module');
        return view('admin.roles.create', compact('permissionModules'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        // Check unique slug
        if (Role::where('slug', $slug)->exists()) {
            $slug .= '_' . rand(10, 99);
        }

        $role = Role::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'],
            'is_system'   => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' created and permissions assigned successfully!");
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role): View|RedirectResponse
    {
        if ($role->slug === 'super_admin') {
            return redirect()->route('admin.roles.index')->withErrors([
                'error' => 'The Super Admin role has permanent full system privileges and cannot be modified.',
            ]);
        }

        $permissionModules = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissionModules', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->slug === 'super_admin') {
            return redirect()->route('admin.roles.index')->withErrors([
                'error' => 'The Super Admin role cannot be modified.',
            ]);
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name'        => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Sync permissions
        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' updated successfully!");
    }

    /**
     * Remove the specified role from storage
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['error' => "System role '{$role->name}' cannot be deleted."]);
        }

        if (!$role->canBeDeleted()) {
            return back()->withErrors(['error' => "Role '{$role->name}' cannot be deleted because it is assigned to {$role->users()->count()} staff user(s). Reassign them first."]);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' deleted successfully!");
    }
}
