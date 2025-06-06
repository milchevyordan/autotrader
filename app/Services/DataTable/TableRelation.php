<?php

declare(strict_types=1);

namespace App\Services\DataTable;

class TableRelation
{
    /**
     * A string representation of the relations.
     *
     * @var string
     */
    public string $relationsString;

    /**
     * An array representation of the relations.
     *
     * @var array|string[]
     */
    public array $relationsArray;

    /**
     * Show if trashed relations should be loaded.
     *
     * @var bool
     */
    public bool $withTrashed;

    /**
     * Array of columns that will be selected from the relation.
     *
     * @var null|array
     */
    public ?array $columnsToSelect = null;

    /**
     * Create a new TableRelation instance.
     *
     * @param string $relationsString
     * @param bool   $withTrashed
     */
    public function __construct(
        string $relationsString,
        bool $withTrashed,
    ) {
        $this->relationsString = $relationsString;

        $this->relationsArray = explode('.', $relationsString);
        $this->withTrashed = $withTrashed;
    }

    /**
     * Get the value of columnsToSelect.
     *
     * @return ?array
     */
    public function getColumnsToSelect(): ?array
    {
        return $this->columnsToSelect;
    }

    /**
     * Set the value of columnsToSelect.
     *
     * @param  ?array $columnsToSelect
     * @return self
     */
    public function setColumnsToSelect(?array $columnsToSelect): self
    {
        $this->columnsToSelect = $columnsToSelect;

        return $this;
    }

    /**
     * Get the value of withTrashed.
     *
     * @return bool
     */
    public function getWithTrashed(): bool
    {
        return $this->withTrashed;
    }

    /**
     * Set the value of withTrashed.
     *
     * @param  bool $withTrashed
     * @return self
     */
    public function setWithTrashed(bool $withTrashed): self
    {
        $this->withTrashed = $withTrashed;

        return $this;
    }
}
