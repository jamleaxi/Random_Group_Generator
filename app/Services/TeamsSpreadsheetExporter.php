<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\GroupTeam;
use App\Models\Participant;
use App\Support\Gender;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeamsSpreadsheetExporter
{
    /**
     * Build a workbook for the batch: an "All" sheet listing every member
     * (name, gender, team) plus one sheet per team (name, gender). Members
     * are always listed alphabetically by name.
     */
    public function build(Batch $batch): Spreadsheet
    {
        $batch->loadMissing('groupTeams.participants');

        $spreadsheet = new Spreadsheet;

        $allParticipants = $batch->participants()
            ->with('groupTeam')
            ->orderBy('name')
            ->get();

        $this->writeSheet(
            $spreadsheet->getActiveSheet(),
            'All',
            ['Name', 'Gender', 'Team'],
            $allParticipants->map(fn (Participant $participant) => [
                $participant->name,
                Gender::label($participant->gender),
                $participant->groupTeam->name,
            ])
        );

        $usedTitles = ['All'];

        foreach ($batch->groupTeams as $team) {
            $sheet = $spreadsheet->createSheet();
            $title = $this->uniqueSheetTitle($team, $usedTitles);
            $usedTitles[] = $title;

            $this->writeSheet(
                $sheet,
                $title,
                ['Name', 'Gender'],
                $team->participants->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                    ->map(fn (Participant $participant) => [$participant->name, Gender::label($participant->gender)])
            );
        }

        return $spreadsheet;
    }

    /**
     * @param  list<string>  $headers
     * @param  Collection<int, list<string>>  $rows
     */
    private function writeSheet(Worksheet $sheet, string $title, array $headers, Collection $rows): void
    {
        $sheet->setTitle($title);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        $row = 2;

        foreach ($rows as $values) {
            $sheet->fromArray($values, null, "A{$row}");
            $row++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Excel sheet titles must be 31 characters or fewer, may not contain
     * \ / ? * [ ] :, and must be unique within the workbook.
     *
     * @param  list<string>  $usedTitles
     */
    private function uniqueSheetTitle(GroupTeam $team, array $usedTitles): string
    {
        $base = trim(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $team->name) ?? '');
        $base = $base === '' ? 'Team' : $base;

        $title = Str::limit($base, 31, '');
        $suffix = 2;

        while (in_array($title, $usedTitles, true)) {
            $marker = " ({$suffix})";
            $title = Str::limit($base, 31 - mb_strlen($marker), '').$marker;
            $suffix++;
        }

        return $title;
    }
}
