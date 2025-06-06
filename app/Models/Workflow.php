<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Workflow extends Model
{
    use HasCreator;
    use HasDocuments;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'vehicleable_type',
        'vehicleable_id',
    ];

    /**
     * Return vehicle's morph relation.
     * (Service Vehicle / Vehicle).
     *
     * @return MorphTo
     */
    public function vehicleable(): MorphTo
    {
        return $this->morphTo('vehicleable');
    }

    // /**
    //  * Return workflow's workflow process.
    //  *
    //  * @return BelongsTo
    //  */
    // public function process(): BelongsTo
    // {
    //     return $this->belongsTo(WorkflowProcess::class, 'workflow_process_id');
    // }

    /**
     * Return workflow's finished steps.
     *
     * @return HasMany
     */
    public function finishedSteps(): HasMany
    {
        return $this->hasMany(WorkflowFinishedStep::class, 'workflow_id');
    }
}
