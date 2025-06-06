<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Make;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MakeService
{
    /**
     * Collection of all the makes for concrete company.
     *
     * @var Collection<Make>
     */
    private Collection $makes;

    /**
     * Load makes.
     *
     * @return void
     */
    private function setMakes(): void
    {
        $this->makes = (new MultiSelectService(Make::inThisCompany()))->dataForSelect();
    }

    /**
     * Get the value of makes.
     *
     * @return Collection
     */
    public static function getMakes(): Collection
    {
        $self = new self();

        if (! isset($self->makes)) {
            $self->setMakes();
        }

        return $self->makes;
    }

    /**
     * Return datatable of Makes by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getMakesDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_diff(Make::$defaultSelectFields, ['creator_id']))
        ))
            ->setColumn('name', __('Make'), true, true)
            ->setTimestamps();
    }
}
