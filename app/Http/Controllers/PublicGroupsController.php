<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\View\View;

class PublicGroupsController extends Controller
{
    /**
     * Show a read-only, public view of a batch's groups (no admin controls).
     */
    public function show(Batch $batch): View
    {
        $batch->load(['groupTeams.participants']);

        return view('batches.public', ['batch' => $batch]);
    }
}
