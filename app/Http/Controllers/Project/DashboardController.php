<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        session(['active_project_id' => $project->id]);

        $projectBankAccounts = BankAccount::where('project_id', $project->id)->get();
        $generalBankAccounts = BankAccount::whereNull('project_id')->get();

        return view('project.dashboard', compact('project', 'projectBankAccounts', 'generalBankAccounts'));
    }
}
