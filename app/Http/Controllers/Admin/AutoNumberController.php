<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoNumberSequence;
use App\Models\Company;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutoNumberController extends Controller
{
    public function __construct(protected AutoNumberService $autoNumberService)
    {
    }

    public function index(): View
    {
        $company = Company::first();
        $sequences = AutoNumberSequence::where('company_id', $company?->id)
            ->orWhereNull('company_id')
            ->get();

        // If any defaults are missing, load them
        $defaultTypes = ['bank_account', 'user', 'project', 'booking', 'receipt', 'payment', 'expense', 'supplier', 'stock_transfer'];
        foreach ($defaultTypes as $type) {
            if (!$sequences->contains('entity_type', $type)) {
                $defaults = AutoNumberService::getDefaultSettings($type, $company);
                $this->autoNumberService->configureSequence(
                    $type,
                    $defaults['prefix'],
                    $defaults['next_number'],
                    $defaults['padding'],
                    $company?->id,
                    $defaults['description']
                );
            }
        }

        $sequences = AutoNumberSequence::where('company_id', $company?->id)
            ->orWhereNull('company_id')
            ->orderBy('id')
            ->get();

        return view('admin.autonumber.index', compact('sequences', 'company'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sequences' => ['required', 'array'],
            'sequences.*.id' => ['required', 'exists:auto_number_sequences,id'],
            'sequences.*.prefix' => ['required', 'string', 'max:30'],
            'sequences.*.next_number' => ['required', 'integer', 'min:1'],
            'sequences.*.padding' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        foreach ($validated['sequences'] as $seqData) {
            $seq = AutoNumberSequence::findOrFail($seqData['id']);
            $seq->update([
                'prefix' => $seqData['prefix'],
                'next_number' => $seqData['next_number'],
                'padding' => $seqData['padding'],
            ]);
        }

        return back()->with('success', 'Auto-numbering rules updated successfully!');
    }
}
