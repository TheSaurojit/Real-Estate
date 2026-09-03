<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        // Check if user has any administrative permissions or is Super Admin
        $hasAdminAccess = $user->isSuperAdmin() ||
            $user->hasPermission('manage_companies') ||
            $user->hasPermission('manage_roles') ||
            $user->hasPermission('manage_autonumber') ||
            $user->hasPermission('view_projects') ||
            $user->hasPermission('view_bank_accounts') ||
            $user->hasPermission('view_users');

        if (!$hasAdminAccess) {
            if (!empty($user->assigned_project_ids)) {
                return redirect()->route('project.dashboard', $user->assigned_project_ids[0]);
            }
            abort(403, 'Unauthorized. Your role does not have permission to access the Admin Panel.');
        }

        $company = Company::first();
        $projectsCount = Project::count();
        $activeProjectsCount = Project::where('is_active', true)->count();
        $bankAccountsCount = BankAccount::count();
        $usersCount = User::where('role', '!=', 'super_admin')->whereDoesntHave('roleRelation', function ($q) {
            $q->where('slug', 'super_admin');
        })->count();

        $recentProjects = Project::latest()->take(5)->get();
        $bankAccounts = BankAccount::with('project')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'company',
            'projectsCount',
            'activeProjectsCount',
            'bankAccountsCount',
            'usersCount',
            'recentProjects',
            'bankAccounts'
        ));
    }
}
