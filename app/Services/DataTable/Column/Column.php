<?php

declare(strict_types=1);

namespace App\Services\DataTable\Column;

class Column
{
    public ?ColumnRelation $relation = null;

    /**
     * Create a new Column instance.
     *
     * @param string $label
     * @param bool   $searchable
     * @param bool   $orderable
     * @param bool   $exactMatch
     * @param bool   $exportable
     */
    public function __construct(
        public string $label,
        public bool $searchable,
        public bool $orderable,
        public bool $exactMatch,
        public bool $exportable
    ) {
    }

    /**
     * Get the value of relation.
     *
     * @return ?ColumnRelation
     */
    public function getRelation(): ?ColumnRelation
    {
        return $this->relation;
    }

    /**
     * Set the value of relation.
     *
     * @param  ?ColumnRelation $relation
     * @return self
     */
    public function setRelation(?ColumnRelation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }
}
