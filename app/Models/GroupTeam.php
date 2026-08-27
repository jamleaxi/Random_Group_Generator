<?php

namespace App\Models;

use Database\Factories\GroupTeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupTeam extends Model
{
    /** @use HasFactory<GroupTeamFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'name',
        'position',
    ];

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return HasMany<Participant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class)->orderBy('name');
    }

    /**
     * Member counts grouped by gender, keyed by gender value.
     *
     * @return array<string, int>
     */
    public function genderCounts(): array
    {
        return $this->participants->countBy('gender')->all();
    }
}
