<?php

namespace Tests\Feature;

use App\Models\AutoNumberSequence;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantCompanyScopingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company1;
    protected Company $company2;
    protected Project $project1;
    protected Project $project2;
    protected BankAccount $bankAccount1;
    protected BankAccount $bankAccount2;
    protected AutoNumberSequence $seq1;
    protected AutoNumberSequence $seq2;
    protected User $superAdmin;
    protected User $company1Admin;
    protected User $company2Admin;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Two Companies
        $this->company1 = Company::create([
            'company_code'    => 'SSI',
            'name'            => 'S & S Infrastructure',
            'contact_primary' => '+91-9382445935',
            'email'           => 'ssi@test.com',
            'address'         => 'Silchar, Assam',
        ]);

        $this->company2 = Company::create([
            'company_code'    => 'APX',
            'name'            => 'Apex Developers',
            'contact_primary' => '+91-9876543210',
            'email'           => 'apex@test.com',
            'address'         => 'Guwahati, Assam',
        ]);

        // 2. Create Sequences
        $this->seq1 = AutoNumberSequence::create([
            'company_id'  => $this->company1->id,
            'entity_type' => 'project',
            'prefix'      => 'SSI/PRJ-',
            'next_number' => 1001,
            'padding'     => 0,
        ]);

        $this->seq2 = AutoNumberSequence::create([
            'company_id'  => $this->company2->id,
            'entity_type' => 'project',
            'prefix'      => 'APX/PRJ-',
            'next_number' => 1001,
            'padding'     => 0,
        ]);

        // AutoNumber for users
        AutoNumberSequence::create([
            'company_id'  => $this->company1->id,
            'entity_type' => 'user',
            'prefix'      => 'SSI/USR-',
            'next_number' => 1005,
            'padding'     => 0,
        ]);

        AutoNumberSequence::create([
            'company_id'  => $this->company2->id,
            'entity_type' => 'user',
            'prefix'      => 'APX/USR-',
            'next_number' => 1005,
            'padding'     => 0,
        ]);

        // 3. Create Projects
        $this->project1 = Project::create([
            'company_id'    => $this->company1->id,
            'project_code'  => 'SSI/PRJ-1001',
            'name'          => 'Ramkrishna Apartment',
            'nick_name'     => 'RA',
            'full_address'  => 'Silchar, Assam',
            'is_active'     => true,
        ]);

        $this->project2 = Project::create([
            'company_id'    => $this->company2->id,
            'project_code'  => 'APX/PRJ-1001',
            'name'          => 'Apex Heights',
            'nick_name'     => 'AH',
            'full_address'  => 'Guwahati, Assam',
            'is_active'     => true,
        ]);

        // 4. Create Bank Accounts
        $this->bankAccount1 = BankAccount::create([
            'company_id'        => $this->company1->id,
            'account_code'      => 'SSI/BNK-1001',
            'account_type'      => 'general',
            'account_nick_name' => 'SSI Main SBI',
            'account_name'      => 'S & S Infrastructure SBI',
            'account_number'    => '1111111111',
            'bank_name'         => 'State Bank of India',
            'branch'            => 'Silchar',
            'is_default'        => true,
        ]);

        $this->bankAccount2 = BankAccount::create([
            'company_id'        => $this->company2->id,
            'account_code'      => 'APX/BNK-1001',
            'account_type'      => 'general',
            'account_nick_name' => 'APX Main HDFC',
            'account_name'      => 'Apex Developers HDFC',
            'account_number'    => '2222222222',
            'bank_name'         => 'HDFC Bank',
            'branch'            => 'Guwahati',
            'is_default'        => true,
        ]);

        // 5. Create Permissions
        $perms = [
            'manage_companies', 'manage_autonumber', 'manage_roles',
            'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
            'view_bank_accounts', 'create_bank_accounts', 'edit_bank_accounts', 'delete_bank_accounts',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_bookings', 'create_bookings',
        ];

        $permIds = [];
        foreach ($perms as $slug) {
            $p = Permission::firstOrCreate(['slug' => $slug], ['name' => ucwords(str_replace('_', ' ', $slug)), 'module' => 'General']);
            $permIds[] = $p->id;
        }

        // 6. Create Roles
        $superAdminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'is_system' => true]);

        $this->adminRole = Role::firstOrCreate(['slug' => 'company_admin'], ['name' => 'Company Admin', 'is_system' => false]);
        $this->adminRole->permissions()->sync($permIds);

        // 7. Create Users
        $this->superAdmin = User::create([
            'company_id'           => $this->company1->id,
            'role_id'              => $superAdminRole->id,
            'user_code'            => 'SSI/USR-1001',
            'name'                 => 'Super Admin User',
            'email'                => 'superadmin@erp.com',
            'password'             => Hash::make('password'),
            'role'                 => 'super_admin',
            'assigned_project_ids' => [$this->project1->id],
            'is_active'            => true,
        ]);

        $this->company1Admin = User::create([
            'company_id'           => $this->company1->id,
            'role_id'              => $this->adminRole->id,
            'user_code'            => 'SSI/USR-1002',
            'name'                 => 'SSI Admin',
            'email'                => 'admin@ssi.com',
            'password'             => Hash::make('password'),
            'role'                 => 'company_admin',
            'assigned_project_ids' => [$this->project1->id],
            'is_active'            => true,
        ]);

        $this->company2Admin = User::create([
            'company_id'           => $this->company2->id,
            'role_id'              => $this->adminRole->id,
            'user_code'            => 'APX/USR-1001',
            'name'                 => 'Apex Admin',
            'email'                => 'admin@apex.com',
            'password'             => Hash::make('password'),
            'role'                 => 'company_admin',
            'assigned_project_ids' => [$this->project2->id],
            'is_active'            => true,
        ]);
    }

    public function test_company_admin_can_only_view_and_update_own_company(): void
    {
        $this->actingAs($this->company1Admin);

        // Index only lists SSI, not Apex
        $response = $this->get(route('admin.companies.index'));
        $response->assertStatus(200);
        $response->assertSee('S & S Infrastructure');
        $response->assertDontSee('Apex Developers');

        // Can edit own company
        $this->get(route('admin.companies.edit', $this->company1->id))->assertStatus(200);

        // Cannot edit company 2
        $this->get(route('admin.companies.edit', $this->company2->id))->assertStatus(403);

        // Cannot create brand new company (Super Admin only)
        $this->get(route('admin.companies.create'))->assertStatus(403);

        // Cannot delete company
        $this->delete(route('admin.companies.destroy', $this->company1->id))->assertStatus(403);
    }

    public function test_company_admin_can_only_view_and_manage_own_company_projects(): void
    {
        $this->actingAs($this->company1Admin);

        // Index only shows SSI projects
        $response = $this->get(route('admin.projects.index'));
        $response->assertStatus(200);
        $response->assertSee('Ramkrishna Apartment');
        $response->assertDontSee('Apex Heights');

        // Can edit own project
        $this->get(route('admin.projects.edit', $this->project1->id))->assertStatus(200);

        // Cannot edit project belonging to company 2
        $this->get(route('admin.projects.edit', $this->project2->id))->assertStatus(403);

        // Cannot create project under company 2
        $response = $this->post(route('admin.projects.store'), [
            'company_id'    => $this->company2->id,
            'name'          => 'Malicious Cross Project',
            'nick_name'     => 'MCP',
            'full_address'  => 'Some Address',
            'rera_category' => 'unregistered',
        ]);
        $response->assertStatus(403);

        // Cannot delete company 2 project
        $this->delete(route('admin.projects.destroy', $this->project2->id))->assertStatus(403);
    }

    public function test_company_admin_can_only_view_and_manage_own_company_bank_accounts(): void
    {
        $this->actingAs($this->company1Admin);

        // Index only shows SSI bank accounts
        $response = $this->get(route('admin.bank-accounts.index'));
        $response->assertStatus(200);
        $response->assertSee('SSI Main SBI');
        $response->assertDontSee('APX Main HDFC');

        // Can edit own bank account
        $this->get(route('admin.bank-accounts.edit', $this->bankAccount1->id))->assertStatus(200);

        // Cannot edit company 2 bank account
        $this->get(route('admin.bank-accounts.edit', $this->bankAccount2->id))->assertStatus(403);

        // Cannot create bank account under company 2
        $response = $this->post(route('admin.bank-accounts.store'), [
            'company_id'        => $this->company2->id,
            'account_type'      => 'general',
            'account_nick_name' => 'Illegal Apex Account',
            'account_name'      => 'Illegal Name',
            'bank_name'         => 'Test Bank',
        ]);
        $response->assertStatus(403);

        // Cannot delete company 2 bank account
        $this->delete(route('admin.bank-accounts.destroy', $this->bankAccount2->id))->assertStatus(403);
    }

    public function test_company_admin_can_only_view_and_manage_own_company_staff(): void
    {
        $this->actingAs($this->company1Admin);

        // Index only shows SSI staff
        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('SSI Admin');
        $response->assertDontSee('Apex Admin');

        // Cannot edit company 2 user
        $this->get(route('admin.users.edit', $this->company2Admin->id))->assertStatus(403);

        // Cannot create user under company 2
        $response = $this->post(route('admin.users.store'), [
            'company_id'            => $this->company2->id,
            'role_id'               => $this->adminRole->id,
            'name'                  => 'Illegal User',
            'designation'           => 'Manager',
            'mobile'                => '9999999999',
            'email'                 => 'illegal@apex.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'assigned_project_ids'  => [$this->project2->id],
        ]);
        $response->assertStatus(403);

        // Cannot delete company 2 user
        $this->delete(route('admin.users.destroy', $this->company2Admin->id))->assertStatus(403);
    }

    public function test_user_cannot_access_project_workspace_belonging_to_another_company(): void
    {
        $this->actingAs($this->company1Admin);

        // Can access own company project workspace
        $this->get(route('project.dashboard', $this->project1->id))->assertStatus(200);
        $this->get(route('project.bookings.index', $this->project1->id))->assertStatus(200);

        // Blocked from accessing company 2 project workspace
        $this->get(route('project.dashboard', $this->project2->id))->assertStatus(403);
        $this->get(route('project.bookings.index', $this->project2->id))->assertStatus(403);
    }

    public function test_user_cannot_modify_another_company_autonumber_sequence(): void
    {
        $this->actingAs($this->company1Admin);

        // Can see own sequence
        $response = $this->get(route('admin.autonumber.index'));
        $response->assertStatus(200);
        $response->assertSee('SSI/PRJ-');

        // Attempting to update company 2's sequence triggers 403 Forbidden
        $response = $this->put(route('admin.autonumber.update'), [
            'sequences' => [
                [
                    'id'          => $this->seq2->id,
                    'prefix'      => 'HACKED/',
                    'next_number' => 9999,
                    'padding'     => 4,
                ]
            ]
        ]);
        $response->assertStatus(403);
    }

    public function test_super_admin_has_unrestricted_cross_company_access(): void
    {
        $this->actingAs($this->superAdmin);

        // Can see all companies
        $response = $this->get(route('admin.companies.index'));
        $response->assertStatus(200);
        $response->assertSee('S & S Infrastructure');
        $response->assertSee('Apex Developers');

        // Can see projects of all companies
        $response = $this->get(route('admin.projects.index'));
        $response->assertStatus(200);
        $response->assertSee('Ramkrishna Apartment');
        $response->assertSee('Apex Heights');

        // Can access workspace of both companies' projects
        $this->get(route('project.dashboard', $this->project1->id))->assertStatus(200);
        $this->get(route('project.dashboard', $this->project2->id))->assertStatus(200);
    }
}
