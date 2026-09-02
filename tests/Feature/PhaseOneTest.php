<?php

namespace Tests\Feature;

use App\Models\AutoNumberSequence;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseOneTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create initial test company
        $this->company = Company::create([
            'company_code' => 'SSI',
            'name' => 'S & S Infrastructure',
            'contact_primary' => '+91-9382445935',
            'email' => 's4subhasish@gmail.com',
            'address' => 'Silchar Road, Karimganj, Assam',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'project_code' => 'SSI/PRJ1001',
            'name' => 'Ramkrishna Apartment',
            'nick_name' => 'RA',
            'daag_no' => '156',
            'patta_no' => '258 [2nd RS]',
            'holding_no' => '129',
            'mouza' => 'Ambicapur Part-X',
            'pogonah' => 'Barakpar',
            'full_address' => 'Krishna Charan Road, Silchar',
            'rera_category' => 'registered',
            'rera_reg_no' => 'RERA 1458 CA of 2025',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'company_id' => $this->company->id,
            'user_code' => 'SSI/USR-1001',
            'name' => 'Subhasish Das',
            'designation' => 'Manager',
            'mobile' => '+91-9382445935',
            'email' => 's4subhasish@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'assigned_project_ids' => [$this->project->id],
            'is_active' => true,
        ]);
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Real Estate ERP Studio');
        $response->assertSee('User Authentication');
    }

    public function test_user_can_login_using_email(): void
    {
        $response = $this->post(route('login.submit'), [
            'login_id' => 's4subhasish@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_user_can_login_using_user_code(): void
    {
        $response = $this->post(route('login.submit'), [
            'login_id' => 'SSI/USR-1001',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_admin_dashboard_renders_successfully(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('System Overview');
        $response->assertSee('Ramkrishna Apartment');
        $response->assertSee('Manage Companies');
    }

    public function test_autonumber_service_generates_sequential_gapless_codes(): void
    {
        $service = app(AutoNumberService::class);

        // Configure a test sequence
        $service->configureSequence('test_entity', 'TEST-', 100, 3, $this->company->id);

        $peek = $service->peekNextNumber('test_entity', $this->company->id);
        $this->assertEquals('TEST-100', $peek);

        $code1 = $service->getNextNumber('test_entity', $this->company->id, true);
        $this->assertEquals('TEST-100', $code1);

        $code2 = $service->getNextNumber('test_entity', $this->company->id, true);
        $this->assertEquals('TEST-101', $code2);

        $code3 = $service->getNextNumber('test_entity', $this->company->id, true);
        $this->assertEquals('TEST-102', $code3);
    }

    public function test_admin_can_create_project_with_autonumbering(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.projects.store'), [
            'company_id' => $this->company->id,
            'name' => 'Imperial Grand Phase 2',
            'nick_name' => 'IG2',
            'daag_no' => '500',
            'patta_no' => '600',
            'holding_no' => '700',
            'mouza' => 'Ambicapur PT. X',
            'pogonah' => 'Barakpar',
            'full_address' => 'Sahid Tarani Road, Silchar',
            'rera_category' => 'registered',
            'rera_reg_no' => 'RERA 9999 CA 2026',
        ]);

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertDatabaseHas('projects', [
            'name' => 'Imperial Grand Phase 2',
            'nick_name' => 'IG2',
        ]);
    }

    public function test_admin_can_create_bank_account(): void
    {
        $this->actingAs($this->adminUser);

        $uniqueNick = 'HDFC Escrow Test ' . uniqid();

        $response = $this->post(route('admin.bank-accounts.store'), [
            'company_id' => $this->company->id,
            'account_type' => 'project_linked',
            'project_id' => $this->project->id,
            'account_nick_name' => $uniqueNick,
            'account_name' => 'S & S Infrastructure',
            'account_number' => '987654321098',
            'bank_name' => 'HDFC Bank',
            'branch' => 'Silchar',
            'ifsc_code' => 'HDFC0001234',
            'is_default' => 0,
        ]);

        $response->assertRedirect(route('admin.bank-accounts.index'));
        $this->assertDatabaseHas('bank_accounts', [
            'account_nick_name' => $uniqueNick,
            'account_number' => '987654321098',
        ]);
    }

    public function test_project_switcher_changes_active_session(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('switch.context'), [
            'target' => (string)$this->project->id,
        ]);

        $response->assertRedirect(route('project.dashboard', $this->project->id));
        $this->assertEquals($this->project->id, session('active_project_id'));

        // Switch to Admin
        $responseAdmin = $this->post(route('switch.context'), [
            'target' => 'admin',
        ]);

        $responseAdmin->assertRedirect(route('admin.dashboard'));
        $this->assertNull(session('active_project_id'));
    }
}
