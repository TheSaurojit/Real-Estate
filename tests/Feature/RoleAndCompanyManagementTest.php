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

        // Auto-number sequence for users starting at 1003
        AutoNumberSequence::create([
            'company_id' => $this->company->id,
            'entity_type' => 'user',
            'prefix' => 'User-',
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

        // 4. Seed Single Super Admin User (User-1001)
        $this->superAdmin = User::create([
            'company_id' => $this->company->id,
            'role_id' => $this->superAdminRole->id,
            'user_code' => 'User-1001',
            'name' => 'Subhasish Das',
            'email' => 's4subhasish@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // 5. Seed Regular Manager User (User-1002)
        $this->regularManager = User::create([
            'company_id' => $this->company->id,
            'role_id' => $this->managerRole->id,
            'user_code' => 'User-1002',
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

        // 1. Can create multiple Admins
        $responseAdmin = $this->post(route('admin.users.store'), [
            'company_id' => $this->company->id,
            'role_id' => $this->adminRole->id,
            'name' => 'Second Admin Officer',
            'designation' => 'Operations Manager',
            'mobile' => '+91-9988776655',
            'email' => 'admin2@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $responseAdmin->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin2@test.com',
            'role' => 'admin',
        ]);

        // 2. Block creating second Super Admin
        $responseSuper = $this->post(route('admin.users.store'), [
            'company_id' => $this->company->id,
            'role_id' => $this->superAdminRole->id,
            'name' => 'Fake Super Admin',
            'designation' => 'Director',
            'mobile' => '+91-9988776644',
            'email' => 'fake_super@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $responseSuper->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'fake_super@test.com']);
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
}
