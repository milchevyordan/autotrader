<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasWorkflow
{
    /**
     * Resource's workflow relation.
     *
     * @return MorphOne
     */
    public function workflow(): MorphOne
    {
        return $this->morphOne(Workflow::class, 'vehicleable');
    }
}
