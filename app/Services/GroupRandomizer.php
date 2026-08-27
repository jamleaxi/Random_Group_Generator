<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use App\Support\Gender;
use Illuminate\Support\Collection;

class GroupRandomizer
{
    /**
     * Randomly assign the given name/gender entries to the batch's group
     * teams, keeping team sizes (and, when enabled, gender counts) as equal
     * as possible across the whole batch. Names that already exist in the
     * batch (case-insensitive) are skipped and returned as duplicates.
     *
     * @param  list<array{name: string, gender: string}>  $entries
     * @return array{assigned: Collection<int, Participant>, duplicates: list<string>}
     */
    public function assign(Batch $batch, array $entries): array
    {
        $existingNames = $batch->participants()->pluck('name')
            ->map(fn (string $name) => mb_strtolower($name))
            ->all();

        $seen = [];
        $duplicates = [];
        $uniqueEntries = [];

        foreach ($entries as $entry) {
            $key = mb_strtolower($entry['name']);

            if (in_array($key, $existingNames, true) || isset($seen[$key])) {
                $duplicates[] = $entry['name'];

                continue;
            }

            $seen[$key] = true;
            $uniqueEntries[] = $entry;
        }

        shuffle($uniqueEntries);

        $assigned = collect();

        foreach ($uniqueEntries as $entry) {
            $assigned->push($this->assignOne($batch, $entry['name'], $entry['gender'] ?: Gender::UNSPECIFIED));
        }

        return [
            'assigned' => $assigned,
            'duplicates' => $duplicates,
        ];
    }

    /**
     * Assign a single participant (typically from the public join form) to
     * whichever team currently keeps the batch most balanced. When
     * `balance_gender` is enabled on the batch, the participant's own gender
     * count within each team is preferred over the team's overall size.
     */
    public function assignOne(Batch $batch, string $name, string $gender): Participant
    {
        $teams = $batch->groupTeams()->with('participants')->get();
        $teamId = $this->pickTeam($teams, $batch->balance_gender, $gender);

        return Participant::create([
            'batch_id' => $batch->id,
            'group_team_id' => $teamId,
            'name' => $name,
            'gender' => $gender,
        ]);
    }

    /**
     * @param  Collection<int, GroupTeam>  $teams
     */
    private function pickTeam(Collection $teams, bool $balanceGender, string $gender): int
    {
        if ($balanceGender) {
            $counts = $teams->mapWithKeys(fn (GroupTeam $team) => [
                $team->id => $team->participants->where('gender', $gender)->count(),
            ])->all();

            $minCount = min($counts);
            $candidateTeamIds = array_keys($counts, $minCount, true);

            // Break ties using overall team size so teams stay balanced too.
            $overallCounts = $teams->pluck('participants', 'id')
                ->map(fn ($participants) => $participants->count())
                ->only($candidateTeamIds)
                ->all();

            $minOverall = min($overallCounts);
            $finalCandidates = array_keys($overallCounts, $minOverall, true);

            return (int) $finalCandidates[array_rand($finalCandidates)];
        }

        $counts = $teams->mapWithKeys(fn (GroupTeam $team) => [$team->id => $team->participants->count()])->all();
        $minCount = min($counts);
        $candidateTeamIds = array_keys($counts, $minCount, true);

        return (int) $candidateTeamIds[array_rand($candidateTeamIds)];
    }
}
