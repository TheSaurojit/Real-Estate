<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $query = Project::with('company')->withCount('bankAccounts');

        if (!$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        $projects = $query->latest()->get();
        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $company = $user->isSuperAdmin() ? Company::first() : $user->company;
        $companies = $user->isSuperAdmin() ? Company::all() : Company::where('id', $user->company_id)->get();
        $nextProjectCode = $this->autoNumberService->peekNextNumber('project', $company?->id);

        return view('admin.projects.create', compact('company', 'companies', 'nextProjectCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'company_id'    => ['required', 'exists:companies,id'],
            'name'          => ['required', 'string', 'max:255'],
            'nick_name'     => ['required', 'string', 'max:6', 'regex:/^[a-zA-Z0-9]+$/'],
            'daag_no'       => ['nullable', 'string', 'max:100'],
            'patta_no'      => ['nullable', 'string', 'max:100'],
            'holding_no'    => ['nullable', 'string', 'max:100'],
            'mouza'         => ['nullable', 'string', 'max:150'],
            'pogonah'       => ['nullable', 'string', 'max:150'],
            'full_address'  => ['required', 'string'],
            'rera_category' => ['required', 'in:unregistered,exempted,registered'],
            'rera_reg_no'   => ['nullable', 'string', 'max:100', 'required_if:rera_category,registered'],
        ]);

        if (!$user->isSuperAdmin() && (int)$validated['company_id'] !== (int)$user->company_id) {
            abort(403, 'Unauthorized. You can only create projects for your own company.');
        }

        $companyId = (int)$validated['company_id'];
        $projectCode = $this->autoNumberService->getNextNumber('project', $companyId, true);

        $validated['project_code'] = $projectCode;
        $validated['nick_name'] = strtoupper($validated['nick_name']);

        if ($validated['rera_category'] === 'unregistered') {
            $validated['rera_reg_no'] = 'Unregistered';
        } elseif ($validated['rera_category'] === 'exempted') {
            $validated['rera_reg_no'] = 'Exempted';
        }

        $project = Project::create($validated);

        return redirect()->route('admin.projects.index')->with('created_project', [
            'code' => $project->project_code,
            'name' => $project->name,
        ])->with('success', "Project created successfully! Project ID: {$project->project_code}");
    }

    public function show(Project $project): View
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $project->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot view projects belonging to another company.');
        }

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $project->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot view or modify projects belonging to another company.');
        }

        $companies = $user->isSuperAdmin() ? Company::all() : Company::where('id', $user->company_id)->get();
        return view('admin.projects.edit', compact('project', 'companies'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $project->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot modify projects belonging to another company.');
        }

        $validated = $request->validate([
            'company_id'    => ['required', 'exists:companies,id'],
            'name'          => ['required', 'string', 'max:255'],
            'nick_name'     => ['required', 'string', 'max:6', 'regex:/^[a-zA-Z0-9]+$/'],
            'daag_no'       => ['nullable', 'string', 'max:100'],
            'patta_no'      => ['nullable', 'string', 'max:100'],
            'holding_no'    => ['nullable', 'string', 'max:100'],
            'mouza'         => ['nullable', 'string', 'max:150'],
            'pogonah'       => ['nullable', 'string', 'max:150'],
            'full_address'  => ['required', 'string'],
            'rera_category' => ['required', 'in:unregistered,exempted,registered'],
            'rera_reg_no'   => ['nullable', 'string', 'max:100', 'required_if:rera_category,registered'],
            'is_active'     => ['boolean'],
        ]);

        if (!$user->isSuperAdmin() && (int)$validated['company_id'] !== (int)$user->company_id) {
            abort(403, 'Unauthorized. You cannot transfer a project to another company.');
        }

        $validated['nick_name'] = strtoupper($validated['nick_name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['rera_category'] === 'unregistered') {
            $validated['rera_reg_no'] = 'Unregistered';
        } elseif ($validated['rera_category'] === 'exempted') {
            $validated['rera_reg_no'] = 'Exempted';
        }

        $project->update($validated);

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $project->company_id !== $user->company_id) {
            abort(403, 'Unauthorized. You cannot delete projects belonging to another company.');
        }

        if (!$project->canBeDeleted()) {
            return back()->withErrors([
                'error' => "Project '{$project->name}' cannot be deleted because it is linked to active bank accounts or records.",
            ]);
        }

        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully!');
    }
}
