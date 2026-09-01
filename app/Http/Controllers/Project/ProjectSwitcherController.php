<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectSwitcherController extends Controller
{
    /**
     * Switch the current active project or return to admin panel
     */
    public function switch(Request $request): RedirectResponse
    {
        $target = $request->input('target');

        if ($target === 'admin') {
            session()->forget('active_project_id');
            return redirect()->route('admin.dashboard');
        }

        if (is_numeric($target)) {
            $project = Project::findOrFail((int)$target);
            session(['active_project_id' => $project->id]);
            return redirect()->route('project.dashboard', $project->id);
        }

        return redirect()->route('admin.dashboard');
    }
}
