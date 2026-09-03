<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // 1. Super Admin has unrestricted access to all projects
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 2. Resolve project from route parameter or session
        $routeProject = $request->route('project');
        $projectId = null;

        if ($routeProject instanceof Project) {
            $projectId = $routeProject->id;
        } elseif (is_numeric($routeProject)) {
            $projectId = (int)$routeProject;
        } elseif (session()->has('active_project_id')) {
            $projectId = (int)session('active_project_id');
        }

        // 3. Verify user has access to this project
        if ($projectId && !$user->hasAccessToProject($projectId)) {
            abort(403, 'Unauthorized. You do not have permission to access this project.');
        }

        return $next($request);
    }
}
