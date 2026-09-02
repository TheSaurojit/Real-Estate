<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\BookingCustomization;
use App\Models\Company;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AutoNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentEngineTest extends TestCase
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

        $this->project = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSB/PRJ-1001',
            'name'          => 'Greenwood Luxury Enclave',
            'nick_name'     => 'Greenwood',
            'daag_no'       => '567/890',
            'patta_no'      => '123',
            'holding_no'    => 'H-99',
            'mouza'         => 'Meherpur',
            'full_address'  => 'Silchar, Assam - 788005',
            'rera_category' => 'registered',
            'rera_reg_no'   => 'RERA-AS-2026-999',
            'is_active'     => true,
        ]);

        $this->booking = Booking::create([
            'project_id'              => $this->project->id,
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
            'parking_no'              => 'Bay-C10',
            'customer_salutation'     => 'Mr.',
            'customer_name'           => 'Pranab Dutta',
            'guardian_relation'       => 'son_of',
            'guardian_name'           => 'R. Dutta',
            'mobile_no'               => '+91-9876543210',
            'pan_number'              => 'ABCDE1234F',
            'rate_per_sqft'           => 3000.00,
            'unit_cost'               => 3900000.00,
            'parking_cost'            => 150000.00,
            'transformer_cost'        => 50000.00,
            'amenities_cost'          => 50000.00,
            'discount_applied'        => 50000.00,
            'gross_total'             => 4150000.00,
            'consideration_value'     => 4100000.00,
            'tax_name'                => 'GST - 5.00%',
            'tax_rate'                => 5.00,
            'tax_amount'              => 205000.00,
            'supplementary_value'     => 45000.00,
            'total_booking_value'     => 4350000.00,
            'taxable_agreement_value' => 4100000.00,
            'taxable_gst_value'       => 205000.00,
            'gross_taxable_value'     => 4305000.00,
            'gross_cash_value'        => 45000.00,
            'status'                  => 'live',
        ]);

        BookingCustomization::create([
            'booking_id'    => $this->booking->id,
            'job_type'      => 'addon',
            'particular'    => 'Kitchen Granite Countertop Extended',
            'material_rate' => 35000.00,
            'labour_rate'   => 10000.00,
            'quantity'      => 1,
            'unit_measure'  => 'LS',
            'job_total'     => 45000.00,
        ]);

        Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1001',
            'voucher_date'           => '2026-09-02',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'money_receipt',
            'transaction_mode'       => 'neft',
            'amount'                 => 1000000.00,
            'instrument_ref_no'      => 'UTR-998877',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);
    }

    public function test_user_can_view_documents_hub_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.documents.index', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Print-Ready Document');
        $response->assertSee('Voucher Hub');
        $response->assertSee('Booking Confirmation Letter');
        $response->assertSee('Property Allotment Letter');
        $response->assertSee('Demand Notice');
        $response->assertSee('Final Bill');
    }

    public function test_user_can_render_all_12_document_templates_successfully(): void
    {
        $this->actingAs($this->user);

        $documentTypes = [
            'booking_confirmation',
            'allotment_letter',
            'demand_letter',
            'final_bill',
            'bank_noc',
            'possession_letter',
            'money_receipt',
            'cash_receipt_voucher',
            'payment_voucher',
            'customization_jobsheet',
            'customer_account_statement',
            'cancellation_deed',
        ];

        foreach ($documentTypes as $docType) {
            $response = $this->get(route('project.documents.show', [$this->project->id, $this->booking->id, $docType]));
            $response->assertStatus(200);
            $response->assertSee($this->company->name);
            $response->assertSee('Flat-3B');
            $response->assertSee('Pranab Dutta');
        }
    }

    public function test_invalid_document_type_returns_404(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.documents.show', [$this->project->id, $this->booking->id, 'invalid_non_existent_doc']));
        $response->assertStatus(404);
    }
}
