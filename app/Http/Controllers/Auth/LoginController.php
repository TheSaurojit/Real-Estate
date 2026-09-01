<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the application login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $credentials['login_id'];
        $password = $credentials['password'];

        // Determine if login is by email or user_code
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_code';

        $user = User::where($fieldType, $loginInput)->first();

        if (!$user || !Auth::validate([$fieldType => $loginInput, 'password' => $password])) {
            return back()->withErrors([
                'login_id' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('login_id'));
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'login_id' => 'This user account is currently deactivated.',
            ])->withInput($request->only('login_id'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // If user is non-admin and has assigned projects, open their first assigned project
        if (!$user->isSuperAdmin() && $user->role !== 'admin' && !empty($user->assigned_project_ids)) {
            $firstProjectId = $user->assigned_project_ids[0];
            $project = Project::find($firstProjectId);
            if ($project) {
                session(['active_project_id' => $project->id]);
                return redirect()->route('project.dashboard', $project->id);
            }
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
