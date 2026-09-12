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
    protected Transaction $moneyReceiptTxn;
    protected Transaction $cashReceiptTxn;
    protected Transaction $paymentTaxableTxn;
    protected Transaction $paymentNonTaxableTxn;

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
            'final_booking_value'     => 4350000.00,
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

        // 1. Money Receipt (Taxable Banking)
        $this->moneyReceiptTxn = Transaction::create([
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

        // 2. Receipt Voucher (Non-taxable Cash)
        $this->cashReceiptTxn = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/RCD1002',
            'voucher_date'           => '2026-09-03',
            'voucher_category'       => 'receipt',
            'voucher_type'           => 'receipt_voucher',
            'transaction_mode'       => 'cash',
            'amount'                 => 25000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => false,
        ]);

        // 3. Payment Voucher - Taxable
        $this->paymentTaxableTxn = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/PAY1001',
            'voucher_date'           => '2026-09-04',
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'transaction_mode'       => 'cheque',
            'payment_category'       => 'taxable',
            'amount'                 => 50000.00,
            'instrument_ref_no'      => 'CHQ-123456',
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => true,
        ]);

        // 4. Payment Voucher - Non-Taxable
        $this->paymentNonTaxableTxn = Transaction::create([
            'project_id'             => $this->project->id,
            'company_id'             => $this->company->id,
            'booking_id'             => $this->booking->id,
            'bank_account_id'        => $this->bankAccount->id,
            'transaction_code'       => 'SSB/PAY1002',
            'voucher_date'           => '2026-09-05',
            'voucher_category'       => 'payment_refund',
            'voucher_type'           => 'payment_voucher',
            'transaction_mode'       => 'cash',
            'payment_category'       => 'non_taxable',
            'amount'                 => 10000.00,
            'instrument_status'      => 'cleared',
            'is_taxable_transaction' => false,
        ]);
    }

    public function test_user_can_view_documents_hub_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.documents.index', $this->project->id));
        $response->assertStatus(200);
        $response->assertSee('Print-Ready Document');
        $response->assertSee('Section 2.4 Official Printable Documents (1 to 11)');

        // Assert all 11 official document titles are present
        $response->assertSee('Booking confirmation letter');
        $response->assertSee('Property allotment Letter');
        $response->assertSee('Demand Letter');
        $response->assertSee('Booking customization &amp; Final bill', false);
        $response->assertSee('No objection/no due certificate');
        $response->assertSee('Possession cum Key handover certificate');
        $response->assertSee('Booking Summary');
        $response->assertSee('Receipts');
        $response->assertSee('Payments');
        $response->assertSee('Proforma Invoice');
        $response->assertSee('Booking closing summary');

        // Sub-options in Receipts and Payments
        $response->assertSee('All Receipts');
        $response->assertSee('Money Receipts');
        $response->assertSee('Receipt Vouchers');
        $response->assertSee('Receipt by Transaction ID');
        $response->assertSee('All Payments');
        $response->assertSee('All – Taxable');
        $response->assertSee('All – Non Taxable');
        $response->assertSee('Payment by Transaction ID');
    }

    public function test_user_can_render_all_11_statutory_document_templates(): void
    {
        $this->actingAs($this->user);

        $officialDocumentTypes = [
            'booking_confirmation',
            'allotment_letter',
            'demand_letter',
            'final_bill',
            'noc_certificate',
            'possession_certificate',
            'booking_summary',
            'receipts',
            'payments',
            'proforma_invoice',
            'booking_closing_summary',
        ];

        foreach ($officialDocumentTypes as $docType) {
            $response = $this->get(route('project.documents.show', [$this->project->id, $this->booking->id, $docType]));
            $response->assertStatus(200);
            $response->assertSee($this->company->name);
            $response->assertSee('Flat-3B');
            $response->assertSee('Pranab Dutta');
        }
    }

    public function test_user_can_render_legacy_document_aliases_for_backwards_compatibility(): void
    {
        $this->actingAs($this->user);

        $legacyAliases = [
            'bank_noc',
            'possession_letter',
            'money_receipt',
            'cash_receipt_voucher',
            'payment_voucher',
            'customization_jobsheet',
            'customer_account_statement',
            'cancellation_deed',
        ];

        foreach ($legacyAliases as $alias) {
            $response = $this->get(route('project.documents.show', [$this->project->id, $this->booking->id, $alias]));
            $response->assertStatus(200);
            $response->assertSee($this->company->name);
            $response->assertSee('Flat-3B');
            $response->assertSee('Pranab Dutta');
        }
    }

    public function test_receipts_document_sub_modes_and_transaction_filtering(): void
    {
        $this->actingAs($this->user);

        // 1. All receipts consolidated
        $resAll = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'receipts',
            'mode' => 'all_receipts',
        ]));
        $resAll->assertStatus(200);
        $resAll->assertSee('SSB/RCD1001');
        $resAll->assertSee('SSB/RCD1002');

        // 2. All Money Receipts (Taxable Banking)
        $resMR = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'receipts',
            'mode' => 'all_money_receipts',
        ]));
        $resMR->assertStatus(200);
        $resMR->assertSee('SSB/RCD1001');
        $resMR->assertDontSee('SSB/RCD1002');

        // 3. All Receipt Vouchers (Non-Taxable Cash)
        $resRV = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'receipts',
            'mode' => 'all_receipt_vouchers',
        ]));
        $resRV->assertStatus(200);
        $resRV->assertSee('SSB/RCD1002');
        $resRV->assertDontSee('SSB/RCD1001');

        // 4. Single Receipt by Transaction ID
        $resSingle = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'receipts',
            'transaction_id' => $this->moneyReceiptTxn->id,
        ]));
        $resSingle->assertStatus(200);
        $resSingle->assertSee('SSB/RCD1001');
        $resSingle->assertSee('UTR-998877');
    }

    public function test_payments_document_sub_modes_and_transaction_filtering(): void
    {
        $this->actingAs($this->user);

        // 1. All payments consolidated
        $resAll = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'payments',
            'mode' => 'all_payments',
        ]));
        $resAll->assertStatus(200);
        $resAll->assertSee('SSB/PAY1001');
        $resAll->assertSee('SSB/PAY1002');

        // 2. All Taxable Payments
        $resTaxable = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'payments',
            'mode' => 'all_taxable',
        ]));
        $resTaxable->assertStatus(200);
        $resTaxable->assertSee('SSB/PAY1001');
        $resTaxable->assertDontSee('SSB/PAY1002');

        // 3. All Non-Taxable Payments
        $resNonTaxable = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'payments',
            'mode' => 'all_nontaxable',
        ]));
        $resNonTaxable->assertStatus(200);
        $resNonTaxable->assertSee('SSB/PAY1002');
        $resNonTaxable->assertDontSee('SSB/PAY1001');

        // 4. Single Payment by Transaction ID
        $resSingle = $this->get(route('project.documents.show', [
            $this->project->id,
            $this->booking->id,
            'payments',
            'transaction_id' => $this->paymentTaxableTxn->id,
        ]));
        $resSingle->assertStatus(200);
        $resSingle->assertSee('SSB/PAY1001');
        $resSingle->assertSee('CHQ-123456');
    }

    public function test_proforma_invoice_contains_sac_code_and_gst_details(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.documents.show', [$this->project->id, $this->booking->id, 'proforma_invoice']));
        $response->assertStatus(200);
        $response->assertSee('995411'); // SAC Code for Construction Services
        $response->assertSee('18AABCS1234D1Z5'); // Company GSTIN
        $response->assertSee('ABCDE1234F'); // Customer PAN
        $response->assertSee('PROFORMA INVOICE');
        $response->assertSee('CGST');
        $response->assertSee('SGST');
    }

    public function test_indian_currency_number_to_words_service(): void
    {
        // Tens of Thousands
        $this->assertEquals(
            'Rupees Forty Five Thousand Only',
            AutoNumberService::numberToIndianWords(45000)
        );

        // Lakhs
        $this->assertEquals(
            'Rupees Forty Three Lakh Fifty Thousand Only',
            AutoNumberService::numberToIndianWords(4350000)
        );

        // Crores
        $this->assertEquals(
            'Rupees One Crore Twenty Five Lakh Only',
            AutoNumberService::numberToIndianWords(12500000)
        );

        // Crores with Paise
        $this->assertEquals(
            'Rupees Two Crore Fifty Lakh Seven Hundred Fifty and Fifty Paise Only',
            AutoNumberService::numberToIndianWords(25000750.50)
        );
    }

    public function test_invalid_document_type_returns_404(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project.documents.show', [$this->project->id, $this->booking->id, 'invalid_non_existent_doc']));
        $response->assertStatus(404);
    }

    public function test_document_hub_and_all_print_documents_work_when_project_has_no_customers(): void
    {
        $this->actingAs($this->user);

        // Create a new project with 0 bookings/customers
        $emptyProject = Project::create([
            'company_id'    => $this->company->id,
            'project_code'  => 'SSB/PRJ-EMPTY',
            'name'          => 'Sunrise Residency',
            'nick_name'     => 'Sunrise',
            'full_address'  => 'Silchar, Assam - 788001',
            'is_active'     => true,
        ]);

        // 1. Check Document Hub Index displays disabled state & hides printing options
        $responseIndex = $this->get(route('project.documents.index', $emptyProject->id));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('No Customers / Bookings Registered in this Project Yet');
        $responseIndex->assertSee('Create Customer Booking');
        $responseIndex->assertSee('Printing Disabled (Requires Customer)');
        $responseIndex->assertSee('Requires Customer');
        $responseIndex->assertSee('Printing options locked - add customer to enable');
        $responseIndex->assertDontSee('Preview & Print');
        // 2. Direct printing without customer redirects to document hub with warning (no 404)
        $resBookingConfirmation = $this->get(route('project.documents.blank', [$emptyProject->id, 'booking_confirmation']));
        $resBookingConfirmation->assertRedirect(route('project.documents.index', $emptyProject->id));
        $resBookingConfirmation->assertSessionHas('warning');
        // 3. Fallback on invalid/0 booking ID also cleanly redirects with warning (no 404)
        $resFallback = $this->get(route('project.documents.show', [$emptyProject->id, 0, 'booking_confirmation']));
        $resFallback->assertRedirect(route('project.documents.index', $emptyProject->id));
        $resFallback->assertSessionHas('warning');
        // 4. Specimen preview mode can still be explicitly rendered with ?specimen=1
        $resSpecimen = $this->get(route('project.documents.blank', [$emptyProject->id, 'booking_confirmation']) . '?specimen=1');
        $resSpecimen->assertStatus(200);
        $resSpecimen->assertSee('Blank Specimen Template');
        $resSpecimen->assertSee('SPECIMEN');
        // 5. Verify that when project HAS a customer, printing options ARE shown
        $responseWithCustomer = $this->get(route('project.documents.index', $this->project->id));
        $responseWithCustomer->assertStatus(200);
        $responseWithCustomer->assertSee('Preview & Print');
        $responseWithCustomer->assertDontSee('Printing Disabled (Requires Customer)');
    }
}
