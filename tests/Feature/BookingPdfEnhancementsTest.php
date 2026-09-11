<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCustomization;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\SaleDeed;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPdfEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;
    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'company_code'    => 'SSB',
            'name'            => 'Shri Sharda Buildcon',
            'email'           => 'info@buildcon.example.com',
            'contact_primary' => '+91-9876543210',
            'address'         => 'Silchar, Assam',
            'pan_number'      => 'ABCDE1234F',
            'gstin'           => '18ABCDE1234F1Z5',
            'status'          => 'active',
        ]);

        $role = Role::create([
            'company_id'  => $this->company->id,
            'name'        => 'Super Admin',
            'slug'        => 'super_admin',
            'description' => 'Full Administrative Access',
            'is_system'   => true,
        ]);

        $permissions = Permission::all();
        $role->permissions()->attach($permissions->pluck('id'));

        $this->user = User::create([
            'company_id' => $this->company->id,
            'role_id'    => $role->id,
            'user_code'  => 'User-1001',
            'name'       => 'Admin User',
            'email'      => 'admin@buildcon.example.com',
            'password'   => bcrypt('password'),
            'role'       => 'super_admin',
            'status'     => 'active',
        ]);

        $this->project = Project::create([
            'company_id'    => $this->company->id,
            'name'          => 'Imperial Grand Residency',
            'nick_name'     => 'HL',
            'project_code'  => 'SSB/HL-101',
            'location'      => 'Meherpur',
            'city'          => 'Silchar',
            'state'         => 'Assam',
            'rera_reg_no'   => 'RERA-AS-2026-001',
            'is_active'     => true,
        ]);

        $this->bankAccount = BankAccount::create([
            'company_id'        => $this->company->id,
            'account_code'      => 'BANK-001',
            'account_nick_name' => 'SBI Project Account',
            'account_name'      => 'SSB SBI Project A/C',
            'bank_name'         => 'State Bank of India',
            'account_number'    => '334455667788',
            'ifsc_code'         => 'SBIN0000188',
            'branch'            => 'Silchar Main',
            'account_type'      => 'project_linked',
            'is_default'        => true,
            'is_active'         => true,
        ]);

        app(AutoNumberService::class)->initializeCompanySequences($this->company->id, $this->company->company_code);
    }

    /**
     * Stage 2.1.1: Booking ID follows <Company code>/<Project Nick name>/BID-<001>
     * and supports 5-tier parking dropdown
     */
    public function test_stage_2_1_1_booking_code_pattern_and_parking_options(): void
    {
        $this->actingAs($this->user);

        // Preview peek code
        $peekCode = app(AutoNumberService::class)->peekBookingCode($this->project);
        $this->assertEquals('SSB/HL/BID-001', $peekCode);

        // Store 1st booking with Open Dedicated Bay parking
        $response1 = $this->post(route('project.bookings.store', $this->project->id), [
            'booking_date'            => '2026-09-01',
            'is_landowner_allocation' => 0,
            'block_name'              => 'Tower A',
            'floor_no'                => '2nd Floor',
            'unit_no'                 => 'Flat-201',
            'built_up_area'           => 900.00,
            'super_built_up_area'     => 1100.00,
            'property_type'           => 'Residential 2BHK',
            'parking_type'            => 'open_dedicated_bay',
            'parking_no'              => 'OB-12',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Suman Nath',
            'guardian_relation'       => 'son_of',
            'guardian_name'           => 'R. Nath',
            'mobile_no'               => '+91-9876543201',
            'photo_id_type'           => 'aadhaar',
            'rate_per_sqft'           => 3000.00,
            'parking_cost'            => 100000.00,
            'transformer_cost'        => 50000.00,
            'amenities_cost'          => 50000.00,
            'discount_applied'        => 0.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
        ]);

        $booking1 = Booking::where('unit_no', 'Flat-201')->first();
        $this->assertNotNull($booking1);
        $this->assertEquals('SSB/HL/BID-001', $booking1->booking_code);
        $this->assertEquals('open_dedicated_bay', $booking1->parking_type);

        // Store 2nd booking -> increments to SSB/HL/BID-002 with Covered Garage
        $response2 = $this->post(route('project.bookings.store', $this->project->id), [
            'booking_date'            => '2026-09-02',
            'is_landowner_allocation' => 0,
            'block_name'              => 'Tower B',
            'floor_no'                => '3rd Floor',
            'unit_no'                 => 'Flat-302',
            'built_up_area'           => 1000.00,
            'super_built_up_area'     => 1200.00,
            'property_type'           => 'Residential 3BHK',
            'parking_type'            => 'covered_garage',
            'customer_salutation'     => 'Mrs.',
            'customer_name'           => 'Pooja Das',
            'guardian_relation'       => 'wife_of',
            'guardian_name'           => 'S. Das',
            'mobile_no'               => '+91-9876543202',
            'photo_id_type'           => 'pan',
            'rate_per_sqft'           => 3200.00,
            'parking_cost'            => 150000.00,
            'transformer_cost'        => 50000.00,
            'amenities_cost'          => 50000.00,
            'discount_applied'        => 0.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
        ]);

        $booking2 = Booking::where('unit_no', 'Flat-302')->first();
        $this->assertNotNull($booking2);
        $this->assertEquals('SSB/HL/BID-002', $booking2->booking_code);
        $this->assertEquals('covered_garage', $booking2->parking_type);
    }

    /**
     * Stage 2.1.4: Booking Customization line items, standard Particulars,
     * Material/Labour totals back-calculation, adjustments discount, and dual-accounting split
     */
    public function test_stage_2_1_4_customization_adjustments_and_dual_accounting(): void
    {
        $this->actingAs($this->user);

        // Create base booking: Consideration 30,00,000, Tax (5%) 1,50,000
        $booking = Booking::create([
            'project_id'              => $this->project->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/HL/BID-003',
            'booking_date'            => '2026-09-01',
            'unit_no'                 => 'Flat-101',
            'built_up_area'           => 900,
            'super_built_up_area'     => 1000,
            'property_type'           => 'Residential 2BHK',
            'parking_type'            => 'shared_parking',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Anirban Paul',
            'mobile_no'               => '+91-9876543203',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 3000000.00,
            'gross_total'             => 3000000.00,
            'consideration_value'     => 3000000.00,
            'tax_rate'                => 5.00,
            'tax_amount'              => 150000.00,
            'status'                  => 'live',
        ]);

        $booking->recalculateTotals();
        $this->assertEquals(3150000.00, (float)$booking->gross_booking_value);
        $this->assertEquals(3150000.00, (float)$booking->final_booking_value);

        // 1. Add Add-on customization item with standard Particular
        $responseAddon = $this->post(route('project.bookings.jobsheets.store', [$this->project->id, $booking->id]), [
            'job_type'      => 'addon',
            'particular'    => 'Electrical works',
            'description'   => 'Extra 16A points and mood LED strips',
            'material_rate' => 200.00,
            'labour_rate'   => 50.00,
            'schedule_rate' => 0.00,
            'quantity'      => 100.00,
            'unit_measure'  => 'points',
        ]);
        $responseAddon->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $addon = BookingCustomization::where('booking_id', $booking->id)->where('job_type', 'addon')->first();
        $this->assertNotNull($addon);
        $this->assertEquals('Electrical works', $addon->particular);
        $this->assertEquals(20000.00, (float)$addon->material_total); // 200 * 100
        $this->assertEquals(5000.00, (float)$addon->labour_total);    // 50 * 100
        $this->assertEquals(25000.00, (float)$addon->job_total);

        // 2. Add Dislodge customization item (Builder tile deduction)
        $responseDislodge = $this->post(route('project.bookings.jobsheets.store', [$this->project->id, $booking->id]), [
            'job_type'      => 'dislodge',
            'particular'    => 'Tile, marble & granite works',
            'description'   => 'Builder vitrified tiles deduction',
            'material_rate' => 80.00,
            'labour_rate'   => 20.00,
            'schedule_rate' => 0.00,
            'quantity'      => 100.00,
            'unit_measure'  => 'sq.ft.',
        ]);
        $responseDislodge->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $dislodge = BookingCustomization::where('booking_id', $booking->id)->where('job_type', 'dislodge')->first();
        $this->assertEquals(8000.00, (float)$dislodge->material_total);
        $this->assertEquals(2000.00, (float)$dislodge->labour_total);
        $this->assertEquals(10000.00, (float)$dislodge->job_total);

        $booking->refresh();
        // Supplementary value = 25000 - 10000 = +15000
        $this->assertEquals(15000.00, (float)$booking->supplementary_value);
        // Gross booking value = 30,00,000 + 1,50,000 + 15,000 = 31,65,000
        $this->assertEquals(3165000.00, (float)$booking->gross_booking_value);

        // 3. Edit customization item
        $responseEdit = $this->put(route('project.bookings.jobsheets.update', [$this->project->id, $booking->id, $addon->id]), [
            'job_type'      => 'addon',
            'particular'    => 'Electrical works',
            'description'   => 'Extra 16A points and mood LED strips revised',
            'material_rate' => 200.00,
            'labour_rate'   => 100.00, // increased labour to 100
            'schedule_rate' => 0.00,
            'quantity'      => 100.00,
            'unit_measure'  => 'points',
        ]);
        $responseEdit->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $addon->refresh();
        $this->assertEquals(10000.00, (float)$addon->labour_total);
        $this->assertEquals(30000.00, (float)$addon->job_total);

        // 4. Update Adjustments (discount) input
        $responseAdjust = $this->post(route('project.bookings.adjustments.update', [$this->project->id, $booking->id]), [
            'adjustments' => 25000.00,
        ]);
        $responseAdjust->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        $this->assertEquals(25000.00, (float)$booking->adjustments);
        // Supplementary = 30000 - 10000 = 20000
        // Gross booking value = 30,00,000 + 1,50,000 + 20,000 = 31,70,000
        $this->assertEquals(3170000.00, (float)$booking->gross_booking_value);
        // Final booking value = Gross (31,70,000) - Adjustments (25,000) = 31,45,000
        $this->assertEquals(3145000.00, (float)$booking->final_booking_value);
    }

    /**
     * Stage 2.1.5: Sale Deed information with executed/pending status,
     * sale deed value, and executed in location
     */
    public function test_stage_2_1_5_sale_deed_information(): void
    {
        $this->actingAs($this->user);

        $booking = Booking::create([
            'project_id'          => $this->project->id,
            'company_id'          => $this->company->id,
            'booking_code'        => 'SSB/HL/BID-004',
            'booking_date'        => '2026-09-01',
            'unit_no'             => 'Flat-501',
            'built_up_area'       => 1100,
            'super_built_up_area' => 1300,
            'property_type'       => 'Residential 3BHK',
            'parking_type'        => 'open_dedicated_parking',
            'customer_salutation' => 'Dr.',
            'customer_name'       => 'Pranab Sen',
            'mobile_no'           => '+91-9876543204',
            'rate_per_sqft'       => 3500.00,
            'unit_cost'           => 4550000.00,
            'gross_total'         => 4550000.00,
            'consideration_value' => 4550000.00,
            'tax_rate'            => 5.00,
            'tax_amount'          => 227500.00,
            'status'              => 'live',
        ]);

        // Update Sale Deed as Executed
        $response = $this->put(route('project.bookings.sale-deed.update', [$this->project->id, $booking->id]), [
            'status'          => 'executed',
            'sale_deed_no'    => 'DEED-Silchar-887/2026',
            'executed_date'   => '2026-09-07',
            'sale_deed_value' => 4550000.00,
            'executed_in'     => 'Senior Sub-Registrar Office, Silchar, Cachar',
            'remarks'         => 'Registered in Volume 12, Page 45',
        ]);

        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $deed = SaleDeed::where('booking_id', $booking->id)->first();
        $this->assertNotNull($deed);
        $this->assertEquals('executed', $deed->status);
        $this->assertEquals('DEED-Silchar-887/2026', $deed->sale_deed_no);
        $this->assertEquals(4550000.00, (float)$deed->sale_deed_value);
        $this->assertEquals('Senior Sub-Registrar Office, Silchar, Cachar', $deed->executed_in);
    }

    /**
     * Stage 2.1.6: Cancellation live/cancelled toggle with days gap,
     * 3-account table reconciliation, instruction >= refunded validation,
     * and reactivation safeguard when refund exists
     */
    public function test_stage_2_1_6_cancellation_and_safeguards(): void
    {
        $this->actingAs($this->user);

        $bookingDate = Carbon::parse('2026-08-01');
        $cancelDate = Carbon::parse('2026-09-01'); // 31 days later

        $booking = Booking::create([
            'project_id'          => $this->project->id,
            'company_id'          => $this->company->id,
            'booking_code'        => 'SSB/HL/BID-005',
            'booking_date'        => $bookingDate,
            'unit_no'             => 'Flat-601',
            'built_up_area'       => 1000,
            'super_built_up_area' => 1200,
            'property_type'       => 'Residential 3BHK',
            'parking_type'        => 'no_parking',
            'customer_salutation' => 'Mr.',
            'customer_name'       => 'Ramesh Choudhury',
            'mobile_no'           => '+91-9876543205',
            'rate_per_sqft'       => 3000.00,
            'unit_cost'           => 3600000.00,
            'gross_total'         => 3600000.00,
            'consideration_value' => 3600000.00,
            'tax_rate'            => 5.00,
            'tax_amount'          => 180000.00,
            'status'              => 'live',
        ]);

        // Prior receipts: Bank loan = ₹10,00,000, Self taxable = ₹5,00,000, Cash = ₹2,00,000
        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1001',
            'voucher_date'           => '2026-08-10',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'particulars'            => 'Disbursement from Bank Loan Account',
            'amount'                 => 1000000.00,
            'source_of_payment'      => 'through_loan_account',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1002',
            'voucher_date'           => '2026-08-12',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'cheque',
            'particulars'            => 'Party Self Taxable Cheque Payment',
            'amount'                 => 500000.00,
            'source_of_payment'      => 'self',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $booking->id,
            'bank_account_id'        => null,
            'transaction_code'       => 'SSB/RCD1003',
            'voucher_date'           => '2026-08-15',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'particulars'            => 'Cash receipt from party',
            'amount'                 => 200000.00,
            'source_of_payment'      => 'self',
            'instrument_status'      => 'not_applicable',
            'is_taxable_transaction' => false,
        ]);

        // 1. View Cancellation Show page
        $responseShow = $this->get(route('project.bookings.cancellation.show', [$this->project->id, $booking->id]));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('2.1.6. Booking Cancellation & Refund Reconciliation', false);

        // 2. Toggle Status to Cancelled
        $responseStatus = $this->post(route('project.bookings.cancellation.status', [$this->project->id, $booking->id]), [
            'status'            => 'cancelled',
            'cancellation_date' => '2026-09-01',
            'remarks'           => 'Cancellation requested due to personal issues',
        ]);
        $responseStatus->assertRedirect(route('project.bookings.cancellation.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals(31, $booking->cancellation_days_gap);

        // 3. Save Refund Instructions
        $responseInst = $this->post(route('project.bookings.cancellation.instructions', [$this->project->id, $booking->id]), [
            'loan_refund_instruction'    => 1000000.00, // Full loan refund
            'taxable_refund_instruction' => 450000.00,  // Deduct 50k fee
            'cash_refund_instruction'    => 200000.00,  // Full cash refund
            'remarks'                    => '₹50,000 deduction fee applied against taxable stream',
        ]);
        $responseInst->assertRedirect(route('project.bookings.cancellation.show', [$this->project->id, $booking->id]));

        $refund = $booking->cancellationRefund;
        $this->assertNotNull($refund);
        $this->assertEquals(1000000.00, (float)$refund->loan_refund_instruction);
        $this->assertEquals(450000.00, (float)$refund->taxable_refund_instruction);
        $this->assertEquals(200000.00, (float)$refund->cash_refund_instruction);
        $this->assertEquals(50000.00, (float)$refund->cancellation_fee);

        // 4. Execute Payout Voucher of ₹4,50,000 to party taxable
        $responsePayout = $this->post(route('project.bookings.cancellation.payout', [$this->project->id, $booking->id]), [
            'refund_target'   => 'party_taxable',
            'amount'          => 450000.00,
            'refund_date'     => '2026-09-05',
            'bank_account_id' => $this->bankAccount->id,
            'payment_mode'    => 'bank_transfer',
            'ref_no'          => 'NEFT-REF-450K',
            'remarks'         => 'Settled party taxable refund',
        ]);
        $responsePayout->assertRedirect(route('project.bookings.cancellation.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        $this->assertEquals(450000.00, $booking->taxable_self_refunded);
        $this->assertEquals(450000.00, $booking->total_refunded);

        // 5. Instruction Validation: Trying to reduce instruction below already refunded amount is rejected
        $responseInvalidInst = $this->from(route('project.bookings.cancellation.show', [$this->project->id, $booking->id]))
            ->post(route('project.bookings.cancellation.instructions', [$this->project->id, $booking->id]), [
                'loan_refund_instruction'    => 1000000.00,
                'taxable_refund_instruction' => 300000.00, // < 450000 refunded -> must fail
                'cash_refund_instruction'    => 200000.00,
            ]);
        $responseInvalidInst->assertSessionHasErrors(['taxable_refund_instruction']);

        // 6. Reactivation Safeguard: Trying to reactivate back to 'live' when total_refunded > 0 is strictly BLOCKED
        $responseReactivate = $this->from(route('project.bookings.cancellation.show', [$this->project->id, $booking->id]))
            ->post(route('project.bookings.cancellation.status', [$this->project->id, $booking->id]), [
                'status' => 'live',
            ]);
        $responseReactivate->assertSessionHasErrors(['status']);

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status); // Remains Cancelled!
    }
}
