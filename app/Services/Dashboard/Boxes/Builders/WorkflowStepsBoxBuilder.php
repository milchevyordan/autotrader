<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Services\Workflow\Components\Steps\Step;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

abstract class WorkflowStepsBoxBuilder extends BoxBuilder
{
    /**
     * By giving builder filter by not having workflow finished steps for the given step variable map names.
     *
     * @param  Builder $builder
     * @param  array   $stepClassNames
     * @param  bool    $useOrWhere
     * @return void
     */
    protected static function filterWorkFlowNotFinishedSteps(Builder &$builder, array $stepClassNames, bool $useOrWhere = false): void
    {
        self::validateStepClassNames($stepClassNames);

        $builder->whereHas('workflow', function ($workflowQuery) use ($stepClassNames, $useOrWhere) {
            $workflowQuery->whereDoesntHave('finishedSteps', function ($finishedStepQuery) use ($stepClassNames, $useOrWhere) {
                foreach ($stepClassNames as $stepVariableMapName) {
                    if ($useOrWhere) {
                        $finishedStepQuery->orWhere('workflow_step_namespace', $stepVariableMapName);
                    } else {
                        $finishedStepQuery->where('workflow_step_namespace', $stepVariableMapName);
                    }
                }
            });
        });
    }

    /**
     * By giving builder filter by not having workflow finished steps for the given step variable map names.
     *
     * @param  Builder $builder
     * @param  array   $stepClassNames
     * @param  bool    $useOrWhere
     * @return void
     */
    protected static function filterWorkFlowFinishedSteps(Builder &$builder, array $stepClassNames, bool $useOrWhere = false): void
    {
        self::validateStepClassNames($stepClassNames);
        $builder->whereHas('workflow', function ($workflowQuery) use ($stepClassNames, $useOrWhere) {
            $workflowQuery->whereHas('finishedSteps', function ($finishedStepQuery) use ($stepClassNames, $useOrWhere) {
                foreach ($stepClassNames as $stepVariableMapName) {
                    if ($useOrWhere) {
                        $finishedStepQuery->orWhere('workflow_step_namespace', $stepVariableMapName);
                    } else {
                        $finishedStepQuery->where('workflow_step_namespace', $stepVariableMapName);
                    }
                }
            });
        });
    }

    private static function validateStepClassNames(array $classNames)
    {
        foreach ($classNames as $className) {
            if (! is_subclass_of($className, Step::class)) {
                throw new InvalidArgumentException("Class {$className} does not extend the Base Step class.");
            }
        }
    }
}
