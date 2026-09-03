<?php

namespace Tests\Feature;

use App\Models\AutoNumberSequence;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAndCompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $superAdmin;
    protected User $regularManager;
    protected Role $superAdminRole;
    protected Role $adminRole;
    protected Role $managerRole;
    protected \App\Models\Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed base company
        $this->company = Company::create([
            'company_code' => 'SSI',
            'name' => 'S & S Infrastructure',
            'contact_primary' => '+91-9382445935',
            'email' => 's4subhasish@gmail.com',
            'address' => 'Silchar Road, Karimganj, Assam',
        ]);

        $this->project = \App\Models\Project::create([
            'company_id' => $this->company->id,
            'project_code' => 'SSI/PRJ-1001',
            'name' => 'Green Valley Residency',
            'nick_name' => 'Green Valley',
            'full_address' => 'Silchar, Assam',
            'is_active' => true,
        ]);

        // Auto-number sequence for users starting at 1003
        AutoNumberSequence::create([
            'company_id' => $this->company->id,
            'entity_type' => 'user',
            'prefix' => 'SSI/USR-',
            'next_number' => 1003,
            'padding' => 0,
        ]);

        // 2. Seed Permissions
        $perm1 = Permission::create(['name' => 'Manage Companies', 'slug' => 'manage_companies', 'module' => 'Company Settings']);
        $perm2 = Permission::create(['name' => 'Create Bookings', 'slug' => 'create_bookings', 'module' => 'Bookings']);
        $perm3 = Permission::create(['name' => 'View Transactions', 'slug' => 'view_transactions', 'module' => 'Transactions']);

        // 3. Seed Roles
        $this->superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'is_system' => true,
        ]);
        $this->superAdminRole->permissions()->sync([$perm1->id, $perm2->id, $perm3->id]);

        $this->adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);
        $this->adminRole->permissions()->sync([$perm2->id, $perm3->id]);

        $this->managerRole = Role::create([
            'name' => 'Project Manager',
            'slug' => 'manager',
            'is_system' => false,
        ]);
        $this->managerRole->permissions()->sync([$perm2->id]);

        // 4. Seed Single Super Admin User (SSI/USR-1001)
        $this->superAdmin = User::create([
            'company_id' => $this->company->id,
            'role_id' => $this->superAdminRole->id,
            'user_code' => 'SSI/USR-1001',
            'name' => 'Subhasish Das',
            'email' => 's4subhasish@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // 5. Seed Regular Manager User (SSI/USR-1002)
        $this->regularManager = User::create([
            'company_id' => $this->company->id,
            'role_id' => $this->managerRole->id,
            'user_code' => 'SSI/USR-1002',
            'name' => 'Sales Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_create_new_company(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.companies.store'), [
            'name' => 'S & S Builders & Developers Pvt Ltd',
            'company_code' => 'SSB',
            'contact_primary' => '+91-9876543210',
            'email' => 'info@ssb.com',
            'address' => 'Silchar Road, Karimganj, Assam',
            'pan_number' => 'AABCS1234F',
            'gstin' => '18AABCS1234F1Z5',
        ]);

        $response->assertRedirect(route('admin.companies.index'));
        $this->assertDatabaseHas('companies', [
            'name' => 'S & S Builders & Developers Pvt Ltd',
            'company_code' => 'SSB',
        ]);

        // Verify that auto-number sequences were initialized for the new company
        $newCompany = Company::where('company_code', 'SSB')->first();
        $this->assertNotNull($newCompany);
        $this->assertDatabaseHas('auto_number_sequences', [
            'company_id' => $newCompany->id,
            'entity_type' => 'project',
            'prefix' => 'SSB/PRJ-',
        ]);
    }

    public function test_non_super_admin_cannot_create_company(): void
    {
        $this->actingAs($this->regularManager);

        $response = $this->post(route('admin.companies.store'), [
            'name' => 'Unauthorized Company',
            'company_code' => 'UNAUTH',
            'contact_primary' => '+91-9876543210',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_custom_role_with_permissions(): void
    {
        $this->actingAs($this->superAdmin);

        $permBooking = Permission::where('slug', 'create_bookings')->first();
        $permTrans = Permission::where('slug', 'view_transactions')->first();

        $response = $this->post(route('admin.roles.store'), [
            'name' => 'Chief Accounts Officer',
            'description' => 'Handles financial transactions and audits',
            'permissions' => [$permBooking->id, $permTrans->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => 'Chief Accounts Officer',
            'slug' => 'chief_accounts_officer',
        ]);

        $createdRole = Role::where('slug', 'chief_accounts_officer')->first();
        $this->assertTrue($createdRole->hasPermission('create_bookings'));
        $this->assertTrue($createdRole->hasPermission('view_transactions'));
        $this->assertFalse($createdRole->hasPermission('manage_companies'));
    }

    public function test_user_has_permission_evaluates_correctly(): void
    {
        // Super Admin has all permissions
        $this->assertTrue($this->superAdmin->hasPermission('manage_companies'));
        $this->assertTrue($this->superAdmin->hasPermission('create_bookings'));

        // Manager only has assigned permissions
        $this->assertTrue($this->regularManager->hasPermission('create_bookings'));
        $this->assertFalse($this->regularManager->hasPermission('view_transactions'));
        $this->assertFalse($this->regularManager->hasPermission('manage_companies'));
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.roles.destroy', $this->superAdminRole->id));
        $this->assertDatabaseHas('roles', ['id' => $this->superAdminRole->id]);
    }

    public function test_system_allows_creating_multiple_admins_but_blocks_second_super_admin(): void
    {
        $this->actingAs($this->superAdmin);

        // 1. Can create multiple Admins with assigned project
        $responseAdmin = $this->post(route('admin.users.store'), [
            'company_id'           => $this->company->id,
            'role_id'              => $this->adminRole->id,
            'name'                 => 'Second Admin Officer',
            'designation'          => 'Operations Manager',
            'mobile'               => '+91-9988776655',
            'email'                => 'admin2@test.com',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'assigned_project_ids' => [$this->project->id],
        ]);

        $responseAdmin->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin2@test.com',
            'role' => 'admin',
        ]);

        // 2. Block creating second Super Admin
        $responseSuper = $this->post(route('admin.users.store'), [
            'company_id'           => $this->company->id,
            'role_id'              => $this->superAdminRole->id,
            'name'                 => 'Fake Super Admin',
            'designation'          => 'Director',
            'mobile'               => '+91-9988776644',
            'email'                => 'fake_super@test.com',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'assigned_project_ids' => [$this->project->id],
        ]);

        $responseSuper->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'fake_super@test.com']);
    }

    public function test_user_cannot_be_created_without_assigning_a_project(): void
    {
        $this->actingAs($this->superAdmin);

        // Try creating user with empty project assignments
        $response = $this->post(route('admin.users.store'), [
            'company_id'           => $this->company->id,
            'role_id'              => $this->adminRole->id,
            'name'                 => 'No Project User',
            'designation'          => 'Accountant',
            'mobile'               => '+91-9876543210',
            'email'                => 'noproject@test.com',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'assigned_project_ids' => [],
        ]);

        $response->assertSessionHasErrors('assigned_project_ids');
        $this->assertDatabaseMissing('users', ['email' => 'noproject@test.com']);
    }

    public function test_user_cannot_be_created_with_project_from_another_company(): void
    {
        $this->actingAs($this->superAdmin);

        $otherCompany = Company::create([
            'company_code'    => 'OTH',
            'name'            => 'Other Company Ltd',
            'contact_primary' => '+91-9000000000',
            'email'           => 'other@test.com',
            'address'         => 'Other City',
        ]);

        $otherProject = \App\Models\Project::create([
            'company_id'   => $otherCompany->id,
            'project_code' => 'OTH/PRJ-1001',
            'name'         => 'Other Project',
            'nick_name'    => 'Other',
            'full_address' => 'Guwahati, Assam',
            'is_active'    => true,
        ]);

        // Attempt to create a user for $this->company but assign $otherProject
        $response = $this->post(route('admin.users.store'), [
            'company_id'           => $this->company->id,
            'role_id'              => $this->adminRole->id,
            'name'                 => 'Cross Company User',
            'designation'          => 'Auditor',
            'mobile'               => '+91-9876543210',
            'email'                => 'cross@test.com',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'assigned_project_ids' => [$otherProject->id],
        ]);

        $response->assertSessionHasErrors('assigned_project_ids');
        $this->assertDatabaseMissing('users', ['email' => 'cross@test.com']);
    }

    public function test_user_created_successfully_when_assigned_to_project(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('admin.users.store'), [
            'company_id'           => $this->company->id,
            'role_id'              => $this->adminRole->id,
            'name'                 => 'Valid Project User',
            'designation'          => 'Site Incharge',
            'mobile'               => '+91-9876543211',
            'email'                => 'validuser@test.com',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'assigned_project_ids' => [$this->project->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'validuser@test.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertContains($this->project->id, $createdUser->assigned_project_ids);
    }

    public function test_cannot_delete_the_primary_super_admin(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('admin.users.destroy', $this->superAdmin->id));
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_super_admin_role_cannot_be_edited(): void
    {
        $this->actingAs($this->superAdmin);

        // GET edit page should redirect with error
        $responseEdit = $this->get(route('admin.roles.edit', $this->superAdminRole->id));
        $responseEdit->assertRedirect(route('admin.roles.index'));
        $responseEdit->assertSessionHasErrors('error');

        // PUT update should redirect with error
        $responseUpdate = $this->put(route('admin.roles.update', $this->superAdminRole->id), [
            'name' => 'Renamed Super Admin',
            'permissions' => [],
        ]);
        $responseUpdate->assertRedirect(route('admin.roles.index'));
        $responseUpdate->assertSessionHasErrors('error');
    }

    public function test_user_creation_page_renders_and_does_not_show_auto_generated_user_id(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.users.create'));
        $response->assertStatus(200);
        $response->assertDontSee('Auto-Generated ID');
        $response->assertSee('Create User Account');
    }

    public function test_creating_user_in_different_company_avoids_user_code_collision(): void
    {
        $this->actingAs($this->superAdmin);

        // Create a second company
        $company2 = Company::create([
            'company_code' => 'COMP2',
            'name' => 'Second Company Ltd',
            'contact_primary' => '+91-9123456789',
            'address' => 'Silchar',
        ]);

        $project2 = \App\Models\Project::create([
            'company_id'   => $company2->id,
            'project_code' => 'COMP2/PRJ-1001',
            'name'         => 'COMP2 Luxury Tower',
            'nick_name'    => 'COMP2 Tower',
            'full_address' => 'Silchar, Assam',
            'is_active'    => true,
        ]);

        // AutoNumberSequence for company2 is uninitialized or starts at 1001
        // Submitting user create for company2 should generate COMP2/USR-1001
        $response = $this->post(route('admin.users.store'), [
            'company_id'           => $company2->id,
            'role_id'              => $this->adminRole->id,
            'name'                 => 'Sayan Kr',
            'designation'          => 'Site Engineer',
            'mobile'               => '+91-9395340221',
            'email'                => 'sayankr@gmail.com',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'assigned_project_ids' => [$project2->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'sayankr@gmail.com',
            'company_id' => $company2->id,
        ]);

        $createdUser = User::where('email', 'sayankr@gmail.com')->first();
        $this->assertNotNull($createdUser->user_code);
        // Company 2 starts its own sequence at COMP2/USR-1001
        $this->assertEquals('COMP2/USR-1001', $createdUser->user_code);
    }

    public function test_assigned_projects_are_strictly_filtered_by_selected_company(): void
    {
        $this->actingAs($this->superAdmin);

        // Project 1 belongs to Company 1
        $project1 = \App\Models\Project::create([
            'company_id' => $this->company->id,
            'project_code' => 'SSI/PRJ-01',
            'name' => 'Company 1 Tower',
            'nick_name' => 'C1T',
            'full_address' => 'Silchar',
            'is_active' => true,
        ]);

        // Company 2 and Project 2
        $company2 = Company::create([
            'company_code' => 'C2',
            'name' => 'Company 2 Ltd',
            'contact_primary' => '+91-9123456789',
            'address' => 'Silchar',
        ]);

        $project2 = \App\Models\Project::create([
            'company_id' => $company2->id,
            'project_code' => 'C2/PRJ-01',
            'name' => 'Company 2 Residency',
            'nick_name' => 'C2R',
            'full_address' => 'Guwahati',
            'is_active' => true,
        ]);

        // Try to assign both project1 (from company1) and project2 (from company2) to a user in company2
        $response = $this->post(route('admin.users.store'), [
            'company_id' => $company2->id,
            'role_id' => $this->adminRole->id,
            'name' => 'Cross Company Test Staff',
            'designation' => 'Supervisor',
            'mobile' => '+91-9876543299',
            'email' => 'cross_staff@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'assigned_project_ids' => [$project1->id, $project2->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'cross_staff@test.com')->first();
        $this->assertNotNull($createdUser);
        // Only project2 from company2 should be stored in assigned_project_ids
        $this->assertEquals([$project2->id], $createdUser->assigned_project_ids);
    }
}
