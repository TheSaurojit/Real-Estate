<?php

use App\Http\Controllers\Admin\AutoNumberController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Project\DashboardController as ProjectDashboardController;
use App\Http\Controllers\Project\ProjectSwitcherController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        if (session()->has('active_project_id')) {
            return redirect()->route('project.dashboard', session('active_project_id'));
        }
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Project context switch
    Route::post('/switch-context', [ProjectSwitcherController::class, 'switch'])->name('switch.context');

    // Admin Panel Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Company Master (Full Multi-Company CRUD by Super Admin)
        Route::resource('companies', CompanyController::class);

        // Dynamic Roles & Permissions Master
        Route::resource('roles', RoleController::class);

        // Auto Number Settings
        Route::get('/autonumber', [AutoNumberController::class, 'index'])->name('autonumber.index');
        Route::put('/autonumber', [AutoNumberController::class, 'update'])->name('autonumber.update');

        // Projects Master
        Route::resource('projects', ProjectController::class);

        // Bank Accounts Master
        Route::resource('bank-accounts', BankAccountController::class);

        // Users Master
        Route::resource('users', UserController::class);
    });

    // Project Workspace Routes
    Route::prefix('project/{project}')->name('project.')->group(function () {
        Route::get('/', [ProjectDashboardController::class, 'index'])->name('dashboard');
    });
});
