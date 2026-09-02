<?php

use App\Http\Controllers\Admin\AutoNumberController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Project\BankFinanceController;
use App\Http\Controllers\Project\BookingController;
use App\Http\Controllers\Project\CancellationRefundController;
use App\Http\Controllers\Project\DashboardController as ProjectDashboardController;
use App\Http\Controllers\Project\JobSheetController;
use App\Http\Controllers\Project\ProjectSwitcherController;
use App\Http\Controllers\Project\SaleAgreementController;
use App\Http\Controllers\Project\SaleDeedController;
use App\Http\Controllers\Project\TransactionController;
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

    // Project Workspace Routes (Scoped to selected project)
    Route::prefix('project/{project}')->name('project.')->group(function () {
        Route::get('/', [ProjectDashboardController::class, 'index'])->name('dashboard');

        // Bookings Management
        Route::resource('bookings', BookingController::class);
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        // Customization Job Sheets (Add-ons vs Dislodges)
        Route::post('bookings/{booking}/jobsheets', [JobSheetController::class, 'store'])->name('bookings.jobsheets.store');
        Route::delete('bookings/{booking}/jobsheets/{customization}', [JobSheetController::class, 'destroy'])->name('bookings.jobsheets.destroy');

        // Sale Agreement (Bayna-nama)
        Route::put('bookings/{booking}/sale-agreement', [SaleAgreementController::class, 'update'])->name('bookings.sale-agreement.update');

        // Bank Finance (Home Loan)
        Route::put('bookings/{booking}/bank-finance', [BankFinanceController::class, 'update'])->name('bookings.bank-finance.update');

        // Sale Deed
        Route::put('bookings/{booking}/sale-deed', [SaleDeedController::class, 'update'])->name('bookings.sale-deed.update');

        // Cross-Ledger Overpayment Rebalance Adjustment
        Route::get('bookings/{booking}/adjustment', [TransactionController::class, 'createAdjustment'])->name('bookings.adjustment.create');
        Route::post('bookings/{booking}/adjustment', [TransactionController::class, 'storeAdjustment'])->name('bookings.adjustment.store');

        // Cancellation Split Refunds
        Route::get('bookings/{booking}/cancellation-refund', [CancellationRefundController::class, 'show'])->name('bookings.cancellation-refund.show');
        Route::post('bookings/{booking}/cancellation-refund', [CancellationRefundController::class, 'store'])->name('bookings.cancellation-refund.store');

        // Financial Transactions & Dual-Ledger Management
        Route::resource('transactions', TransactionController::class);
        Route::put('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status.update');

        // Document Printing Hub & 12 Print-Ready Templates
        Route::get('documents', [\App\Http\Controllers\Project\DocumentController::class, 'index'])->name('documents.index');
        Route::get('bookings/{booking}/documents/{documentType}', [\App\Http\Controllers\Project\DocumentController::class, 'show'])->name('documents.show');

        // Site Expenses & Material Purchases
        Route::resource('expenses', \App\Http\Controllers\Project\ExpenseController::class);

        // Inter-Project Stock Transfers
        Route::resource('stock-transfers', \App\Http\Controllers\Project\StockTransferController::class);

        // Executive Reports & Profitability Analytics
        Route::get('reports', [\App\Http\Controllers\Project\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/profitability', [\App\Http\Controllers\Project\ReportController::class, 'profitability'])->name('reports.profitability');
        Route::get('reports/inventory', [\App\Http\Controllers\Project\ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/aging', [\App\Http\Controllers\Project\ReportController::class, 'aging'])->name('reports.aging');
        Route::get('reports/banking', [\App\Http\Controllers\Project\ReportController::class, 'banking'])->name('reports.banking');
    });
});
