<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportParticipantsRequest;
use App\Http\Requests\RenameGroupTeamRequest;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\StoreParticipantsRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use App\Services\GroupRandomizer;
use App\Services\TeamsSpreadsheetExporter;
use App\Support\Gender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BatchController extends Controller
{
    /**
     * Display a list of all grouping batches.
     */
    public function index(): View
    {
        $batches = Batch::withCount('participants')
            ->with('creator')
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
                'balance_gender' => $request->boolean('balance_gender'),
                'created_by' => $request->user()->id,
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
        $batch->load(['groupTeams.participants', 'creator']);

        return view('batches.show', ['batch' => $batch]);
    }

    /**
     * Return the batch's current groups markup and member total, polled by
     * the admin view so newly self-submitted members appear automatically.
     */
    public function refresh(Batch $batch): JsonResponse
    {
        $batch->load(['groupTeams.participants']);
        $total = $batch->participants->count();

        return response()->json([
            'total' => $total,
            'totalLabel' => "{$total} ".Str::plural('member', $total).' total',
            'html' => view('batches.partials.groups', ['batch' => $batch])->render(),
        ]);
    }

    /**
     * Download the batch's members as a CSV file, sorted alphabetically.
     */
    public function exportCsv(Batch $batch): StreamedResponse
    {
        $participants = $batch->participants()->orderBy('name')->get();

        $filename = Str::slug($batch->name).'-groups.csv';

        $callback = function () use ($participants) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['name', 'gender']);

            foreach ($participants as $participant) {
                fputcsv($handle, [$participant->name, Gender::label($participant->gender)]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Download the batch's members as an Excel workbook: an "All" sheet
     * (name, gender, team) plus one sheet per team (name, gender). Members
     * are always sorted alphabetically by name.
     */
    public function exportTeamsExcel(Batch $batch, TeamsSpreadsheetExporter $exporter): StreamedResponse
    {
        $spreadsheet = $exporter->build($batch);

        $filename = Str::slug($batch->name).'-teams.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Rename a batch and/or update its gender-balancing preference.
     */
    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $batch->update([
            'name' => $request->validated('name'),
            'balance_gender' => $request->boolean('balance_gender'),
        ]);

        return redirect()->route('batches.show', $batch)
            ->with('status', 'Batch updated.');
    }

    /**
     * Rename one of the batch's group teams.
     */
    public function renameTeam(RenameGroupTeamRequest $request, Batch $batch, GroupTeam $groupTeam): RedirectResponse
    {
        abort_unless($groupTeam->batch_id === $batch->id, 404);

        $groupTeam->update(['name' => $request->validated('name')]);

        return redirect()->route('batches.show', $batch)
            ->with('status', 'Team renamed.');
    }

    /**
     * Randomly assign the submitted names to the batch's group teams.
     */
    public function storeParticipants(StoreParticipantsRequest $request, Batch $batch, GroupRandomizer $randomizer): RedirectResponse
    {
        $result = $randomizer->assign($batch, $request->entries());

        if ($result['duplicates'] !== []) {
            return redirect()->route('batches.show', $batch)
                ->with('duplicates', $result['duplicates']);
        }

        return redirect()->route('batches.show', $batch)
            ->with('status', $result['assigned']->count().' name(s) randomized into groups.');
    }

    /**
     * Parse name/gender entries from an uploaded CSV file and load them into
     * the "Add names manually" form for review. Nothing is assigned to a
     * group team until the admin submits that form.
     */
    public function importParticipants(ImportParticipantsRequest $request, Batch $batch, GroupRandomizer $randomizer): RedirectResponse
    {
        $entries = $request->entries();

        if ($entries === []) {
            return redirect()->route('batches.show', $batch)
                ->with('error', 'No valid names were found in that CSV.');
        }

        $duplicateNameKeys = $randomizer->findDuplicateNameKeys($batch, $entries);

        $redirect = redirect()->route('batches.show', $batch)
            ->with('importedEntries', $entries)
            ->with('importedDuplicateNameKeys', $duplicateNameKeys)
            ->with('status', count($entries).' name(s) imported from CSV. Review them below, then click "Randomize into groups".');

        if ($duplicateNameKeys !== []) {
            $duplicateNames = collect($entries)
                ->pluck('name')
                ->filter(fn (string $name) => in_array(mb_strtolower($name), $duplicateNameKeys, true))
                ->unique()
                ->values()
                ->all();

            $redirect->with('importDuplicates', $duplicateNames);
        }

        return $redirect;
    }

    /**
     * Manually move a participant to a different team.
     */
    public function transferParticipant(Batch $batch, Participant $participant, GroupTeam $groupTeam): RedirectResponse
    {
        abort_unless($participant->batch_id === $batch->id && $groupTeam->batch_id === $batch->id, 404);

        $participant->update(['group_team_id' => $groupTeam->id]);

        return redirect()->route('batches.show', $batch)
            ->with('status', "{$participant->name} moved to {$groupTeam->name}.");
    }

    /**
     * Remove a participant from the batch entirely.
     */
    public function destroyParticipant(Batch $batch, Participant $participant): RedirectResponse
    {
        abort_unless($participant->batch_id === $batch->id, 404);

        $participant->delete();

        return redirect()->route('batches.show', $batch)
            ->with('status', "{$participant->name} removed.");
    }

    /**
     * Remove every participant from the batch, emptying all of its groups.
     * The groups themselves are left in place.
     */
    public function clearParticipants(Batch $batch): RedirectResponse
    {
        if ($batch->locked) {
            return redirect()->route('batches.show', $batch)
                ->with('error', 'This batch is locked and its groups cannot be cleared. Unlock it first.');
        }

        $count = $batch->participants()->count();
        $batch->participants()->delete();

        return redirect()->route('batches.show', $batch)
            ->with('status', "Cleared {$count} member(s) from all groups.");
    }

    /**
     * Generate (or regenerate) the public link that lets users self-submit
     * their names into this batch.
     */
    public function openLink(Batch $batch): RedirectResponse
    {
        $batch->update(['public_token' => Str::random(32)]);

        return redirect()->route('batches.show', $batch)
            ->with('status', 'Batch opened for public submissions.');
    }

    /**
     * Disable the public link so users can no longer self-submit names.
     */
    public function closeLink(Batch $batch): RedirectResponse
    {
        $batch->update(['public_token' => null]);

        return redirect()->route('batches.show', $batch)
            ->with('status', 'Public submission link closed.');
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
