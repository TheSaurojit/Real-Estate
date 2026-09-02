<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Project;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseAndReportEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $projectA;
    protected Project $projectB;
    protected BankAccount $bankAccount;
    protected Supplier $supplier;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'     => 'admin@ssb.com',
            'user_code' => 'SSB/USR-1001',
        ]);

        $this->company = Company::create([
            'name'           => 'SSB Properties Pvt Ltd',
            'company_code'   => 'SSB',
            'pan_number'     => 'AABCS1234D',
            'gstin'          => '18AABCS1234D1Z5',
            'address'        => 'Central Road, Silchar, Assam - 788001',
            'contact_number' => '+91-3842-123456',
            'email'          => 'info@ssbproperties.com',
            'is_active'      => true,
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

        $this->supplier = Supplier::create([
            'company_id'    => $this->company->id,
            'supplier_code' => 'SSB/VEN-1001',
            'name'          => 'Assam Cement Corporation',
            'category'      => 'Cement & Concrete',
            'is_active'     => true,
        ]);

        $this->projectA = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSB/PRJ-1001',
            'name'          => 'Greenwood Luxury Enclave',
            'nick_name'     => 'Greenwood',
            'daag_no'       => '567/890',
            'patta_no'      => '123',
            'full_address'  => 'Silchar, Assam - 788005',
            'is_active'     => true,
        ]);

        $this->projectB = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSB/PRJ-1002',
            'name'          => 'Riverside Heights',
            'nick_name'     => 'Riverside',
            'daag_no'       => '112/334',
            'patta_no'      => '456',
            'full_address'  => 'Barak Valley, Assam',
            'is_active'     => true,
        ]);

        $this->booking = Booking::create([
            'project_id'              => $this->projectA->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/BK-1001',
            'booking_date'            => '2026-09-01',
            'block_name'              => 'Block A',
            'floor_no'                => '3rd Floor',
            'unit_no'                 => 'Flat-3B',
            'built_up_area'           => 1100,
            'super_built_up_area'     => 1300,
            'property_type'           => 'Residential 3BHK Flat',
            'parking_type'            => 'private_covered',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Pranab Dutta',
            'mobile_no'               => '+91-9876543210',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 3900000.00,
            'gross_total'             => 3900000.00,
            'consideration_value'     => 3900000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 195000.00,
            'supplementary_value'     => 0,
            'total_booking_value'     => 4095000.00,
            'taxable_agreement_value' => 3900000.00,
            'taxable_gst_value'       => 195000.00,
            'gross_taxable_value'     => 4095000.00,
            'gross_cash_value'        => 0,
            'status'                  => 'live',
        ]);

        Transaction::create([
            'project_id'             => $this->projectA->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1001',
            'voucher_date'           => '2026-09-02',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'amount'                 => 1000000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);
    }

    public function test_user_can_view_expenses_index_and_stats(): void
    {
        $this->actingAs($this->user);

        Expense::create([
            'project_id'       => $this->projectA->id,
            'company_id'       => $this->company->id,
            'expense_code'     => 'SSB/EXP-1001',
            'expense_date'     => '2026-09-02',
            'expense_category' => 'material_purchase',
            'item_name'        => 'UltraTech Cement 500 Bags',
            'quantity'         => 500,
            'unit_measure'     => 'Bags',
            'unit_rate'        => 400.00,
            'sub_total'        => 200000.00,
            'tax_rate'         => 18.00,
            'tax_amount'       => 36000.00,
            'gross_amount'     => 236000.00,
            'payment_mode'     => 'bank_transfer',
            'payment_status'   => 'paid',
        ]);

        $response = $this->get(route('project.expenses.index', $this->projectA->id));
        $response->assertStatus(200);
        $response->assertSee('SSB/EXP-1001');
        $response->assertSee('UltraTech Cement');
        $response->assertSee('236,000.00');
    }

    public function test_user_can_record_site_expense_with_auto_numbering_and_tax_calc(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('project.expenses.store', $this->projectA->id), [
            'expense_category' => 'material_purchase',
            'expense_date'     => '2026-09-02',
            'item_name'        => 'TMT Steel Rods 12mm',
            'supplier_id'      => $this->supplier->id,
            'quantity'         => 5,
            'unit_measure'     => 'Tonnes',
            'unit_rate'        => 60000.00,
            'tax_rate'         => 18.00,
            'invoice_no'       => 'INV-8899',
            'payment_mode'     => 'bank_transfer',
            'bank_account_id'  => $this->bankAccount->id,
            'payment_status'   => 'paid',
        ]);

        $response->assertRedirect(route('project.expenses.index', $this->projectA->id));

        $expense = Expense::where('project_id', $this->projectA->id)->where('item_name', 'TMT Steel Rods 12mm')->first();
        $this->assertNotNull($expense);
        $this->assertEquals('SSB/EXP-1001', $expense->expense_code);
        $this->assertEquals(300000.00, (float)$expense->sub_total);
        $this->assertEquals(54000.00, (float)$expense->tax_amount);
        $this->assertEquals(354000.00, (float)$expense->gross_amount);
    }

    public function test_user_can_record_inter_project_stock_transfer(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('project.stock-transfers.store', $this->projectA->id), [
            'destination_project_id' => $this->projectB->id,
            'transfer_date'          => '2026-09-02',
            'material_name'          => 'PPC Cement 100 Bags',
            'quantity'               => 100,
            'unit_measure'           => 'Bags',
            'unit_cost'              => 420.00,
            'vehicle_no'             => 'AS-11-CA-1234',
            'challan_no'             => 'CH-901',
            'remarks'                => 'Site emergency foundation pour',
        ]);

        $response->assertRedirect(route('project.stock-transfers.index', $this->projectA->id));

        $transfer = StockTransfer::where('source_project_id', $this->projectA->id)->first();
        $this->assertNotNull($transfer);
        $this->assertEquals('SSB/STK-1001', $transfer->transfer_code);
        $this->assertEquals(42000.00, (float)$transfer->total_transfer_value);
        $this->assertEquals($this->projectB->id, $transfer->destination_project_id);
    }

    public function test_user_can_view_executive_reports_hub_and_statements(): void
    {
        $this->actingAs($this->user);

        // 1. Hub Index
        $response = $this->get(route('project.reports.index', $this->projectA->id));
        $response->assertStatus(200);
        $response->assertSee('Project Profitability');
        $response->assertSee('Inventory Analytics');

        // 2. Profitability
        $response = $this->get(route('project.reports.profitability', $this->projectA->id));
        $response->assertStatus(200);
        $response->assertSee('4,095,000.00'); // Total Contract Revenue

        // 3. Inventory Velocity
        $response = $this->get(route('project.reports.inventory', $this->projectA->id));
        $response->assertStatus(200);
        $response->assertSee('1,300'); // Sold area

        // 4. Aging Matrix
        $response = $this->get(route('project.reports.aging', $this->projectA->id));
        $response->assertStatus(200);
        $response->assertSee('3,095,000.00'); // Due balance

        // 5. Banking Reconciliation
        $response = $this->get(route('project.reports.banking', $this->projectA->id));
        $response->assertStatus(200);
        $response->assertSee('HDFC Escrow Account');
    }
}
