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
    public function index(): View
    {
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
