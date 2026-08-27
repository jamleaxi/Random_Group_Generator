<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\StoreParticipantsRequest;
use App\Models\Batch;
use App\Models\GroupTeam;
use App\Services\GroupRandomizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchController extends Controller
{
    /**
     * Display a list of all grouping batches.
     */
    public function index(): View
    {
        $batches = Batch::withCount('participants')
            ->latest()
            ->get();

        return view('batches.index', ['batches' => $batches]);
    }

    /**
     * Show the form to start a new grouping batch.
     */
    public function create(): View
    {
        return view('batches.create');
    }

    /**
     * Create a new grouping batch with its group teams.
     */
    public function store(StoreBatchRequest $request): RedirectResponse
    {
        $groupCount = (int) $request->validated('group_count');

        $groupNames = collect(preg_split('/\r\n|\r|\n/', (string) $request->string('group_names')))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->values();

        $batch = DB::transaction(function () use ($request, $groupCount, $groupNames) {
            $batch = Batch::create([
                'name' => $request->validated('name') ?: 'Untitled batch',
                'group_count' => $groupCount,
            ]);

            for ($position = 1; $position <= $groupCount; $position++) {
                GroupTeam::create([
                    'batch_id' => $batch->id,
                    'name' => $groupNames->get($position - 1) ?: "Group {$position}",
                    'position' => $position,
                ]);
            }

            return $batch;
        });

        return redirect()->route('batches.show', $batch);
    }

    /**
     * Show a batch's group teams and members, and the form to add more names.
     */
    public function show(Batch $batch): View
    {
        $batch->load(['groupTeams.participants']);

        return view('batches.show', ['batch' => $batch]);
    }

    /**
     * Randomly assign the submitted names to the batch's group teams.
     */
    public function storeParticipants(StoreParticipantsRequest $request, Batch $batch, GroupRandomizer $randomizer): RedirectResponse
    {
        $result = $randomizer->assign($batch, $request->names());

        if ($result['duplicates'] !== []) {
            return redirect()->route('batches.show', $batch)
                ->with('duplicates', $result['duplicates']);
        }

        return redirect()->route('batches.show', $batch)
            ->with('status', $result['assigned']->count().' name(s) randomized into groups.');
    }

    /**
     * Toggle whether a batch is locked against deletion.
     */
    public function toggleLock(Batch $batch): RedirectResponse
    {
        $batch->update(['locked' => ! $batch->locked]);

        return redirect()->route('batches.show', $batch)
            ->with('status', $batch->locked ? 'Batch locked.' : 'Batch unlocked.');
    }

    /**
     * Delete a batch and its groups and participants.
     */
    public function destroy(Batch $batch): RedirectResponse
    {
        if ($batch->locked) {
            return redirect()->route('batches.show', $batch)
                ->with('error', 'This batch is locked and cannot be deleted. Unlock it first.');
        }

        $batch->delete();

        return redirect()->route('batches.index')
            ->with('status', 'Batch deleted.');
    }
}
