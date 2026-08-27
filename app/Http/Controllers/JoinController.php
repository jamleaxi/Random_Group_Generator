<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinBatchRequest;
use App\Models\Batch;
use App\Services\GroupRandomizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JoinController extends Controller
{
    /**
     * Show the public form for joining a batch by its share link.
     */
    public function show(Batch $batch): View
    {
        abort_unless($batch->isOpenForSubmissions(), 404);

        return view('join.show', ['batch' => $batch]);
    }

    /**
     * Submit a name to the batch and immediately assign it to a team.
     */
    public function store(JoinBatchRequest $request, Batch $batch): RedirectResponse|View
    {
        abort_unless($batch->isOpenForSubmissions(), 404);

        $fullName = $request->fullName();

        $duplicate = $batch->participants()
            ->whereRaw('lower(name) = ?', [mb_strtolower($fullName)])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'first_name' => 'This name has already been submitted for this batch.',
            ]);
        }

        $randomizer = app(GroupRandomizer::class);

        $participant = $randomizer->assignOne($batch, $fullName, $request->genderValue(), [
            'last_name' => $request->string('last_name')->trim()->value(),
            'first_name' => $request->string('first_name')->trim()->value(),
            'middle_initial' => $request->string('middle_initial')->trim()->upper()->value() ?: null,
        ]);

        return view('join.show', [
            'batch' => $batch,
            'joined' => $participant->load('groupTeam'),
        ]);
    }
}
