<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowFinishedStep extends Model
{
    use HasFiles;
    use HasImages;
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'workflow_id',
        'workflow_step_namespace',
        'additional_value',
        'finished_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'finished_at' => 'datetime:d-m-Y H:i',
    ];

    /**
     * Return workflow finished step's workflow relation.
     *
     * @return BelongsTo
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }
}
