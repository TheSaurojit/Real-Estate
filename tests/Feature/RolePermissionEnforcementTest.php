<?php

namespace Tests\Feature;

use App\Models\AutoNumberSequence;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RolePermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Project $project1;
    protected Project $project2;
    protected User $superAdmin;
    protected User $salesStaff;
    protected User $siteStaff;
    protected Role $salesRole;
    protected Role $siteRole;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Base Company
        $this->company = Company::create([
            'company_code'    => 'SSI',
            'name'            => 'S & S Infrastructure',
            'contact_primary' => '+91-9382445935',
            'email'           => 's4subhasish@gmail.com',
            'address'         => 'Silchar Road, Karimganj, Assam',
        ]);

        // AutoNumber sequence for users
        AutoNumberSequence::create([
            'company_id'  => $this->company->id,
            'entity_type' => 'user',
            'prefix'      => 'SSI/USR-',
            'next_number' => 1005,
            'padding'     => 0,
        ]);

        // 2. Create Two Projects
        $this->project1 = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSI/PRJ-1001',
            'name'          => 'Ramkrishna Apartment',
            'nick_name'     => 'RA',
            'full_address'  => 'Silchar, Assam',
            'is_active'     => true,
        ]);

        $this->project2 = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSI/PRJ-1002',
            'name'          => 'Imperial Grand',
            'nick_name'     => 'IG',
            'full_address'  => 'Silchar, Assam',
            'is_active'     => true,
        ]);

        // 3. Create Standard Permissions
        $pManageCompanies = Permission::create(['name' => 'Manage Companies', 'slug' => 'manage_companies', 'module' => 'Company Settings']);
        $pManageAutoNum   = Permission::create(['name' => 'Manage Auto-Numbering', 'slug' => 'manage_autonumber', 'module' => 'Company Settings']);
        $pManageRoles     = Permission::create(['name' => 'Manage Roles', 'slug' => 'manage_roles', 'module' => 'User & Roles']);
        $pViewProjects    = Permission::create(['name' => 'View Projects', 'slug' => 'view_projects', 'module' => 'Projects Master']);
        $pCreateProjects  = Permission::create(['name' => 'Create Projects', 'slug' => 'create_projects', 'module' => 'Projects Master']);
        $pViewBookings    = Permission::create(['name' => 'View Bookings', 'slug' => 'view_bookings', 'module' => 'Bookings']);
        $pCreateBookings  = Permission::create(['name' => 'Create Bookings', 'slug' => 'create_bookings', 'module' => 'Bookings']);
        $pViewTrans       = Permission::create(['name' => 'View Transactions', 'slug' => 'view_transactions', 'module' => 'Transactions']);
        $pCreateReceipts  = Permission::create(['name' => 'Create Receipts', 'slug' => 'create_receipts', 'module' => 'Transactions']);
        $pManageExpenses  = Permission::create(['name' => 'Manage Site Expenses', 'slug' => 'manage_expenses', 'module' => 'Construction Expense']);
        $pPrintDocs       = Permission::create(['name' => 'Print Documents', 'slug' => 'print_documents', 'module' => 'Print Documents']);
        $pViewFinReports  = Permission::create(['name' => 'View Financial Reports', 'slug' => 'view_financial_reports', 'module' => 'Reports']);

        // 4. Create Roles
        $superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin', 'is_system' => true]);

        $this->salesRole = Role::create(['name' => 'Sales Executive', 'slug' => 'sales_executive', 'is_system' => false]);
        $this->salesRole->permissions()->sync([
            $pViewBookings->id,
            $pCreateBookings->id,
            $pViewTrans->id,
            $pCreateReceipts->id,
            $pPrintDocs->id,
        ]);

        $this->siteRole = Role::create(['name' => 'Site Engineer', 'slug' => 'site_engineer', 'is_system' => false]);
        $this->siteRole->permissions()->sync([
            $pManageExpenses->id,
            $pPrintDocs->id,
        ]);

        // 5. Create Users
        $this->superAdmin = User::create([
            'company_id'           => $this->company->id,
            'role_id'              => $superAdminRole->id,
            'user_code'            => 'SSI/USR-1001',
            'name'                 => 'Subhasish Das',
            'email'                => 'super@test.com',
            'password'             => Hash::make('password'),
            'role'                 => 'super_admin',
            'assigned_project_ids' => [$this->project1->id], // only explicitly assigned to project1, but super admin bypasses
            'is_active'            => true,
        ]);

        $this->salesStaff = User::create([
            'company_id'           => $this->company->id,
            'role_id'              => $this->salesRole->id,
            'user_code'            => 'SSI/USR-1002',
            'name'                 => 'Priya Roy',
            'email'                => 'priya@test.com',
            'password'             => Hash::make('password'),
            'role'                 => 'sales_executive',
            'assigned_project_ids' => [$this->project1->id], // assigned strictly to project1
            'is_active'            => true,
        ]);

        $this->siteStaff = User::create([
            'company_id'           => $this->company->id,
            'role_id'              => $this->siteRole->id,
            'user_code'            => 'SSI/USR-1003',
            'name'                 => 'Anupam Deb',
            'email'                => 'anupam@test.com',
            'password'             => Hash::make('password'),
            'role'                 => 'site_engineer',
            'assigned_project_ids' => [$this->project1->id],
            'is_active'            => true,
        ]);
    }

    public function test_super_admin_bypasses_all_checks_including_unassigned_projects(): void
    {
        $this->actingAs($this->superAdmin);

        // Can access admin modules
        $this->get(route('admin.companies.index'))->assertStatus(200);
        $this->get(route('admin.companies.create'))->assertStatus(200);
        $this->get(route('admin.autonumber.index'))->assertStatus(200);
        $this->get(route('admin.roles.index'))->assertStatus(200);
        $this->get(route('admin.projects.index'))->assertStatus(200);
        $this->get(route('admin.bank-accounts.index'))->assertStatus(200);
        $this->get(route('admin.users.index'))->assertStatus(200);

        // Can access Project 1
        $this->get(route('project.dashboard', $this->project1->id))->assertStatus(200);
        $this->get(route('project.bookings.index', $this->project1->id))->assertStatus(200);

        // Can access Project 2 even though not in assigned_project_ids
        $this->get(route('project.dashboard', $this->project2->id))->assertStatus(200);
        $this->get(route('project.bookings.index', $this->project2->id))->assertStatus(200);
        $this->get(route('project.expenses.index', $this->project2->id))->assertStatus(200);
        $this->get(route('project.documents.index', $this->project2->id))->assertStatus(200);
    }

    public function test_staff_user_with_permission_can_access_allowed_modules(): void
    {
        $this->actingAs($this->salesStaff);

        // Sales staff has view_bookings & create_bookings on assigned project
        $this->get(route('project.bookings.index', $this->project1->id))->assertStatus(200);
        $this->get(route('project.bookings.create', $this->project1->id))->assertStatus(200);

        // Sales staff has view_transactions & create_receipts
        $this->get(route('project.transactions.index', $this->project1->id))->assertStatus(200);
        $this->get(route('project.transactions.create', $this->project1->id))->assertStatus(200);

        // Sales staff has print_documents
        $this->get(route('project.documents.index', $this->project1->id))->assertStatus(200);
    }

    public function test_staff_user_without_permission_receives_403_forbidden(): void
    {
        $this->actingAs($this->salesStaff);

        // Sales staff does NOT have manage_companies
        $this->get(route('admin.companies.index'))->assertStatus(403);
        $this->get(route('admin.companies.create'))->assertStatus(403);

        // Sales staff does NOT have manage_autonumber
        $this->get(route('admin.autonumber.index'))->assertStatus(403);

        // Sales staff does NOT have manage_roles
        $this->get(route('admin.roles.index'))->assertStatus(403);

        // Sales staff does NOT have manage_expenses
        $this->get(route('project.expenses.index', $this->project1->id))->assertStatus(403);
        $this->get(route('project.expenses.create', $this->project1->id))->assertStatus(403);
    }

    public function test_staff_user_cannot_access_unassigned_project(): void
    {
        $this->actingAs($this->salesStaff);

        // Sales staff is assigned to project1, so project1 dashboard passes
        $this->get(route('project.dashboard', $this->project1->id))->assertStatus(200);

        // Attempting to access project2 (which is not in assigned_project_ids) triggers 403 Forbidden
        $this->get(route('project.dashboard', $this->project2->id))->assertStatus(403);
        $this->get(route('project.bookings.index', $this->project2->id))->assertStatus(403);
        $this->get(route('project.transactions.index', $this->project2->id))->assertStatus(403);

        // Also switcher blocks switching to unassigned project
        $response = $this->post(route('switch.context'), ['target' => (string)$this->project2->id]);
        $response->assertStatus(403);
    }

    public function test_ui_hides_unauthorized_buttons_and_navigation_links(): void
    {
        $this->actingAs($this->siteStaff);

        // Site staff has manage_expenses, but lacks view_bookings and view_transactions
        $response = $this->get(route('project.dashboard', $this->project1->id));
        $response->assertStatus(200);

        // Sidebar should see Site Expenses and Print Documents
        $response->assertSee('Site Expenses');
        $response->assertSee('Print Documents');

        // Sidebar should NOT see Bookings Master or Transactions Ledger
        $response->assertDontSee('Bookings Master');
        $response->assertDontSee('Transactions Ledger');

        // Should NOT see Admin Settings menu
        $response->assertDontSee('Companies Master');
        $response->assertDontSee('Roles & Permissions');
        $response->assertDontSee('Auto-Numbering');
    }
}
