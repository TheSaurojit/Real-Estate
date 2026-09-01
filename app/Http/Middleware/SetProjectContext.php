<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetProjectContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allProjects = Project::where('is_active', true)->orderBy('name')->get();
        $company = Company::first();

        // 1. Check if route has a {project} parameter (Model or ID)
        $routeProject = $request->route('project');
        $activeProjectId = null;

        if ($routeProject instanceof Project) {
            $activeProjectId = $routeProject->id;
        } elseif (is_numeric($routeProject)) {
            $activeProjectId = (int)$routeProject;
        } elseif (session()->has('active_project_id')) {
            $activeProjectId = session('active_project_id');
        }

        $currentProject = null;
        if ($activeProjectId) {
            $currentProject = $allProjects->firstWhere('id', $activeProjectId);
            if ($currentProject) {
                session(['active_project_id' => $currentProject->id]);
            }
        }

        // Share globally with all Blade views
        View::share('allProjects', $allProjects);
        View::share('currentProject', $currentProject);
        View::share('currentCompany', $company);

        return $next($request);
    }
}
