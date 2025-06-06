<?php

declare(strict_types=1);

namespace App\Services\Workflow;

abstract class BaseDatabaseRelationsInitiator
{
    abstract public function getRelations();

    // private

    /**
     * Map the morph model and the columns and relations, that should be selected.
     * [key: ModelNamespace] => [columns to select].
     */
    public function __construct()
    {
    }

    /**
     * Return columns that need to be selected.
     *
     * @param  string              $morphedModel
     * @return null|mixed|string[]
     */
    public function getMorphedColumnsToSelect(string $morphedModel): mixed
    {
        return $this->getRelations()[$morphedModel]['columnsToSelect'] ?? null;
    }

    /**
     * Return relations that need to be loaded.
     *
     * @param  string              $morphedModel
     * @return null|mixed|string[]
     */
    public function getMorphedRelations(string $morphedModel): mixed
    {
        return $this->getRelations()[$morphedModel]['relations'] ?? null;
    }
}
