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
     * Randomly assign the given names to the batch's group teams, keeping
     * team sizes as equal as possible across the whole batch (including
     * names assigned in earlier rounds). Names that already exist in the
     * batch (case-insensitive) are skipped and returned as duplicates.
     *
     * @param  list<string>  $names
     * @return array{assigned: Collection<int, Participant>, duplicates: list<string>}
     */
    public function assign(Batch $batch, array $names): array
    {
        $existingNames = $batch->participants()->pluck('name')
            ->map(fn (string $name) => mb_strtolower($name))
            ->all();

        $seen = [];
        $duplicates = [];
        $uniqueNames = [];

        foreach ($names as $name) {
            $key = mb_strtolower($name);

            if (in_array($key, $existingNames, true) || isset($seen[$key])) {
                $duplicates[] = $name;

                continue;
            }

            $seen[$key] = true;
            $uniqueNames[] = $name;
        }

        shuffle($uniqueNames);

        $assigned = collect();

        foreach ($uniqueNames as $name) {
            $assigned->push($this->assignOne($batch, $name, Gender::UNSPECIFIED));
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
    public function assignOne(Batch $batch, string $name, string $gender, array $attributes = []): Participant
    {
        $teams = $batch->groupTeams()->with('participants')->get();
        $teamId = $this->pickTeam($teams, $batch->balance_gender, $gender);

        return Participant::create(array_merge([
            'batch_id' => $batch->id,
            'group_team_id' => $teamId,
            'name' => $name,
            'gender' => $gender,
        ], $attributes));
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
