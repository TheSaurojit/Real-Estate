<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCustomization;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\SaleAgreement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;
    protected Booking $liveBooking;
    protected Booking $cancelledBooking;
    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Company with auto number sequences
        $this->company = Company::create([
            'company_code'    => 'SSB',
            'name'            => 'S&S Builders Pvt Ltd',
            'contact_primary' => '+91-9876543210',
            'email'           => 'info@ssb.com',
            'address'         => 'Silchar, Assam',
            'pan_number'      => 'ABCDE1234F',
            'gstin'           => '18ABCDE1234F1Z5',
        ]);

        $autoNum = app(AutoNumberService::class);
        $autoNum->initializeCompanySequences($this->company->id, 'SSB');

        // 2. Setup Super Admin Role & User
        $superAdminRole = Role::create([
            'name'        => 'Super Admin',
            'slug'        => 'super_admin',
            'description' => 'System Owner',
            'is_system'   => true,
        ]);

        $this->user = User::create([
            'company_id'   => $this->company->id,
            'role_id'      => $superAdminRole->id,
            'user_code'    => 'User-1001',
            'name'         => 'Subhasish Das',
            'designation'  => 'Managing Director',
            'mobile'       => '+91-9876543210',
            'email'        => 's4subhasish@gmail.com',
            'password'     => bcrypt('password'),
            'role'         => 'super_admin',
        ]);

        // 3. Setup Project
        $this->project = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSB/PRJ-1001',
            'name'          => 'Imperial Grand Residency',
            'nick_name'     => 'Imperial Grand',
            'daag_no'       => '123/456',
            'patta_no'      => '789',
            'holding_no'    => 'H-42',
            'mouza'         => 'Silchar Part 1',
            'full_address'  => 'College Road, Silchar, Assam - 788001',
            'rera_category' => 'registered',
            'rera_reg_no'   => 'RERA-AS-2026-001',
            'is_active'     => true,
        ]);

        // 4. Setup Bank Account
        $this->bankAccount = BankAccount::create([
            'company_id'        => $this->company->id,
            'account_code'      => 'BANK-001',
            'account_nick_name' => 'HDFC Escrow A/C',
            'account_name'      => 'SSB Properties Escrow A/C',
            'bank_name'         => 'HDFC Bank',
            'account_number'    => '50200012345678',
            'ifsc_code'         => 'HDFC0001234',
            'branch'            => 'Silchar Main',
            'account_type'      => 'project_linked',
            'is_default'        => true,
            'is_active'         => true,
        ]);

        // 5. Setup Live Booking
        $this->liveBooking = Booking::create([
            'company_id'               => $this->company->id,
            'project_id'               => $this->project->id,
            'booking_code'             => 'SSB/BKG-2026-001',
            'booking_date'             => Carbon::now()->subDays(10),
            'customer_name'            => 'Rahul Sharma',
            'email_id'                 => 'rahul@example.com',
            'mobile_no'                => '9876543210',
            'pan_number'               => 'ABCPS1234K',
            'unit_no'                  => 'Flat 302',
            'floor_no'                 => '3rd Floor',
            'block_name'               => 'Tower A',
            'super_built_up_area'      => 1200,
            'rate_per_sqft'            => 4500,
            'unit_cost'                => 5400000,
            'parking_cost'             => 300000,
            'transformer_cost'         => 100000,
            'amenities_cost'           => 100000,
            'gross_total'              => 5900000,
            'discount_applied'         => 100000,
            'adjustments'              => 0,
            'consideration_value'      => 5800000,
            'taxable_agreement_value'  => 5800000,
            'tax_name'                 => 'GST',
            'tax_rate'                 => 5,
            'tax_amount'               => 290000,
            'gross_taxable_value'      => 6090000,
            'gross_cash_value'         => 0,
            'total_booking_value'      => 6090000,
            'final_booking_value'      => 6090000,
            'status'                   => 'live',
            'is_landowner_allocation'  => false,
        ]);

        // Add Customization Job Sheets to Live Booking
        BookingCustomization::create([
            'booking_id'      => $this->liveBooking->id,
            'particular'      => 'Electrical works',
            'job_type'        => 'addon',
            'description'     => 'Additional modular AC points in master bedroom',
            'quantity'        => 4,
            'unit_measure'    => 'Points',
            'material_rate'   => 1500,
            'labour_rate'     => 500,
            'material_total'  => 6000,
            'labour_total'    => 2000,
            'job_total'       => 8000,
            'approval_status' => 'approved',
        ]);

        BookingCustomization::create([
            'booking_id'      => $this->liveBooking->id,
            'particular'      => 'Plumbing works',
            'job_type'        => 'dislodge',
            'description'     => 'Omit standard developer fittings for client luxury fixtures',
            'quantity'        => 2,
            'unit_measure'    => 'Nos',
            'material_rate'   => 2000,
            'labour_rate'     => 500,
            'material_total'  => 4000,
            'labour_total'    => 1000,
            'job_total'       => 5000,
            'approval_status' => 'approved',
        ]);

        // Add Transactions to Live Booking
        // 1. Money Receipt (Taxable Banking)
        Transaction::create([
            'company_id'             => $this->company->id,
            'project_id'             => $this->project->id,
            'booking_id'             => $this->liveBooking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/TXN-2026-001',
            'voucher_type'           => 'money_receipt',
            'voucher_category'       => 'receipt',
            'payment_category'       => 'taxable',
            'voucher_no'             => 'MR-101',
            'voucher_date'           => Carbon::now()->subDays(5),
            'amount'                 => 1000000,
            'transaction_mode'       => 'neft',
            'source_of_payment'      => 'self',
            'is_taxable_transaction' => true,
            'instrument_ref_no'      => 'NEFT-889900',
            'instrument_status'      => 'cleared',
            'particulars'            => 'Booking advance paid by client',
        ]);

        // 2. Receipt Voucher (Non-Taxable Cash)
        Transaction::create([
            'company_id'             => $this->company->id,
            'project_id'             => $this->project->id,
            'booking_id'             => $this->liveBooking->id,
            'bank_account_id'        => null,
            'transaction_code'       => 'SSB/TXN-2026-002',
            'voucher_type'           => 'receipt_voucher',
            'voucher_category'       => 'receipt',
            'payment_category'       => 'non_taxable',
            'voucher_no'             => 'RV-202',
            'voucher_date'           => Carbon::now()->subDays(3),
            'amount'                 => 50000,
            'transaction_mode'       => 'cash',
            'source_of_payment'      => 'self',
            'is_taxable_transaction' => false,
            'instrument_status'      => 'cleared',
            'particulars'            => 'Cash receipt for documentation charges',
        ]);

        // 3. Payment Voucher / Refund
        Transaction::create([
            'company_id'             => $this->company->id,
            'project_id'             => $this->project->id,
            'booking_id'             => $this->liveBooking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/TXN-2026-003',
            'voucher_type'           => 'payment_voucher',
            'voucher_category'       => 'payment_refund',
            'payment_category'       => 'taxable',
            'voucher_no'             => 'PV-303',
            'voucher_date'           => Carbon::now()->subDays(1),
            'amount'                 => 20000,
            'transaction_mode'       => 'cheque',
            'source_of_payment'      => 'self',
            'is_taxable_transaction' => true,
            'instrument_ref_no'      => 'CHQ-112233',
            'instrument_status'      => 'cleared',
            'particulars'            => 'Customization adjustment refund to customer',
        ]);

        // 6. Setup Cancelled Booking
        $this->cancelledBooking = Booking::create([
            'company_id'               => $this->company->id,
            'project_id'               => $this->project->id,
            'booking_code'             => 'SSB/BKG-2026-002',
            'booking_date'             => Carbon::now()->subDays(30),
            'customer_name'            => 'Amit Patel',
            'email_id'                 => 'amit@example.com',
            'mobile_no'                => '9123456789',
            'pan_number'               => 'XYZPA5678Q',
            'unit_no'                  => 'Flat 405',
            'floor_no'                 => '4th Floor',
            'block_name'               => 'Tower B',
            'super_built_up_area'      => 1000,
            'rate_per_sqft'            => 4200,
            'unit_cost'                => 4200000,
            'parking_cost'             => 250000,
            'transformer_cost'         => 80000,
            'amenities_cost'           => 70000,
            'gross_total'              => 4600000,
            'discount_applied'         => 50000,
            'adjustments'              => 0,
            'consideration_value'      => 4550000,
            'taxable_agreement_value'  => 4550000,
            'tax_name'                 => 'GST',
            'tax_rate'                 => 5,
            'tax_amount'               => 227500,
            'gross_taxable_value'      => 4777500,
            'gross_cash_value'         => 0,
            'total_booking_value'      => 4777500,
            'final_booking_value'      => 4777500,
            'status'                   => 'cancelled',
            'is_landowner_allocation'  => false,
        ]);
    }

    public function test_user_can_view_reports_index_hub(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.index', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Section 2.3: Operational & Financial Audit Reports', false);
        $response->assertSee('Booking General Information');
        $response->assertSee('Booking Financial Breakdown');
        $response->assertSee('Dual-Ledger Receipt Report');
        $response->assertSee('Refund & Payment Outflow Report', false);
        $response->assertSee('Booking Customization Report');
        $response->assertSee('Quick Summary');
    }

    public function test_user_can_view_booking_general_report(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.bookings.general', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Booking General Information Report');
        $response->assertSee($this->liveBooking->customer_name);
        $response->assertSee($this->liveBooking->booking_code);
        $response->assertSee($this->liveBooking->customer_pan);
    }

    public function test_user_can_export_booking_general_report_to_excel(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.bookings.general', [
            'project' => $this->project->id,
            'export'  => 'excel',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', 'attachment'));
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', '.csv'));
    }

    public function test_user_can_view_booking_financial_breakdown_live_and_cancelled(): void
    {
        $this->actingAs($this->user);

        // Live bookings tab
        $liveResponse = $this->get(route('project.reports.bookings.financial', [
            'project' => $this->project->id,
            'status'  => 'live',
        ]));
        $liveResponse->assertStatus(200);
        $liveResponse->assertSee($this->liveBooking->customer_name);
        $liveResponse->assertDontSee($this->cancelledBooking->customer_name);

        // Cancelled bookings tab
        $cancelledResponse = $this->get(route('project.reports.bookings.financial', [
            'project' => $this->project->id,
            'status'  => 'cancelled',
        ]));
        $cancelledResponse->assertStatus(200);
        $cancelledResponse->assertSee($this->cancelledBooking->customer_name);
        $cancelledResponse->assertDontSee($this->liveBooking->customer_name);
    }

    public function test_user_can_export_booking_financial_report_to_excel(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.bookings.financial', [
            'project' => $this->project->id,
            'status'  => 'live',
            'export'  => 'excel',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', 'Bookings_Financial_Live_Report'));
    }

    public function test_user_can_view_receipt_report_and_filter(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.receipts', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Dual-Ledger Receipt Report');
        $response->assertSee('MR-101');
        $response->assertSee('RV-202');
        $response->assertSee('HDFC Escrow A/C');
        $response->assertSee('Cash in Hand');

        // Test filter by voucher_type = money_receipt
        $filteredResponse = $this->get(route('project.reports.receipts', [
            'project'      => $this->project->id,
            'voucher_type' => 'money_receipt',
        ]));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('MR-101');
        $filteredResponse->assertDontSee('RV-202');

        // Test export to excel
        $exportResponse = $this->get(route('project.reports.receipts', [
            'project' => $this->project->id,
            'export'  => 'excel',
        ]));
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('Content-Disposition') ?? '', 'Receipts_Report'));
    }

    public function test_user_can_view_refund_report_and_filter(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.refunds', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Refund & Payment Outflow Report', false);
        $response->assertSee('PV-303');
        $response->assertSee('Customization adjustment refund to customer');

        // Filter by payment_category = taxable
        $filteredResponse = $this->get(route('project.reports.refunds', [
            'project'          => $this->project->id,
            'payment_category' => 'taxable',
        ]));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('PV-303');

        // Export to excel
        $exportResponse = $this->get(route('project.reports.refunds', [
            'project' => $this->project->id,
            'export'  => 'excel',
        ]));
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('Content-Disposition') ?? '', 'Refunds_Report'));
    }

    public function test_user_can_view_customizations_report_and_filter_by_particular(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.customizations', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Booking Customization Report');
        $response->assertSee('Electrical works');
        $response->assertSee('Plumbing works');

        // Filter by particular = 'Electrical works'
        $filteredResponse = $this->get(route('project.reports.customizations', [
            'project'    => $this->project->id,
            'particular' => 'Electrical works',
        ]));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('Electrical works');
        $filteredResponse->assertDontSee('Plumbing works');

        // Export to excel
        $exportResponse = $this->get(route('project.reports.customizations', [
            'project' => $this->project->id,
            'export'  => 'excel',
        ]));
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('Content-Disposition') ?? '', 'Booking_Customizations_Report'));
    }

    public function test_user_can_view_quick_summary_report(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.reports.quick-summary', [
            'project'    => $this->project->id,
            'booking_id' => $this->liveBooking->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Quick Summary – Booking');
        $response->assertSee($this->liveBooking->customer_name);
        $response->assertSee('Recd :: Party (Taxable)');
        $response->assertSee('Recd :: Non-Taxable');
        $response->assertSee('Multi-Stream Financial Reconciliation Summary');
        $response->assertSee('Net Sales-Receipt');
        $response->assertSee('Balance Due');
    }
}
