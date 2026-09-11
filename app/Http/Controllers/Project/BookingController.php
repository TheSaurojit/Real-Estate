<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BankFinance;
use App\Models\Project;
use App\Models\SaleAgreement;
use App\Models\SaleDeed;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    /**
     * Display a listing of bookings for the active project
     */
    public function index(Request $request, Project $project): View
    {
        $query = $project->bookings()->with(['customizations', 'saleAgreement', 'bankFinance', 'saleDeed'])->latest('booking_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('booking_code', 'like', "%{$search}%")
                  ->orWhere('unit_no', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(15)->withQueryString();

        // Project Booking Stats
        $stats = [
            'total_bookings'       => $project->bookings()->count(),
            'active_bookings'      => $project->bookings()->whereIn('status', ['live', 'executed_agreement', 'registered_deed'])->count(),
            'total_consideration'  => (float)$project->bookings()->where('status', '!=', 'cancelled')->sum('consideration_value'),
            'total_supplementary'  => (float)$project->bookings()->where('status', '!=', 'cancelled')->sum('supplementary_value'),
            'total_booking_value'  => (float)$project->bookings()->where('status', '!=', 'cancelled')->sum('total_booking_value'),
            'cancelled_bookings'   => $project->bookings()->where('status', 'cancelled')->count(),
        ];

        return view('project.bookings.index', compact('project', 'bookings', 'stats'));
    }

    /**
     * Show the form for creating a new booking in this project
     */
    public function create(Project $project): View
    {
        $nextBookingCode = $this->autoNumberService->peekBookingCode($project);

        return view('project.bookings.create', compact('project', 'nextBookingCode'));
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            // Property Specs
            'booking_date'            => ['required', 'date'],
            'is_landowner_allocation' => ['boolean'],
            'block_name'              => ['nullable', 'string', 'max:50'],
            'floor_no'                => ['nullable', 'string', 'max:50'],
            'unit_no'                 => ['required', 'string', 'max:50'],
            'built_up_area'           => ['required', 'numeric', 'min:0'],
            'super_built_up_area'     => ['required', 'numeric', 'min:0'],
            'property_type'           => ['required', 'string', 'max:100'],
            'parking_type'            => ['required', 'string', 'in:none,no_parking,covered_garage,private_covered,open_dedicated_bay,private_open,shared,shared_parking,open_dedicated_parking'],
            'parking_no'              => ['nullable', 'string', 'max:50'],
            'reference_source'        => ['nullable', 'string', 'max:100'],

            // Customer Info
            'customer_salutation'     => ['required', 'string', 'max:20'],
            'customer_name'           => ['required', 'string', 'max:255'],
            'guardian_relation'       => ['required', 'in:son_of,daughter_of,wife_of,care_of'],
            'guardian_name'           => ['nullable', 'string', 'max:255'],
            'mobile_no'               => ['required', 'string', 'max:50'],
            'alt_mobile_no'           => ['nullable', 'string', 'max:50'],
            'email_id'                => ['nullable', 'email', 'max:255'],
            'address'                 => ['nullable', 'string'],
            'pan_number'              => ['nullable', 'string', 'max:20'],
            'gstin'                   => ['nullable', 'string', 'max:30'],
            'photo_id_type'           => ['required', 'in:aadhaar,passport,voter_id,driving_license,pan'],
            'photo_id_no'             => ['nullable', 'string', 'max:50'],

            // Pricing
            'rate_per_sqft'           => ['required', 'numeric', 'min:0'],
            'parking_cost'            => ['nullable', 'numeric', 'min:0'],
            'transformer_cost'        => ['nullable', 'numeric', 'min:0'],
            'amenities_cost'          => ['nullable', 'numeric', 'min:0'],
            'discount_applied'        => ['nullable', 'numeric', 'min:0'],
            'tax_name'                => ['required', 'string', 'max:50'],
            'tax_rate'                => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $companyId = $project->company_id;
        $bookingCode = $this->autoNumberService->generateBookingCode($project, true);

        // Pre-compute basic pricing
        $rate = (float)$validated['rate_per_sqft'];
        $sbArea = (float)$validated['super_built_up_area'];
        $unitCost = round($rate * $sbArea, 2);
        $parking = (float)($validated['parking_cost'] ?? 0);
        $transformer = (float)($validated['transformer_cost'] ?? 0);
        $amenities = (float)($validated['amenities_cost'] ?? 0);
        $grossTotal = round($unitCost + $parking + $transformer + $amenities, 2);
        $discount = (float)($validated['discount_applied'] ?? 0);
        $consideration = max(0, round($grossTotal - $discount, 2));
        $taxRate = (float)$validated['tax_rate'];
        $taxAmount = round(($consideration * $taxRate) / 100, 2);
        $totalBooking = round($consideration + $taxAmount, 2);

        $booking = Booking::create([
            'project_id'              => $project->id,
            'company_id'              => $companyId,
            'booking_code'            => $bookingCode,
            'booking_date'            => $validated['booking_date'],
            'is_landowner_allocation' => $request->boolean('is_landowner_allocation'),
            'block_name'              => $validated['block_name'] ?? null,
            'floor_no'                => $validated['floor_no'] ?? null,
            'unit_no'                 => $validated['unit_no'],
            'built_up_area'           => $validated['built_up_area'],
            'super_built_up_area'     => $validated['super_built_up_area'],
            'property_type'           => $validated['property_type'],
            'parking_type'            => $validated['parking_type'],
            'parking_no'              => $validated['parking_no'] ?? null,
            'reference_source'        => $validated['reference_source'] ?? null,
            'customer_salutation'     => $validated['customer_salutation'],
            'customer_name'           => $validated['customer_name'],
            'guardian_relation'       => $validated['guardian_relation'],
            'guardian_name'           => $validated['guardian_name'] ?? null,
            'mobile_no'               => $validated['mobile_no'],
            'alt_mobile_no'           => $validated['alt_mobile_no'] ?? null,
            'email_id'                => $validated['email_id'] ?? null,
            'address'                 => $validated['address'] ?? null,
            'pan_number'              => $validated['pan_number'] ?? null,
            'gstin'                   => $validated['gstin'] ?? null,
            'photo_id_type'           => $validated['photo_id_type'],
            'photo_id_no'             => $validated['photo_id_no'] ?? null,
            'rate_per_sqft'           => $rate,
            'unit_cost'               => $unitCost,
            'parking_cost'            => $parking,
            'transformer_cost'        => $transformer,
            'amenities_cost'          => $amenities,
            'gross_total'             => $grossTotal,
            'discount_applied'        => $discount,
            'consideration_value'     => $consideration,
            'tax_name'                => $validated['tax_name'],
            'tax_rate'                => $taxRate,
            'tax_amount'              => $taxAmount,
            'supplementary_value'     => 0,
            'total_booking_value'     => $totalBooking,
            'taxable_agreement_value' => $consideration,
            'taxable_gst_value'       => $taxAmount,
            'gross_taxable_value'     => $totalBooking,
            'gross_cash_value'        => 0,
            'status'                  => 'live',
            'created_by'              => auth()->id(),
        ]);

        // Create initial placeholder records for SaleAgreement, BankFinance, SaleDeed
        SaleAgreement::create([
            'booking_id'          => $booking->id,
            'agreement_status'    => 'pending',
            'agreement_value'     => $consideration,
            'tax_rate'            => $taxRate,
            'tax_value'           => $taxAmount,
            'gross_taxable_value' => $totalBooking,
            'cash_value'          => 0,
        ]);

        BankFinance::create([
            'booking_id'     => $booking->id,
            'finance_status' => 'not_applicable',
        ]);

        SaleDeed::create([
            'booking_id' => $booking->id,
            'status'     => 'pending',
        ]);

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('created_booking', [
                'code'     => $booking->booking_code,
                'customer' => $booking->customer_name,
                'unit'     => $booking->unit_no,
                'total'    => number_format($booking->total_booking_value, 2),
            ])
            ->with('success', "Booking created successfully! Booking ID: {$booking->booking_code}");
    }

    /**
     * Display 360° booking details, customizations, sale agreement, home loan, and deeds
     */
    public function show(Project $project, Booking $booking): View
    {
        $booking->load(['customizations', 'saleAgreement', 'bankFinance', 'saleDeed', 'company']);
        return view('project.bookings.show', compact('project', 'booking'));
    }

    /**
     * Show form for editing booking details
     */
    public function edit(Project $project, Booking $booking): View
    {
        return view('project.bookings.edit', compact('project', 'booking'));
    }

    /**
     * Update booking details
     */
    public function update(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            // Property Specs
            'booking_date'            => ['required', 'date'],
            'is_landowner_allocation' => ['boolean'],
            'block_name'              => ['nullable', 'string', 'max:50'],
            'floor_no'                => ['nullable', 'string', 'max:50'],
            'unit_no'                 => ['required', 'string', 'max:50'],
            'built_up_area'           => ['required', 'numeric', 'min:0'],
            'super_built_up_area'     => ['required', 'numeric', 'min:0'],
            'property_type'           => ['required', 'string', 'max:100'],
            'parking_type'            => ['required', 'string', 'in:none,no_parking,covered_garage,private_covered,open_dedicated_bay,private_open,shared,shared_parking,open_dedicated_parking'],
            'parking_no'              => ['nullable', 'string', 'max:50'],
            'reference_source'        => ['nullable', 'string', 'max:100'],

            // Customer Info
            'customer_salutation'     => ['required', 'string', 'max:20'],
            'customer_name'           => ['required', 'string', 'max:255'],
            'guardian_relation'       => ['required', 'in:son_of,daughter_of,wife_of,care_of'],
            'guardian_name'           => ['nullable', 'string', 'max:255'],
            'mobile_no'               => ['required', 'string', 'max:50'],
            'alt_mobile_no'           => ['nullable', 'string', 'max:50'],
            'email_id'                => ['nullable', 'email', 'max:255'],
            'address'                 => ['nullable', 'string'],
            'pan_number'              => ['nullable', 'string', 'max:20'],
            'gstin'                   => ['nullable', 'string', 'max:30'],
            'photo_id_type'           => ['required', 'in:aadhaar,passport,voter_id,driving_license,pan'],
            'photo_id_no'             => ['nullable', 'string', 'max:50'],

            // Pricing
            'rate_per_sqft'           => ['required', 'numeric', 'min:0'],
            'parking_cost'            => ['nullable', 'numeric', 'min:0'],
            'transformer_cost'        => ['nullable', 'numeric', 'min:0'],
            'amenities_cost'          => ['nullable', 'numeric', 'min:0'],
            'discount_applied'        => ['nullable', 'numeric', 'min:0'],
            'tax_name'                => ['required', 'string', 'max:50'],
            'tax_rate'                => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $validated['is_landowner_allocation'] = $request->boolean('is_landowner_allocation');

        $booking->update($validated);
        $booking->recalculateTotals();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', 'Booking details updated successfully!');
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_date'    => ['required', 'date'],
            'cancellation_charge'  => ['required', 'numeric', 'min:0'],
            'cancellation_remarks' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'status'               => 'cancelled',
            'cancellation_date'    => $validated['cancellation_date'],
            'cancellation_charge'  => $validated['cancellation_charge'],
            'cancellation_remarks' => $validated['cancellation_remarks'],
        ]);

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Booking {$booking->booking_code} has been marked as Cancelled.");
    }

    /**
     * Delete booking (with safeguards)
     */
    public function destroy(Project $project, Booking $booking): RedirectResponse
    {
        $booking->customizations()->delete();
        $booking->saleAgreement()?->delete();
        $booking->bankFinance()?->delete();
        $booking->saleDeed()?->delete();
        $booking->delete();

        return redirect()->route('project.bookings.index', $project->id)
            ->with('success', "Booking record {$booking->booking_code} deleted successfully.");
    }
}
