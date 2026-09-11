<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCustomization;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobSheetController extends Controller
{
    /**
     * Store a new customization item (Add-on or Dislodge) in the Job Sheet
     */
    public function store(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'job_type'      => ['required', 'in:addon,dislodge'],
            'particular'    => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:500'],
            'material_rate' => ['required', 'numeric', 'min:0'],
            'labour_rate'   => ['required', 'numeric', 'min:0'],
            'schedule_rate' => ['nullable', 'numeric', 'min:0'],
            'quantity'      => ['required', 'numeric', 'min:0.01'],
            'unit_measure'  => ['required', 'string', 'max:30'],
        ]);

        $mat = (float)$validated['material_rate'];
        $lab = (float)$validated['labour_rate'];
        $sch = (float)($validated['schedule_rate'] ?? 0);
        $qty = (float)$validated['quantity'];
        $matTotal = round($mat * $qty, 2);
        $labTotal = round($lab * $qty, 2);
        $jobTotal = round(($mat + $lab + $sch) * $qty, 2);

        $booking->customizations()->create([
            'job_type'       => $validated['job_type'],
            'particular'     => $validated['particular'],
            'description'    => $validated['description'],
            'material_rate'  => $mat,
            'labour_rate'    => $lab,
            'schedule_rate'  => $sch,
            'quantity'       => $qty,
            'material_total' => $matTotal,
            'labour_total'   => $labTotal,
            'unit_measure'   => $validated['unit_measure'],
            'job_total'      => $jobTotal,
        ]);

        // Recalculate rollup totals on booking
        $booking->recalculateTotals();

        $typeLabel = $validated['job_type'] === 'addon' ? 'Add-on (+)' : 'Dislodge (-)';

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Job sheet item '{$validated['particular']}' [{$typeLabel}] added successfully!");
    }

    /**
     * Update an existing customization item in the Job Sheet
     */
    public function update(Request $request, Project $project, Booking $booking, BookingCustomization $customization): RedirectResponse
    {
        $validated = $request->validate([
            'job_type'      => ['required', 'in:addon,dislodge'],
            'particular'    => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:500'],
            'material_rate' => ['required', 'numeric', 'min:0'],
            'labour_rate'   => ['required', 'numeric', 'min:0'],
            'schedule_rate' => ['nullable', 'numeric', 'min:0'],
            'quantity'      => ['required', 'numeric', 'min:0.01'],
            'unit_measure'  => ['required', 'string', 'max:30'],
        ]);

        $mat = (float)$validated['material_rate'];
        $lab = (float)$validated['labour_rate'];
        $sch = (float)($validated['schedule_rate'] ?? 0);
        $qty = (float)$validated['quantity'];
        $matTotal = round($mat * $qty, 2);
        $labTotal = round($lab * $qty, 2);
        $jobTotal = round(($mat + $lab + $sch) * $qty, 2);

        $customization->update([
            'job_type'       => $validated['job_type'],
            'particular'     => $validated['particular'],
            'description'    => $validated['description'],
            'material_rate'  => $mat,
            'labour_rate'    => $lab,
            'schedule_rate'  => $sch,
            'quantity'       => $qty,
            'material_total' => $matTotal,
            'labour_total'   => $labTotal,
            'unit_measure'   => $validated['unit_measure'],
            'job_total'      => $jobTotal,
        ]);

        // Recalculate rollup totals on booking
        $booking->recalculateTotals();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Job sheet item '{$validated['particular']}' updated successfully!");
    }

    /**
     * Update Adjustments (additional discount) on the booking
     */
    public function updateAdjustments(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'adjustments' => ['required', 'numeric', 'min:0'],
        ]);

        $booking->adjustments = (float)$validated['adjustments'];
        $booking->recalculateTotals();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', 'Adjustments / Additional Discount updated successfully! Booking totals recalculated.');
    }

    /**
     * Remove a customization item from the Job Sheet
     */
    public function destroy(Project $project, Booking $booking, BookingCustomization $customization): RedirectResponse
    {
        $name = $customization->particular;
        $customization->delete();

        // Recalculate rollup totals on booking
        $booking->recalculateTotals();

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', "Job sheet item '{$name}' removed and booking balance updated!");
    }
}
