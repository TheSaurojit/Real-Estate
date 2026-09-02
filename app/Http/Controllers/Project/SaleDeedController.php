<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Project;
use App\Models\SaleDeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleDeedController extends Controller
{
    /**
     * Update/Register Sale Deed details
     */
    public function update(Request $request, Project $project, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status'                => ['required', 'in:pending,registered'],
            'sale_deed_no'          => ['nullable', 'string', 'max:100'],
            'executed_date'         => ['nullable', 'date'],
            'sub_registrar_office'  => ['nullable', 'string', 'max:150'],
            'remarks'               => ['nullable', 'string', 'max:1000'],
        ]);

        $saleDeed = $booking->saleDeed ?? new SaleDeed(['booking_id' => $booking->id]);

        $saleDeed->fill([
            'status'               => $validated['status'],
            'sale_deed_no'         => $validated['sale_deed_no'],
            'executed_date'        => $validated['executed_date'],
            'sub_registrar_office' => $validated['sub_registrar_office'],
            'remarks'              => $validated['remarks'],
        ]);
        $saleDeed->save();

        if ($validated['status'] === 'registered' && $booking->status !== 'cancelled') {
            $booking->status = 'registered_deed';
            $booking->save();
        }

        return redirect()->route('project.bookings.show', [$project->id, $booking->id])
            ->with('success', 'Sale Deed & Registration records updated successfully!');
    }
}
