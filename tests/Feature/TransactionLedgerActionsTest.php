<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionLedgerActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;
    protected BankAccount $bankAccount;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'         => 'SSB Properties Pvt Ltd',
            'company_code' => 'SSB',
            'is_active'    => true,
        ]);

        $superAdminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'is_system' => true]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role'       => 'super_admin',
            'role_id'    => $superAdminRole->id,
            'email'      => 'admin@realestate.test',
            'user_code'  => 'SSB/USR-999',
        ]);

        $autoNum = app(AutoNumberService::class);
        $autoNum->initializeCompanySequences($this->company->id, 'SSB');

        $this->bankAccount = BankAccount::create([
            'company_id'        => $this->company->id,
            'account_code'      => 'BANK-001',
            'account_nick_name' => 'HDFC Escrow Account',
            'account_name'      => 'SSB Properties Escrow A/C',
            'bank_name'         => 'HDFC Bank',
            'account_number'    => '50200012345678',
            'ifsc_code'         => 'HDFC0001234',
            'branch'            => 'Silchar Main',
            'account_type'      => 'project_linked',
            'is_default'        => true,
        ]);

        $this->project = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSB/PRJ-1001',
            'name'          => 'Greenwood Luxury Enclave',
            'nick_name'     => 'Greenwood',
            'daag_no'       => '567/890',
            'patta_no'      => '123',
            'holding_no'    => 'H-99',
            'mouza'         => 'Meherpur',
            'full_address'  => 'Silchar, Assam',
            'rera_category' => 'registered',
            'rera_reg_no'   => 'RERA-AS-2026-999',
            'is_active'     => true,
        ]);

        $this->booking = Booking::create([
            'project_id'              => $this->project->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/BK-1001',
            'booking_date'            => '2026-09-01',
            'unit_no'                 => 'Flat-3B',
            'built_up_area'           => 1100,
            'super_built_up_area'     => 1300,
            'property_type'           => 'Residential 3BHK Flat',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Pranab Dutta',
            'guardian_relation'       => 'son_of',
            'guardian_name'           => 'R. Dutta',
            'mobile_no'               => '+91-9876543210',
            'photo_id_type'           => 'aadhaar',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 3900000.00,
            'gross_total'             => 3900000.00,
            'discount_applied'        => 100000.00,
            'consideration_value'     => 3800000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 190000.00,
            'supplementary_value'     => 210000.00,
            'total_booking_value'     => 4200000.00,
            'taxable_agreement_value' => 3800000.00,
            'taxable_gst_value'       => 190000.00,
            'gross_taxable_value'     => 3990000.00,
            'gross_cash_value'        => 210000.00,
            'status'                  => 'live',
        ]);
    }

    public function test_transaction_ledger_renders_all_five_action_buttons_for_authorized_user(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.transactions.index', $this->project->id));

        $response->assertStatus(200);

        // Button 1: Collect Payment
        $response->assertSee('Collect Payment');
        $response->assertSee(route('project.transactions.create', $this->project->id));

        // Button 2: Refunds
        $response->assertSee('Refunds');
        $response->assertSee('Issue Refund Voucher');
        $response->assertSee(route('project.transactions.create', ['project' => $this->project->id, 'voucher_type' => 'payment_voucher']));
        $response->assertSee('Cancellation Payouts Hub');
        $response->assertSee('Filter Refund Records');

        // Button 3: Rebalance Overpayment
        $response->assertSee('Rebalance Overpayment');

        // Button 4: Cancel Booking
        $response->assertSee('Cancel Booking');
        $response->assertSee(route('project.cancellations.index', $this->project->id));

        // Button 5: Print Documents
        $response->assertSee('Print Documents');
        $response->assertSee(route('project.documents.index', $this->project->id));
    }

    public function test_transaction_ledger_shows_overpayment_badge_and_modal_when_booking_is_overpaid(): void
    {
        $this->actingAs($this->user);

        // Create transaction with bank excess (total_taxable_received = 4,500,000 > gross_taxable_value = 3,990,000)
        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/MR-9901',
            'voucher_date'           => '2026-09-05',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 4500000.00,
            'instrument_ref_no'      => 'UTR-9901',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'particulars'            => 'Full taxable payment with excess',
            'created_by'             => $this->user->id,
        ]);

        $response = $this->get(route('project.transactions.index', $this->project->id));

        $response->assertStatus(200);

        // Check that overpayment excess of 510,000 is detected and displayed
        $response->assertSee('Cross-Ledger Overpayment Rebalance');
        $response->assertSee('+₹510,000.00');
        $response->assertSee(route('project.bookings.adjustment.create', [$this->project->id, $this->booking->id]));
    }

    public function test_transaction_create_initializes_voucher_type_from_query_param(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.transactions.create', [
            'project'      => $this->project->id,
            'voucher_type' => 'payment_voucher',
        ]));

        $response->assertStatus(200);
        $response->assertSee("voucherType: 'payment_voucher'", false);
    }

    public function test_unauthorized_user_cannot_see_restricted_action_buttons(): void
    {
        // Only give view_transactions, no create, adjust, or cancel permissions
        $restrictedRole = Role::create([
            'name' => 'Restricted Viewer',
            'slug' => 'restricted_viewer',
            'is_system' => false,
        ]);
        $perm = Permission::firstOrCreate(['slug' => 'view_transactions'], ['name' => 'View Transactions', 'module' => 'General']);
        $restrictedRole->permissions()->attach($perm->id);

        $restrictedUser = User::factory()->create([
            'company_id'           => $this->company->id,
            'role'                 => 'accountant',
            'role_id'              => $restrictedRole->id,
            'assigned_project_ids' => [$this->project->id],
        ]);

        $this->actingAs($restrictedUser);

        $response = $this->get(route('project.transactions.index', $this->project->id));

        $response->assertStatus(200);
        $response->assertDontSee('Collect Payment');
        $response->assertDontSee('Rebalance Overpayment');
        $response->assertDontSee('Cancel Booking');
        $response->assertDontSee('Print Documents');
    }
}
