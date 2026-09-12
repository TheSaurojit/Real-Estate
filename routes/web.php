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
use App\Http\Controllers\Project\DocumentController;
use App\Http\Controllers\Project\ExpenseController;
use App\Http\Controllers\Project\JobSheetController;
use App\Http\Controllers\Project\ProjectSwitcherController;
use App\Http\Controllers\Project\ReportController;
use App\Http\Controllers\Project\SaleAgreementController;
use App\Http\Controllers\Project\SaleDeedController;
use App\Http\Controllers\Project\StockTransferController;
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

        // Company Master (Full Multi-Company CRUD by Super Admin or user with manage_companies)
        Route::resource('companies', CompanyController::class)->middleware('permission:manage_companies');

        // Dynamic Roles & Permissions Master
        Route::resource('roles', RoleController::class)->middleware('permission:manage_roles');

        // Auto Number Settings
        Route::get('/autonumber', [AutoNumberController::class, 'index'])->name('autonumber.index')->middleware('permission:manage_autonumber');
        Route::put('/autonumber', [AutoNumberController::class, 'update'])->name('autonumber.update')->middleware('permission:manage_autonumber');

        // Projects Master (Granular Permissions)
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index')->middleware('permission:view_projects');
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create')->middleware('permission:create_projects');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store')->middleware('permission:create_projects');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show')->middleware('permission:view_projects');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit')->middleware('permission:edit_projects');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update')->middleware('permission:edit_projects');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy')->middleware('permission:delete_projects');

        // Bank Accounts Master (Granular Permissions)
        Route::get('bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index')->middleware('permission:view_bank_accounts');
        Route::get('bank-accounts/create', [BankAccountController::class, 'create'])->name('bank-accounts.create')->middleware('permission:create_bank_accounts');
        Route::post('bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store')->middleware('permission:create_bank_accounts');
        Route::get('bank-accounts/{bank_account}/edit', [BankAccountController::class, 'edit'])->name('bank-accounts.edit')->middleware('permission:edit_bank_accounts');
        Route::put('bank-accounts/{bank_account}', [BankAccountController::class, 'update'])->name('bank-accounts.update')->middleware('permission:edit_bank_accounts');
        Route::delete('bank-accounts/{bank_account}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy')->middleware('permission:delete_bank_accounts');

        // Users Master (Granular Permissions)
        Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view_users');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create_users');
        Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create_users');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit_users');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit_users');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete_users');
    });

    // Project Workspace Routes (Scoped to selected project & verified by project.access)
    Route::prefix('project/{project}')->name('project.')->middleware(['project.access'])->group(function () {
        Route::get('/', [ProjectDashboardController::class, 'index'])->name('dashboard');

        // Bookings Management (Granular Permissions)
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index')->middleware('permission:view_bookings');
        Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create')->middleware('permission:create_bookings');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store')->middleware('permission:create_bookings');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show')->middleware('permission:view_bookings');
        Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit')->middleware('permission:edit_bookings');
        Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update')->middleware('permission:edit_bookings');
        Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy')->middleware('permission:delete_bookings');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel')->middleware('permission:cancel_bookings');

        // Customization Job Sheets (Add-ons vs Dislodges)
        Route::post('bookings/{booking}/jobsheets', [JobSheetController::class, 'store'])->name('bookings.jobsheets.store')->middleware('permission:manage_jobsheets');
        Route::put('bookings/{booking}/jobsheets/{customization}', [JobSheetController::class, 'update'])->name('bookings.jobsheets.update')->middleware('permission:manage_jobsheets');
        Route::delete('bookings/{booking}/jobsheets/{customization}', [JobSheetController::class, 'destroy'])->name('bookings.jobsheets.destroy')->middleware('permission:manage_jobsheets');
        Route::post('bookings/{booking}/adjustments', [JobSheetController::class, 'updateAdjustments'])->name('bookings.adjustments.update')->middleware('permission:manage_jobsheets');

        // Sale Agreement (Bayna-nama)
        Route::put('bookings/{booking}/sale-agreement', [SaleAgreementController::class, 'update'])->name('bookings.sale-agreement.update')->middleware('permission:manage_sale_agreements');

        // Bank Finance (Home Loan)
        Route::put('bookings/{booking}/bank-finance', [BankFinanceController::class, 'update'])->name('bookings.bank-finance.update')->middleware('permission:manage_sale_agreements');

        // Sale Deed
        Route::put('bookings/{booking}/sale-deed', [SaleDeedController::class, 'update'])->name('bookings.sale-deed.update')->middleware('permission:manage_sale_deeds');

        // Cross-Ledger Overpayment Rebalance Adjustment
        Route::get('bookings/{booking}/adjustment', [TransactionController::class, 'createAdjustment'])->name('bookings.adjustment.create')->middleware('permission:manage_adjustments');
        Route::post('bookings/{booking}/adjustment', [TransactionController::class, 'storeAdjustment'])->name('bookings.adjustment.store')->middleware('permission:manage_adjustments');

        // Stage 2.1.6 Booking Cancellation & 3-Account Reconciliation
        Route::get('cancellations', [CancellationRefundController::class, 'show'])->name('cancellations.index')->middleware('permission:cancel_bookings');
        Route::get('bookings/{booking}/cancellation', [CancellationRefundController::class, 'show'])->name('bookings.cancellation.show')->middleware('permission:cancel_bookings');
        Route::get('bookings/{booking}/cancellation-refund', [CancellationRefundController::class, 'show'])->name('bookings.cancellation-refund.show')->middleware('permission:create_refunds');
        Route::post('bookings/{booking}/cancellation/status', [CancellationRefundController::class, 'updateStatus'])->name('bookings.cancellation.status')->middleware('permission:cancel_bookings');
        Route::post('bookings/{booking}/cancellation/instructions', [CancellationRefundController::class, 'updateInstructions'])->name('bookings.cancellation.instructions')->middleware('permission:cancel_bookings');
        Route::post('bookings/{booking}/cancellation-refund', [CancellationRefundController::class, 'store'])->name('bookings.cancellation-refund.store')->middleware('permission:create_refunds');
        Route::post('bookings/{booking}/cancellation/payout', [CancellationRefundController::class, 'store'])->name('bookings.cancellation.payout')->middleware('permission:create_refunds');

        // Financial Transactions & Dual-Ledger Management
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index')->middleware('permission:view_transactions');
        Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create')->middleware('permission:create_receipts');
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store')->middleware('permission:create_receipts');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show')->middleware('permission:view_transactions');
        Route::get('transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit')->middleware('permission:create_receipts');
        Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update')->middleware('permission:create_receipts');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy')->middleware('permission:create_receipts');
        Route::put('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status.update')->middleware('permission:manage_cheques');

        // Document Printing Hub & 12 Print-Ready Templates
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index')->middleware('permission:print_documents');
        Route::get('documents/{documentType}', [DocumentController::class, 'showBlankOrSpecimen'])->name('documents.blank')->middleware('permission:print_documents');
        Route::get('bookings/documents/{documentType}', [DocumentController::class, 'showBlankOrSpecimen']);
        Route::get('bookings/{booking}/documents/{documentType}', [DocumentController::class, 'show'])->name('documents.show')->middleware('permission:print_documents');

        // Site Expenses & Material Purchases
        Route::resource('expenses', ExpenseController::class)->middleware('permission:manage_expenses');

        // Inter-Project Stock Transfers
        Route::resource('stock-transfers', StockTransferController::class)->middleware('permission:manage_expenses');

        // Section 2.3 Comprehensive Reporting Modules & Excel Exports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:view_booking_reports,view_financial_reports');
        Route::get('reports/bookings-general', [ReportController::class, 'bookingsGeneral'])->name('reports.bookings.general')->middleware('permission:view_booking_reports');
        Route::get('reports/bookings-financial', [ReportController::class, 'bookingsFinancial'])->name('reports.bookings.financial')->middleware('permission:view_financial_reports');
        Route::get('reports/receipts', [ReportController::class, 'receipts'])->name('reports.receipts')->middleware('permission:view_financial_reports');
        Route::get('reports/refunds', [ReportController::class, 'refunds'])->name('reports.refunds')->middleware('permission:view_financial_reports');
        Route::get('reports/customizations', [ReportController::class, 'customizations'])->name('reports.customizations')->middleware('permission:view_booking_reports');
        Route::get('reports/quick-summary', [ReportController::class, 'quickSummary'])->name('reports.quick-summary')->middleware('permission:view_booking_reports,view_financial_reports');

        // Executive Analytics Statements
        Route::get('reports/profitability', [ReportController::class, 'profitability'])->name('reports.profitability')->middleware('permission:view_financial_reports');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory')->middleware('permission:view_booking_reports');
        Route::get('reports/aging', [ReportController::class, 'aging'])->name('reports.aging')->middleware('permission:view_financial_reports');
        Route::get('reports/banking', [ReportController::class, 'banking'])->name('reports.banking')->middleware('permission:view_financial_reports');
    });
});
