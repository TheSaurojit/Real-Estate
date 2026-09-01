<?php

namespace Database\Seeders;

use App\Models\AutoNumberSequence;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Comprehensive Permissions by Module
        $permissions = [
            // Company & Global Settings
            ['name' => 'Manage Companies', 'slug' => 'manage_companies', 'module' => 'Company Settings', 'description' => 'Super Admin: Create, edit, delete developer companies'],
            ['name' => 'Manage Auto-Numbering', 'slug' => 'manage_autonumber', 'module' => 'Company Settings', 'description' => 'Configure auto-numbering prefixes and sequences'],

            // Projects Master
            ['name' => 'View Projects', 'slug' => 'view_projects', 'module' => 'Projects Master', 'description' => 'View project details and listings'],
            ['name' => 'Create Projects', 'slug' => 'create_projects', 'module' => 'Projects Master', 'description' => 'Create new real estate projects'],
            ['name' => 'Edit Projects', 'slug' => 'edit_projects', 'module' => 'Projects Master', 'description' => 'Update project details and RERA information'],
            ['name' => 'Delete Projects', 'slug' => 'delete_projects', 'module' => 'Projects Master', 'description' => 'Delete projects without active records'],

            // Bank Accounts
            ['name' => 'View Bank Accounts', 'slug' => 'view_bank_accounts', 'module' => 'Bank Accounts', 'description' => 'View company and project escrow accounts'],
            ['name' => 'Create Bank Accounts', 'slug' => 'create_bank_accounts', 'module' => 'Bank Accounts', 'description' => 'Add new bank and cash accounts'],
            ['name' => 'Edit Bank Accounts', 'slug' => 'edit_bank_accounts', 'module' => 'Bank Accounts', 'description' => 'Update bank account details'],
            ['name' => 'Delete Bank Accounts', 'slug' => 'delete_bank_accounts', 'module' => 'Bank Accounts', 'description' => 'Delete bank accounts without transactions'],

            // Users & Roles
            ['name' => 'View Users', 'slug' => 'view_users', 'module' => 'User & Roles', 'description' => 'View staff user accounts'],
            ['name' => 'Create Users', 'slug' => 'create_users', 'module' => 'User & Roles', 'description' => 'Create new staff accounts'],
            ['name' => 'Edit Users', 'slug' => 'edit_users', 'module' => 'User & Roles', 'description' => 'Update user particulars and passwords'],
            ['name' => 'Delete Users', 'slug' => 'delete_users', 'module' => 'User & Roles', 'description' => 'Delete user accounts'],
            ['name' => 'Manage Roles & Permissions', 'slug' => 'manage_roles', 'module' => 'User & Roles', 'description' => 'Create and configure dynamic roles and assign permissions'],

            // Bookings & Units
            ['name' => 'View Bookings', 'slug' => 'view_bookings', 'module' => 'Bookings', 'description' => 'View flat/unit booking records'],
            ['name' => 'Create Bookings', 'slug' => 'create_bookings', 'module' => 'Bookings', 'description' => 'Create new flat/unit bookings on the fly'],
            ['name' => 'Edit Bookings', 'slug' => 'edit_bookings', 'module' => 'Bookings', 'description' => 'Modify booking particulars and pricing'],
            ['name' => 'Delete Bookings', 'slug' => 'delete_bookings', 'module' => 'Bookings', 'description' => 'Delete booking records'],
            ['name' => 'Cancel Bookings', 'slug' => 'cancel_bookings', 'module' => 'Bookings', 'description' => 'Process booking cancellation and calculate refunds'],

            // Customization & Job Sheets
            ['name' => 'Manage Customization Job Sheets', 'slug' => 'manage_jobsheets', 'module' => 'Customization', 'description' => 'Add and manage add-ons and dislodge/deduction items'],

            // Sale Agreement & Deeds
            ['name' => 'Manage Sale Agreements', 'slug' => 'manage_sale_agreements', 'module' => 'Sale Agreement', 'description' => 'Execute and manage Bayna-nama and Bank finance loans'],
            ['name' => 'Manage Sale Deeds', 'slug' => 'manage_sale_deeds', 'module' => 'Sale Agreement', 'description' => 'Record and manage property registration sale deeds'],

            // Transactions
            ['name' => 'View Transactions', 'slug' => 'view_transactions', 'module' => 'Transactions', 'description' => 'View money receipts and vouchers'],
            ['name' => 'Create Receipts', 'slug' => 'create_receipts', 'module' => 'Transactions', 'description' => 'Create Money Receipts (Bank) and Receipt Vouchers (Cash)'],
            ['name' => 'Create Refunds', 'slug' => 'create_refunds', 'module' => 'Transactions', 'description' => 'Issue payment refund vouchers'],
            ['name' => 'Manage Adjustments', 'slug' => 'manage_adjustments', 'module' => 'Transactions', 'description' => 'Process cross-ledger bank and cash rebalancing'],
            ['name' => 'Manage Cheques', 'slug' => 'manage_cheques', 'module' => 'Transactions', 'description' => 'Update clearance status and apply bounce penalties'],

            // Reports & Print
            ['name' => 'View Booking Reports', 'slug' => 'view_booking_reports', 'module' => 'Reports', 'description' => 'Access general and financial booking reports'],
            ['name' => 'View Financial Reports', 'slug' => 'view_financial_reports', 'module' => 'Reports', 'description' => 'Access daily/monthly bank vs cash collection reports'],
            ['name' => 'Print Documents & Vouchers', 'slug' => 'print_documents', 'module' => 'Print Documents', 'description' => 'Generate and print letters, money receipts, vouchers, and NOCs'],

            // Construction Expenses
            ['name' => 'Manage Site Expenses', 'slug' => 'manage_expenses', 'module' => 'Construction Expense', 'description' => 'Track material purchases, contractor bills, and stock transfers'],
        ];

        $permissionModels = [];
        foreach ($permissions as $pData) {
            $permissionModels[$pData['slug']] = Permission::updateOrCreate(['slug' => $pData['slug']], $pData);
        }

        // 2. Seed Default Roles
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full administrative control over all companies, master settings, roles, and projects',
                'is_system' => true,
            ]
        );
        $superAdminRole->permissions()->sync(array_column($permissionModels, 'id'));

        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Full operations access to manage projects, bank accounts, staff, and bookings',
                'is_system' => true,
            ]
        );
        $adminPermissions = Permission::where('slug', '!=', 'manage_companies')->pluck('id')->toArray();
        $adminRole->permissions()->sync($adminPermissions);

        $managerRole = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Project Manager',
                'description' => 'Manages flat bookings, customization job sheets, sale agreements, and letters',
                'is_system' => false,
            ]
        );
        $managerPermissions = Permission::whereIn('slug', [
            'view_projects', 'view_bank_accounts', 'view_bookings', 'create_bookings', 'edit_bookings',
            'cancel_bookings', 'manage_jobsheets', 'manage_sale_agreements', 'manage_sale_deeds',
            'view_transactions', 'create_receipts', 'view_booking_reports', 'print_documents'
        ])->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermissions);

        $accountantRole = Role::updateOrCreate(
            ['slug' => 'accountant'],
            [
                'name' => 'Accountant',
                'description' => 'Handles bank receipts, cash vouchers, refunds, payment adjustments, and ledger reports',
                'is_system' => false,
            ]
        );
        $accountantPermissions = Permission::whereIn('slug', [
            'view_projects', 'view_bank_accounts', 'view_bookings', 'view_transactions',
            'create_receipts', 'create_refunds', 'manage_adjustments', 'manage_dishonored_cheques',
            'view_booking_reports', 'view_financial_reports', 'print_documents'
        ])->pluck('id')->toArray();
        $accountantRole->permissions()->sync($accountantPermissions);

        $siteEngineerRole = Role::updateOrCreate(
            ['slug' => 'site_engineer'],
            [
                'name' => 'Site Engineer',
                'description' => 'Tracks construction purchases, labor expenses, and site-to-site stock transfers',
                'is_system' => false,
            ]
        );
        $siteEngineerPermissions = Permission::whereIn('slug', [
            'view_projects', 'manage_expenses', 'print_documents'
        ])->pluck('id')->toArray();
        $siteEngineerRole->permissions()->sync($siteEngineerPermissions);

        // 3. Create Default Developer Company
        $company = Company::firstOrCreate(
            ['company_code' => 'SSI'],
            [
                'name'              => 'S & S Infrastructure',
                'contact_primary'   => '+91-9382445935',
                'contact_secondary' => '+91-6000583292',
                'email'             => 's4subhasish@gmail.com',
                'address'           => "S & S Infrastructure\nSilchar Road, Sribhumi (Karimganj),\nAssam, Pin - 788713",
                'pan_number'        => 'AKAPD3409L',
                'gstin'             => '18AKAPD3409L2ZK',
            ]
        );

        // 4. Configure Auto-Number Sequences
        $sequences = [
            ['company_id' => $company->id, 'entity_type' => 'bank_account', 'prefix' => 'BANK-00', 'next_number' => 1, 'padding' => 1, 'description' => 'Bank Accounts (e.g. BANK-001)'],
            ['company_id' => $company->id, 'entity_type' => 'user', 'prefix' => 'User-', 'next_number' => 1001, 'padding' => 0, 'description' => 'User / Staff (e.g. User-1001)'],
            ['company_id' => $company->id, 'entity_type' => 'project', 'prefix' => 'SSI/PRJ-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Projects (e.g. SSI/PRJ-1001)'],
            ['company_id' => $company->id, 'entity_type' => 'booking', 'prefix' => 'SSI/BK-', 'next_number' => 1001, 'padding' => 0, 'description' => 'Customer Bookings (e.g. SSI/BK-1001)'],
            ['company_id' => $company->id, 'entity_type' => 'receipt', 'prefix' => 'SSI/RCD', 'next_number' => 1001, 'padding' => 0, 'description' => 'Money Receipts / Receipt Vouchers (e.g. SSI/RCD1001)'],
            ['company_id' => $company->id, 'entity_type' => 'payment', 'prefix' => 'SSI/PMT', 'next_number' => 1001, 'padding' => 0, 'description' => 'Payment / Refund Vouchers (e.g. SSI/PMT1001)'],
        ];

        foreach ($sequences as $seq) {
            AutoNumberSequence::updateOrCreate(
                ['company_id' => $seq['company_id'], 'entity_type' => $seq['entity_type']],
                $seq
            );
        }

        // 5. Create Sample Projects
        $project1 = Project::updateOrCreate(
            ['project_code' => 'SSI/PRJ1001'],
            [
                'company_id'    => $company->id,
                'name'          => 'Ramkrishna Apartment',
                'nick_name'     => 'RA',
                'daag_no'       => '156',
                'patta_no'      => '258 [2nd RS]',
                'holding_no'    => '129',
                'mouza'         => 'Ambicapur Part-X',
                'pogonah'       => 'Barakpar',
                'full_address'  => 'Krishna Charan Road (West), Rangirkhari, Silchar, Cachar, Assam, Pin - 788005',
                'rera_category' => 'registered',
                'rera_reg_no'   => 'RERA 1458 CA of 2025',
                'is_active'     => true,
            ]
        );

        $project2 = Project::updateOrCreate(
            ['project_code' => 'SSI/PRJ-1002'],
            [
                'company_id'    => $company->id,
                'name'          => 'Imperial Grand',
                'nick_name'     => 'IG',
                'daag_no'       => '287',
                'patta_no'      => '385 [2nd RS]',
                'holding_no'    => 'Unknown',
                'mouza'         => 'Ambicapur PT. x',
                'pogonah'       => 'Barakpar',
                'full_address'  => 'House no. 7, Dag no. 287, Patta no. 385 [2nd RS], Mouza : Ambicapur PT. x, Sahid Tarani Road, Silchar, Dist. Cachar, Assam, Pin - 788005',
                'rera_category' => 'registered',
                'rera_reg_no'   => 'RERA 1459 CA of 2025',
                'is_active'     => true,
            ]
        );

        // 6. Create Bank Accounts
        BankAccount::updateOrCreate(
            ['account_code' => 'BANK-001'],
            [
                'company_id'        => $company->id,
                'project_id'        => null,
                'account_type'      => 'general',
                'account_nick_name' => 'S & S - Indian Bank',
                'account_name'      => 'S & S Infrastructure',
                'account_number'    => '5467755222',
                'bank_name'         => 'Indian Bank',
                'branch'            => 'Karimganj',
                'ifsc_code'         => 'IDIB000K601',
                'is_default'        => true,
            ]
        );

        BankAccount::updateOrCreate(
            ['account_code' => 'BANK-002'],
            [
                'company_id'        => $company->id,
                'project_id'        => $project2->id,
                'account_type'      => 'project_linked',
                'account_nick_name' => 'Imperial Grand - HDFC Rera Account',
                'account_name'      => 'S & S Infrastructure',
                'account_number'    => '123456789',
                'bank_name'         => 'HDFC Bank Limited',
                'branch'            => 'Karimganj',
                'ifsc_code'         => 'HDFC0004589',
                'is_default'        => false,
            ]
        );

        BankAccount::updateOrCreate(
            ['account_code' => 'CASH-001'],
            [
                'company_id'        => $company->id,
                'project_id'        => null,
                'account_type'      => 'cash_account',
                'account_nick_name' => 'Cash Account',
                'account_name'      => 'Cash in Hand',
                'account_number'    => 'CASH-01',
                'bank_name'         => 'Cash in Hand',
                'branch'            => 'Head Office',
                'ifsc_code'         => 'N/A',
                'is_default'        => false,
            ]
        );

        // 7. Seed Users with Dynamic Role Relations
        User::updateOrCreate(
            ['email' => 's4subhasish@gmail.com'],
            [
                'company_id'           => $company->id,
                'role_id'              => $superAdminRole->id,
                'user_code'            => 'User-1001',
                'name'                 => 'Subhasish Das',
                'designation'          => 'Managing Director',
                'mobile'               => '+91-9382445935',
                'password'             => Hash::make('password'),
                'role'                 => 'super_admin',
                'assigned_project_ids' => [$project1->id, $project2->id],
                'is_active'            => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@realestate.com'],
            [
                'company_id'           => $company->id,
                'role_id'              => $adminRole->id,
                'user_code'            => 'User-1002',
                'name'                 => 'Admin Officer',
                'designation'          => 'General Manager',
                'mobile'               => '+91-9876543210',
                'password'             => Hash::make('password'),
                'role'                 => 'admin',
                'assigned_project_ids' => [$project1->id, $project2->id],
                'is_active'            => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@realestate.com'],
            [
                'company_id'           => $company->id,
                'role_id'              => $managerRole->id,
                'user_code'            => 'User-1003',
                'name'                 => 'Rahul Sharma',
                'designation'          => 'Project Sales Manager',
                'mobile'               => '+91-9123456780',
                'password'             => Hash::make('password'),
                'role'                 => 'manager',
                'assigned_project_ids' => [$project1->id],
                'is_active'            => true,
            ]
        );
    }
}
