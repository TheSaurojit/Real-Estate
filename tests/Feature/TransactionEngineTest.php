<?php

namespace Tests\Feature;

use App\Models\AutoNumberSequence;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCancellationRefund;
use App\Models\Company;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionEngineTest extends TestCase
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

        // Create initial booking with:
        // Consideration: ₹38,00,000 + GST (5%): ₹1,90,000 = ₹39,90,000 (Taxable Agreement)
        // Supplementary / Cash: ₹2,10,000
        // Total Booking Value: ₹42,00,000
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

    public function test_user_can_view_transactions_ledger_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.transactions.index', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Dual-Ledger Financial Transactions');
        $response->assertSee('Greenwood Luxury Enclave');
    }

    public function test_user_can_create_money_receipt_with_auto_generated_rcd_code_and_credit_taxable_stream(): void
    {
        $this->actingAs($this->user);

        // Record a Money Receipt of ₹10,00,000 paid via NEFT into HDFC Escrow
        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'money_receipt',
            'voucher_date'      => '2026-09-02',
            'transaction_mode'  => 'neft',
            'source_of_payment' => 'self',
            'amount'            => 1000000.00,
            'bank_account_id'   => $this->bankAccount->id,
            'instrument_ref_no' => 'UTR-HDFC-99887766',
            'instrument_date'   => '2026-09-02',
            'issuing_bank'      => 'State Bank of India',
            'issuing_branch'    => 'Silchar Main',
            'particulars'       => 'Initial booking token advance',
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);

        $this->assertDatabaseHas('transactions', [
            'project_id'             => $this->project->id,
            'booking_id'             => $this->booking->id,
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'amount'                 => 1000000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        $createdTx = Transaction::where('booking_id', $this->booking->id)->first();
        $this->assertNotNull($createdTx);
        $this->assertStringStartsWith('SSB/RCD', $createdTx->transaction_code);

        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $this->booking->id]));
    }

    public function test_user_can_create_cash_receipt_voucher_and_credit_cash_stream(): void
    {
        $this->actingAs($this->user);

        // Record Cash Receipt Voucher of ₹50,000 for customization alterations
        $postData = [
            'booking_id'        => $this->booking->id,
            'voucher_type'      => 'receipt_voucher',
            'voucher_date'      => '2026-09-02',
            'transaction_mode'  => 'cash',
            'source_of_payment' => 'self',
            'amount'            => 50000.00,
            'bank_account_id'   => null, // Cash in hand
            'particulars'       => 'Cash advance for balcony modifications',
        ];

        $response = $this->post(route('project.transactions.store', $this->project->id), $postData);

        $this->assertDatabaseHas('transactions', [
            'project_id'             => $this->project->id,
            'booking_id'             => $this->booking->id,
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'amount'                 => 50000.00,
            'instrument_status'      => 'not_applicable',
            'is_taxable_transaction' => false,
        ]);

        $this->booking->refresh();
        $this->assertEquals(50000.00, $this->booking->total_cash_received);
        $this->assertEquals(160000.00, $this->booking->cash_due_balance); // 2,10,000 - 50,000
    }

    public function test_live_customer_due_balance_calculations_across_bank_and_cash_ledgers(): void
    {
        // 1. Pay ₹20,00,000 in Money Receipts (Bank)
        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1001',
            'voucher_date'           => '2026-09-01',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'amount'                 => 2000000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        // 2. Pay ₹1,00,000 in Receipt Vouchers (Cash)
        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => null,
            'transaction_code'       => 'SSB/RCD1002',
            'voucher_date'           => '2026-09-01',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'amount'                 => 100000.00,
            'instrument_status'      => 'not_applicable',
            'is_taxable_transaction' => false,
        ]);

        $this->booking->refresh();

        // Gross Taxable = ₹39,90,000, Received Bank = ₹20,00,000 -> Taxable Due = ₹19,90,000
        $this->assertEquals(2000000.00, $this->booking->total_taxable_received);
        $this->assertEquals(1990000.00, $this->booking->taxable_due_balance);

        // Gross Cash = ₹2,10,000, Received Cash = ₹1,00,000 -> Cash Due = ₹1,10,000
        $this->assertEquals(100000.00, $this->booking->total_cash_received);
        $this->assertEquals(110000.00, $this->booking->cash_due_balance);

        // Net Outstanding Due = 19,90,000 + 1,10,000 = ₹21,00,000
        $this->assertEquals(2100000.00, $this->booking->total_outstanding_due);
    }

    public function test_marking_cheque_as_dishonored_reverses_balance_and_applies_penalty_fee(): void
    {
        $this->actingAs($this->user);

        // Customer gives a cheque of ₹5,00,000 (Pending Clearance)
        $chqTx = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1003',
            'voucher_date'           => '2026-09-01',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'cheque',
            'amount'                 => 500000.00,
            'instrument_ref_no'      => 'CHQ-888999',
            'instrument_status'      => 'pending_clearance',
            'is_taxable_transaction' => true,
        ]);

        // Cheque is returned unpaid / bounced by bank
        $response = $this->put(route('project.transactions.status.update', [$this->project->id, $chqTx->id]), [
            'instrument_status'       => 'dishonored',
            'dishonor_penalty_amount' => 500.00,
            'dishonor_date'           => '2026-09-03',
            'dishonor_remarks'        => 'Insufficient funds in customer drawer account',
        ]);

        $response->assertRedirect();

        $chqTx->refresh();
        $this->assertEquals('dishonored', $chqTx->instrument_status);
        $this->assertEquals(500.00, (float)$chqTx->dishonor_penalty_amount);

        $this->booking->refresh();
        // Since cheque is dishonored, total_taxable_received is 0 (not credited)
        $this->assertEquals(0.00, $this->booking->total_taxable_received);
        $this->assertEquals(500.00, $this->booking->total_dishonor_penalties);
        // Total Outstanding Due = 39,90,000 + 2,10,000 + 500 = ₹42,00,500
        $this->assertEquals(4200500.00, $this->booking->total_outstanding_due);
    }

    public function test_cross_ledger_overpayment_adjustment_wizard_rebalances_bank_excess_to_cash(): void
    {
        $this->actingAs($this->user);

        // Suppose client deposited ₹45,00,000 into Bank Account
        // But Taxable Target is only ₹39,90,000 -> Bank Excess is ₹5,10,000
        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1004',
            'voucher_date'           => '2026-09-01',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'amount'                 => 4500000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        // Execute Cross-Ledger Rebalance of ₹2,10,000 from Bank to Cash
        $response = $this->post(route('project.bookings.adjustment.store', [$this->project->id, $this->booking->id]), [
            'adjustment_amount' => 210000.00,
            'bank_account_id'   => $this->bankAccount->id,
            'adjustment_date'   => '2026-09-03',
            'remarks'           => 'Rebalance excess bank collection to cash ledger',
        ]);

        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $this->booking->id]));

        // Check Payment Voucher (Outflow from Bank) was created
        $this->assertDatabaseHas('transactions', [
            'booking_id'             => $this->booking->id,
            'voucher_type'           => 'adjustment_voucher',
            'voucher_category'       => 'adjustment',
            'amount'                 => 210000.00,
            'is_taxable_transaction' => true,
        ]);

        // Check Receipt Voucher (Inflow to Cash) was created
        $this->assertDatabaseHas('transactions', [
            'booking_id'             => $this->booking->id,
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'amount'                 => 210000.00,
            'is_taxable_transaction' => false,
        ]);

        $this->booking->refresh();
        $this->assertEquals(210000.00, $this->booking->total_cash_received);
        $this->assertEquals(0.00, $this->booking->cash_due_balance); // Cash balance fully settled!
    }

    public function test_booking_cancellation_split_refund_processing(): void
    {
        $this->actingAs($this->user);

        // Cancel booking with ₹1,00,000 cancellation charge
        $this->booking->update([
            'status'              => 'cancelled',
            'cancellation_date'   => '2026-09-03',
            'cancellation_charge' => 100000.00,
            'cancellation_reason' => 'Client relocated abroad',
        ]);

        // Prior receipts: Bank = ₹15,00,000, Cash = ₹1,00,000
        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1005',
            'voucher_date'           => '2026-09-01',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'amount'                 => 1500000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => null,
            'transaction_code'       => 'SSB/RCD1006',
            'voucher_date'           => '2026-09-01',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'amount'                 => 100000.00,
            'instrument_status'      => 'not_applicable',
            'is_taxable_transaction' => false,
        ]);

        // Process a refund payout of ₹5,00,000 to customer bank
        $response = $this->post(route('project.bookings.cancellation-refund.store', [$this->project->id, $this->booking->id]), [
            'refund_target'   => 'party_taxable',
            'amount'          => 500000.00,
            'refund_date'     => '2026-09-04',
            'bank_account_id' => $this->bankAccount->id,
            'payment_mode'    => 'bank_transfer',
            'ref_no'          => 'REF-998811',
            'remarks'         => 'First installment refund',
        ]);

        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $this->booking->id]));

        $this->assertDatabaseHas('booking_cancellation_refunds', [
            'booking_id'                => $this->booking->id,
            'refunded_to_party_taxable' => 500000.00,
            'status'                    => 'partially_refunded',
        ]);

        $this->assertDatabaseHas('transactions', [
            'booking_id'             => $this->booking->id,
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'amount'                 => 500000.00,
            'is_taxable_transaction' => true,
        ]);
    }
}
