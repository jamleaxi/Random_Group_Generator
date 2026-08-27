<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Participant;
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
        $teams = $batch->groupTeams()->withCount('participants')->get();

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

        $counts = $teams->pluck('participants_count', 'id')->all();

        $assigned = collect();

        foreach ($uniqueNames as $name) {
            $minCount = min($counts);
            $candidateTeamIds = array_keys($counts, $minCount, true);
            $teamId = $candidateTeamIds[array_rand($candidateTeamIds)];

            $assigned->push(Participant::create([
                'batch_id' => $batch->id,
                'group_team_id' => $teamId,
                'name' => $name,
            ]));

            $counts[$teamId]++;
        }

        return [
            'assigned' => $assigned,
            'duplicates' => $duplicates,
        ];
    }
}
