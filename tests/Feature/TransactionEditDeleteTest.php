<?php

namespace Tests\Feature;

use App\Models\AutoNumberSequence;
use App\Models\BankAccount;
use App\Models\BankFinance;
use App\Models\Booking;
use App\Models\BookingCancellationRefund;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionEditDeleteTest extends TestCase
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

        $this->user = User::factory()->create([
            'email'     => 'finance@ssb.com',
            'user_code' => 'SSB/USR-1001',
        ]);

        $this->company = Company::create([
            'name'         => 'SSB Properties Pvt Ltd',
            'company_code' => 'SSB',
            'is_active'    => true,
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

    public function test_user_can_view_transaction_edit_page(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1001',
            'voucher_date'           => '2026-09-02',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 500000.00,
            'instrument_ref_no'      => 'UTR-123456',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'particulars'            => 'Advance token',
            'created_by'             => $this->user->id,
        ]);

        $response = $this->get(route('project.transactions.edit', [$this->project->id, $tx->id]));
        $response->assertStatus(200);
        $response->assertSee('Edit Transaction');
        $response->assertSee('Voucher Code (Immutable)');
        $response->assertSee('SSB/RCD1001');
        $response->assertSee('Pranab Dutta');
        $response->assertSee('500000');
    }

    public function test_user_can_update_transaction_and_rebalances_ledger(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1001',
            'voucher_date'           => '2026-09-02',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 500000.00,
            'instrument_ref_no'      => 'UTR-123456',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'particulars'            => 'Advance token',
            'created_by'             => $this->user->id,
        ]);

        // Prior taxable received is 500,000
        $this->assertEquals(500000.00, $this->booking->fresh()->total_taxable_received);

        // Update amount to 750,000 and mode to rtgs
        $updateData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-03',
            'transaction_mode'  => 'rtgs',
            'source_of_payment' => 'self',
            'amount'            => 750000.00,
            'bank_account_id'   => $this->bankAccount->id,
            'instrument_ref_no' => 'UTR-UPDATED-999',
            'instrument_status' => 'cleared',
            'particulars'       => 'Updated advance amount',
        ];

        $response = $this->put(route('project.transactions.update', [$this->project->id, $tx->id]), $updateData);
        $response->assertRedirect(route('project.transactions.index', $this->project->id));
        $response->assertSessionHas('success');

        $tx->refresh();
        $this->assertEquals(750000.00, $tx->amount);
        $this->assertEquals('rtgs', $tx->transaction_mode);
        $this->assertEquals('UTR-UPDATED-999', $tx->instrument_ref_no);
        $this->assertEquals('SSB/RCD1001', $tx->transaction_code); // Immutable code preserved

        // Verify booking balances rebalanced dynamically
        $this->assertEquals(750000.00, $this->booking->fresh()->total_taxable_received);
    }

    public function test_updating_transaction_to_loan_account_syncs_bank_finance(): void
    {
        $this->actingAs($this->user);

        $bankFinance = BankFinance::create([
            'booking_id'        => $this->booking->id,
            'bank_name'         => 'State Bank of India',
            'branch'            => 'Silchar Main',
            'sanctioned_amount' => 2000000.00,
            'disbursed_amount'  => 0.00,
            'finance_status'    => 'sanctioned',
        ]);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1002',
            'voucher_date'           => '2026-09-05',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 600000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        $this->assertEquals(0.00, $bankFinance->fresh()->disbursed_amount);

        // Edit transaction: change source to through_loan_account
        $updateData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-05',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'through_loan_account',
            'amount'            => 600000.00,
            'bank_account_id'   => $this->bankAccount->id,
            'instrument_status' => 'cleared',
        ];

        $response = $this->put(route('project.transactions.update', [$this->project->id, $tx->id]), $updateData);
        $response->assertRedirect(route('project.transactions.index', $this->project->id));

        $bankFinance->refresh();
        $this->assertEquals(600000.00, (float)$bankFinance->disbursed_amount);
        $this->assertEquals('disbursed', $bankFinance->finance_status);
    }

    public function test_user_can_delete_transaction_and_rebalances_balances(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1003',
            'voucher_date'           => '2026-09-05',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 400000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        $this->assertEquals(400000.00, $this->booking->fresh()->total_taxable_received);

        // Delete transaction
        $response = $this->delete(route('project.transactions.destroy', [$this->project->id, $tx->id]));
        $response->assertRedirect(route('project.transactions.index', $this->project->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
        $this->assertEquals(0.00, $this->booking->fresh()->total_taxable_received);
    }

    public function test_deleting_loan_disbursement_transaction_syncs_bank_finance(): void
    {
        $this->actingAs($this->user);

        $bankFinance = BankFinance::create([
            'booking_id'        => $this->booking->id,
            'bank_name'         => 'Axis Bank',
            'branch'            => 'Silchar Main',
            'sanctioned_amount' => 1500000.00,
            'disbursed_amount'  => 500000.00,
            'finance_status'    => 'disbursed',
        ]);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1004',
            'voucher_date'           => '2026-09-06',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'through_loan_account',
            'amount'                 => 500000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        $response = $this->delete(route('project.transactions.destroy', [$this->project->id, $tx->id]));
        $response->assertRedirect(route('project.transactions.index', $this->project->id));

        $bankFinance->refresh();
        $this->assertEquals(0.00, (float)$bankFinance->disbursed_amount);
        $this->assertEquals('sanctioned', $bankFinance->finance_status);
    }

    public function test_edit_and_delete_buttons_are_rendered_in_index_and_booking_show(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1005',
            'voucher_date'           => '2026-09-06',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 250000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        // Check index page
        $indexRes = $this->get(route('project.transactions.index', $this->project->id));
        $indexRes->assertStatus(200);
        $indexRes->assertSee(route('project.transactions.edit', [$this->project->id, $tx->id]));
        $indexRes->assertSee(route('project.transactions.destroy', [$this->project->id, $tx->id]));
        $indexRes->assertSee('Edit');
        $indexRes->assertSee('Delete');

        // Check booking show page
        $showRes = $this->get(route('project.bookings.show', [$this->project->id, $this->booking->id]));
        $showRes->assertStatus(200);
        $showRes->assertSee(route('project.transactions.edit', [$this->project->id, $tx->id]));
        $showRes->assertSee(route('project.transactions.destroy', [$this->project->id, $tx->id]));
    }

    public function test_unauthorized_user_without_permission_cannot_access_edit_or_delete(): void
    {
        $restrictedRole = Role::create([
            'name'        => 'Staff Viewer',
            'slug'        => 'staff_viewer',
            'permissions' => ['view_transactions', 'view_bookings'],
        ]);

        $restrictedUser = User::factory()->create([
            'role_id'   => $restrictedRole->id,
            'role'      => 'staff_viewer',
            'user_code' => 'SSB/USR-RESTRICTED',
        ]);

        $this->actingAs($restrictedUser);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1006',
            'voucher_date'           => '2026-09-06',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 100000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        // Attempt edit GET -> 403 Forbidden
        $editRes = $this->get(route('project.transactions.edit', [$this->project->id, $tx->id]));
        $editRes->assertStatus(403);

        // Attempt update PUT -> 403 Forbidden
        $updateRes = $this->put(route('project.transactions.update', [$this->project->id, $tx->id]), [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-06',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 200000.00,
        ]);
        $updateRes->assertStatus(403);

        // Attempt destroy DELETE -> 403 Forbidden
        $deleteRes = $this->delete(route('project.transactions.destroy', [$this->project->id, $tx->id]));
        $deleteRes->assertStatus(403);
    }

    public function test_user_can_create_payment_voucher_with_taxable_category_and_voucher_no(): void
    {
        $this->actingAs($this->user);

        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'payment_voucher',
            'payment_category'  => 'taxable',
            'voucher_no'        => 'VCH-TAX-8899',
            'voucher_date'      => '2026-09-08',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 150000.00,
            'bank_account_id'   => $this->bankAccount->id,
            'particulars'       => 'Taxable agreement refund outflow',
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);
        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $this->booking->id]));

        $this->assertDatabaseHas('transactions', [
            'booking_id'             => $this->booking->id,
            'voucher_type'           => 'payment_voucher',
            'voucher_category'       => 'payment_refund',
            'payment_category'       => 'taxable',
            'voucher_no'             => 'VCH-TAX-8899',
            'amount'                 => 150000.00,
            'is_taxable_transaction' => true,
        ]);
    }

    public function test_user_can_create_payment_voucher_with_non_taxable_category(): void
    {
        $this->actingAs($this->user);

        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'payment_voucher',
            'payment_category'  => 'non_taxable',
            'voucher_no'        => 'VCH-CASH-4455',
            'voucher_date'      => '2026-09-08',
            'transaction_mode'  => 'cash',
            'source_of_payment' => 'self',
            'amount'            => 50000.00,
            'particulars'       => 'Non-taxable cash refund outflow',
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);
        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $this->booking->id]));

        $this->assertDatabaseHas('transactions', [
            'booking_id'             => $this->booking->id,
            'voucher_type'           => 'payment_voucher',
            'voucher_category'       => 'payment_refund',
            'payment_category'       => 'non_taxable',
            'voucher_no'             => 'VCH-CASH-4455',
            'amount'                 => 50000.00,
            'is_taxable_transaction' => false,
        ]);
    }

    public function test_payment_category_is_required_when_voucher_is_payment_voucher(): void
    {
        $this->actingAs($this->user);

        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'payment_voucher',
            'voucher_date'      => '2026-09-08',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 100000.00,
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);
        $response->assertSessionHasErrors(['payment_category']);
    }

    public function test_user_can_update_payment_category_and_voucher_no(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/PMT1001',
            'voucher_no'             => 'OLD-VCH-1',
            'voucher_date'           => '2026-09-08',
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'payment_category'       => 'taxable',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 100000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        $updateData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'payment_voucher',
            'payment_category'  => 'non_taxable',
            'voucher_no'        => 'NEW-VCH-2',
            'voucher_date'      => '2026-09-09',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 120000.00,
            'bank_account_id'   => $this->bankAccount->id,
        ];

        $response = $this->put(route('project.transactions.update', [$this->project->id, $tx->id]), $updateData);
        $response->assertRedirect(route('project.transactions.index', $this->project->id));

        $tx->refresh();
        $this->assertEquals('non_taxable', $tx->payment_category);
        $this->assertEquals('NEW-VCH-2', $tx->voucher_no);
        $this->assertFalse($tx->is_taxable_transaction);
        $this->assertEquals(120000.00, $tx->amount);
    }

    public function test_voucher_no_and_payment_category_appear_on_views(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/PMT1002',
            'voucher_no'             => 'VCH-VISIBLE-77',
            'voucher_date'           => '2026-09-08',
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'payment_category'       => 'taxable',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 75000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        // Index page
        $indexRes = $this->get(route('project.transactions.index', $this->project->id));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Vch: VCH-VISIBLE-77');
        $indexRes->assertSee('Payment Outflow (Taxable)');

        // Show page
        $showRes = $this->get(route('project.transactions.show', [$this->project->id, $tx->id]));
        $showRes->assertStatus(200);
        $showRes->assertSee('Voucher No: VCH-VISIBLE-77');
        $showRes->assertSee('TAXABLE');

        // Booking Show page
        $bkShowRes = $this->get(route('project.bookings.show', [$this->project->id, $this->booking->id]));
        $bkShowRes->assertStatus(200);
        $bkShowRes->assertSee('Vch: VCH-VISIBLE-77');
        $bkShowRes->assertSee('Payment (Taxable)');
    }

    public function test_creating_transaction_with_amount_greater_than_booking_amount_fails_validation(): void
    {
        $this->actingAs($this->user);

        // Booking total_booking_value is ₹42,00,000
        $this->assertEquals(4200000.00, (float)$this->booking->total_booking_value);

        // Attempt to create a transaction of ₹45,00,000 (exceeds booking amount)
        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-10',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 4500000.00,
            'bank_account_id'   => $this->bankAccount->id,
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);
        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseMissing('transactions', [
            'booking_id' => $this->booking->id,
            'amount'     => 4500000.00,
        ]);
    }

    public function test_creating_transaction_with_amount_equal_to_or_less_than_booking_amount_succeeds(): void
    {
        $this->actingAs($this->user);

        // Transaction of ₹42,00,000 (exact booking amount)
        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-10',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 4200000.00,
            'bank_account_id'   => $this->bankAccount->id,
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);
        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $this->booking->id]));
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $this->booking->id,
            'amount'     => 4200000.00,
        ]);
    }

    public function test_updating_transaction_with_amount_greater_than_booking_amount_fails_validation(): void
    {
        $this->actingAs($this->user);

        $tx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1099',
            'voucher_date'           => '2026-09-10',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'amount'                 => 500000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
            'created_by'             => $this->user->id,
        ]);

        // Attempt update with ₹50,00,000 (> ₹42,00,000)
        $updateData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-10',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 5000000.00,
            'bank_account_id'   => $this->bankAccount->id,
        ];

        $response = $this->put(route('project.transactions.update', [$this->project->id, $tx->id]), $updateData);
        $response->assertSessionHasErrors(['amount']);

        $tx->refresh();
        $this->assertEquals(500000.00, (float)$tx->amount);
    }
}
