<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCustomization;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;

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
    }

    public function test_user_can_view_project_bookings_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.bookings.index', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Project Flat');
        $response->assertSee('Bookings Master');
        $response->assertSee('Imperial Grand Residency');
    }

    public function test_user_can_create_booking_with_auto_generated_code_and_pricing_calculation(): void
    {
        $this->actingAs($this->user);

        // 1200 sqft @ ₹3000/sqft = ₹36,00,000 unit cost
        // Parking = ₹1,50,000, Transformer = ₹50,000, Amenities = ₹50,000 -> Gross = ₹38,50,000
        // Discount = ₹50,000 -> Consideration = ₹38,00,000
        // GST 5% = ₹1,90,000 -> Total Booking Value = ₹39,90,000
        $bookingData = [
            'booking_date'            => '2026-09-01',
            'is_landowner_allocation' => 0,
            'block_name'              => 'Tower A',
            'floor_no'                => '4th Floor',
            'unit_no'                 => 'Flat-4B',
            'built_up_area'           => 1000.00,
            'super_built_up_area'     => 1200.00,
            'property_type'           => 'Residential 3BHK Flat',
            'parking_type'            => 'private_covered',
            'parking_no'              => 'Bay-C12',
            'reference_source'        => 'Direct Inquiry',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Rajesh Sharma',
            'guardian_relation'       => 'son_of',
            'guardian_name'           => 'Kamal Sharma',
            'mobile_no'               => '+91-9876500001',
            'alt_mobile_no'           => '+91-9876500002',
            'email_id'                => 'rajesh@example.com',
            'address'                 => 'House 14, Silchar, Assam',
            'pan_number'              => 'ABCDE9876K',
            'gstin'                   => null,
            'photo_id_type'           => 'aadhaar',
            'photo_id_no'             => '1234-5678-9012',
            'rate_per_sqft'           => 3000.00,
            'parking_cost'            => 150000.00,
            'transformer_cost'        => 50000.00,
            'amenities_cost'          => 50000.00,
            'discount_applied'        => 50000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
        ];

        $response = $this->post(route('project.bookings.store', $this->project->id), $bookingData);

        $this->assertDatabaseHas('bookings', [
            'project_id'          => $this->project->id,
            'unit_no'             => 'Flat-4B',
            'customer_name'       => 'Rajesh Sharma',
            'rate_per_sqft'       => 3000.00,
            'unit_cost'           => 3600000.00,
            'gross_total'         => 3850000.00,
            'consideration_value' => 3800000.00,
            'tax_amount'          => 190000.00,
            'total_booking_value' => 3990000.00,
            'status'              => 'live',
        ]);

        $createdBooking = Booking::where('unit_no', 'Flat-4B')->first();
        $this->assertNotNull($createdBooking);
        $this->assertMatchesRegularExpression('/^SSB\/.*\/BID-\d{3}$/', $createdBooking->booking_code);
        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $createdBooking->id]));
    }

    public function test_customization_job_sheets_add_ons_and_dislodges_recalculate_booking_totals(): void
    {
        $this->actingAs($this->user);

        $booking = Booking::create([
            'project_id'              => $this->project->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/BK-1002',
            'booking_date'            => '2026-09-01',
            'unit_no'                 => 'Flat-2A',
            'built_up_area'           => 800,
            'super_built_up_area'     => 1000,
            'property_type'           => 'Residential 2BHK Flat',
            'parking_type'            => 'none',
            'customer_salutation'     => 'Dr.',
            'customer_name'           => 'Ananya Sen',
            'guardian_relation'       => 'daughter_of',
            'guardian_name'           => 'A. K. Sen',
            'mobile_no'               => '+91-9123456789',
            'photo_id_type'           => 'aadhaar',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 3000000.00,
            'gross_total'             => 3000000.00,
            'consideration_value'     => 3000000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 150000.00,
            'supplementary_value'     => 0,
            'total_booking_value'     => 3150000.00,
            'taxable_agreement_value' => 3000000.00,
            'taxable_gst_value'       => 150000.00,
            'gross_taxable_value'     => 3150000.00,
            'gross_cash_value'        => 0,
            'status'                  => 'live',
        ]);

        // 1. Add Add-on Item: 2 Extra AC points (Material: ₹25,000, Labor: ₹5,000) * 2 = ₹60,000
        $responseAddon = $this->post(route('project.bookings.jobsheets.store', [$this->project->id, $booking->id]), [
            'job_type'      => 'addon',
            'particular'    => 'Extra AC Copper Piping & Power Point',
            'description'   => 'Master bedroom and guest room AC line',
            'material_rate' => 25000.00,
            'labour_rate'   => 5000.00,
            'schedule_rate' => 0,
            'quantity'      => 2,
            'unit_measure'  => 'points',
        ]);

        $responseAddon->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        $this->assertEquals(60000.00, (float)$booking->supplementary_value);
        $this->assertEquals(3210000.00, (float)$booking->total_booking_value); // 31,50,000 + 60,000

        // 2. Add Dislodge Item: Floor tiles rejected by client (Material: ₹30,000, Labor: ₹10,000) * 1 = ₹40,000 deduction
        $responseDislodge = $this->post(route('project.bookings.jobsheets.store', [$this->project->id, $booking->id]), [
            'job_type'      => 'dislodge',
            'particular'    => 'Client arranging Italian Marble Flooring',
            'description'   => 'Builder standard 2x2 vitrified tiles deduction',
            'material_rate' => 30000.00,
            'labour_rate'   => 10000.00,
            'schedule_rate' => 0,
            'quantity'      => 1,
            'unit_measure'  => 'LS',
        ]);

        $responseDislodge->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        // Supplementary should now be +60,000 - 40,000 = +20,000
        $this->assertEquals(20000.00, (float)$booking->supplementary_value);
        $this->assertEquals(3170000.00, (float)$booking->total_booking_value); // 31,50,000 + 20,000

        // 3. Delete the Dislodge Item and verify recomputation
        $dislodgeItem = BookingCustomization::where('job_type', 'dislodge')->first();
        $responseDelete = $this->delete(route('project.bookings.jobsheets.destroy', [$this->project->id, $booking->id, $dislodgeItem->id]));
        $responseDelete->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        // After deleting dislodge, supplementary returns to +60,000
        $this->assertEquals(60000.00, (float)$booking->supplementary_value);
        $this->assertEquals(3210000.00, (float)$booking->total_booking_value);
    }

    public function test_sale_agreement_execution_calculates_dual_accounting_split(): void
    {
        $this->actingAs($this->user);

        // Booking with total value: Consideration (39,00,000 - 1,00,000 discount = 38,00,000) + GST (1,90,000) + Job Sheet (50,000) = ₹40,40,000
        $booking = Booking::create([
            'project_id'              => $this->project->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/BK-1003',
            'booking_date'            => '2026-09-01',
            'unit_no'                 => 'Flat-5A',
            'built_up_area'           => 1100,
            'super_built_up_area'     => 1300,
            'property_type'           => 'Penthouse / Duplex',
            'parking_type'            => 'private_covered',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Debashis Roy',
            'guardian_relation'       => 'son_of',
            'guardian_name'           => 'N. Roy',
            'mobile_no'               => '+91-9876540001',
            'photo_id_type'           => 'aadhaar',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 3900000.00,
            'gross_total'             => 3900000.00,
            'discount_applied'        => 100000.00,
            'consideration_value'     => 3800000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 190000.00,
            'supplementary_value'     => 50000.00,
            'total_booking_value'     => 4040000.00,
            'taxable_agreement_value' => 3800000.00,
            'taxable_gst_value'       => 190000.00,
            'gross_taxable_value'     => 3990000.00,
            'gross_cash_value'        => 50000.00,
            'status'                  => 'live',
        ]);

        // Add 50k customization row to match supplementary_value
        BookingCustomization::create([
            'booking_id'    => $booking->id,
            'job_type'      => 'addon',
            'particular'    => 'Balcony False Ceiling',
            'material_rate' => 40000.00,
            'labour_rate'   => 10000.00,
            'quantity'      => 1,
            'unit_measure'  => 'LS',
            'job_total'     => 50000.00,
        ]);

        // Developer and Client agree upon official Sale Agreement (Bayna-nama) value of ₹25,00,000
        $response = $this->put(route('project.bookings.sale-agreement.update', [$this->project->id, $booking->id]), [
            'agreement_status'    => 'executed',
            'agreement_serial_no' => 'AGR/2026/099',
            'execution_date'      => '2026-09-05',
            'agreement_value'     => 2500000.00,
            'tax_rate'            => 5.00,
            'document_details'    => 'Signed on ₹100 Non-Judicial Stamp Paper',
        ]);

        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        // Check dual accounting fields (Stage 2.1.4 standard formulas):
        // Agreement Value = ₹25,00,000
        // GST (5%) on Agreement Value = ₹1,25,000
        // Gross Taxable = ₹26,25,000
        // Gross Booking Value = Consideration (38,00,000) + GST (1,25,000) + Supplement (50,000) = ₹39,75,000
        // Final Booking Value = ₹39,75,000
        // Non-taxable Cash = Final Booking Value (39,75,000) - Gross Taxable (26,25,000) = ₹13,50,000
        $this->assertEquals('executed_agreement', $booking->status);
        $this->assertEquals(2500000.00, (float)$booking->taxable_agreement_value);
        $this->assertEquals(125000.00, (float)$booking->taxable_gst_value);
        $this->assertEquals(2625000.00, (float)$booking->gross_taxable_value);
        $this->assertEquals(1350000.00, (float)$booking->gross_cash_value);
    }

    public function test_bank_finance_and_sale_deed_updates(): void
    {
        $this->actingAs($this->user);

        $booking = Booking::create([
            'project_id'              => $this->project->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/BK-1004',
            'booking_date'            => '2026-09-01',
            'unit_no'                 => 'Shop-01',
            'built_up_area'           => 400,
            'super_built_up_area'     => 500,
            'property_type'           => 'Commercial Shop / Showroom',
            'parking_type'            => 'none',
            'customer_salutation'     => 'M/s',
            'customer_name'           => 'Apex Retail Enterprises',
            'guardian_relation'       => 'care_of',
            'guardian_name'           => 'Vikram Singha',
            'mobile_no'               => '+91-9988776655',
            'photo_id_type'           => 'pan',
            'rate_per_sqft'           => 5000.00,
            'unit_cost'               => 2500000.00,
            'gross_total'             => 2500000.00,
            'consideration_value'     => 2500000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 125000.00,
            'supplementary_value'     => 0,
            'total_booking_value'     => 2625000.00,
            'taxable_agreement_value' => 2500000.00,
            'taxable_gst_value'       => 125000.00,
            'gross_taxable_value'     => 2625000.00,
            'gross_cash_value'        => 0,
            'status'                  => 'live',
        ]);

        // 1. Update Bank Finance
        $responseLoan = $this->put(route('project.bookings.bank-finance.update', [$this->project->id, $booking->id]), [
            'finance_status'    => 'sanctioned',
            'bank_name'         => 'State Bank of India',
            'branch_name'       => 'Silchar Main',
            'loan_account_no'   => 'SBI-HL-778899',
            'sanctioned_amount' => 2000000.00,
            'disbursed_amount'  => 500000.00,
            'sanction_date'     => '2026-09-04',
            'remarks'           => 'Initial 25% disbursement approved',
        ]);

        $responseLoan->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));
        $this->assertDatabaseHas('bank_finances', [
            'booking_id'        => $booking->id,
            'bank_name'         => 'State Bank of India',
            'sanctioned_amount' => 2000000.00,
            'disbursed_amount'  => 500000.00,
            'finance_status'    => 'sanctioned',
        ]);

        // 2. Update Sale Deed
        $responseDeed = $this->put(route('project.bookings.sale-deed.update', [$this->project->id, $booking->id]), [
            'status'               => 'registered',
            'sale_deed_no'         => 'DEED-987/2026',
            'executed_date'        => '2026-09-08',
            'sub_registrar_office' => 'Senior Sub-Registrar Silchar',
            'remarks'              => 'Book No 1, Vol 25, Page 50-70',
        ]);

        $responseDeed->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));
        $this->assertDatabaseHas('sale_deeds', [
            'booking_id'   => $booking->id,
            'sale_deed_no' => 'DEED-987/2026',
            'status'       => 'registered',
        ]);

        $booking->refresh();
        $this->assertEquals('registered_deed', $booking->status);
    }

    public function test_booking_cancellation(): void
    {
        $this->actingAs($this->user);

        $booking = Booking::create([
            'project_id'              => $this->project->id,
            'company_id'              => $this->company->id,
            'booking_code'            => 'SSB/BK-1005',
            'booking_date'            => '2026-09-01',
            'unit_no'                 => 'Flat-1C',
            'built_up_area'           => 750,
            'super_built_up_area'     => 900,
            'property_type'           => 'Residential 2BHK Flat',
            'parking_type'            => 'none',
            'customer_salutation'     => 'Mrs.',
            'customer_name'           => 'Pooja Choudhury',
            'guardian_relation'       => 'wife_of',
            'guardian_name'           => 'Amit Choudhury',
            'mobile_no'               => '+91-9876543299',
            'photo_id_type'           => 'aadhaar',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 2700000.00,
            'gross_total'             => 2700000.00,
            'consideration_value'     => 2700000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 135000.00,
            'supplementary_value'     => 0,
            'total_booking_value'     => 2835000.00,
            'taxable_agreement_value' => 2700000.00,
            'taxable_gst_value'       => 135000.00,
            'gross_taxable_value'     => 2835000.00,
            'gross_cash_value'        => 0,
            'status'                  => 'live',
        ]);

        $response = $this->post(route('project.bookings.cancel', [$this->project->id, $booking->id]), [
            'cancellation_date'    => '2026-09-02',
            'cancellation_charge'  => 50000.00,
            'cancellation_remarks' => 'Purchaser cancelled due to personal relocation',
        ]);

        $response->assertRedirect(route('project.bookings.show', [$this->project->id, $booking->id]));

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('2026-09-02', $booking->cancellation_date->format('Y-m-d'));
        $this->assertEquals(50000.00, (float)$booking->cancellation_charge);
    }
}
