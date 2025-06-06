<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Components;

use App\Services\Dashboard\Boxes\Builders\BoxBuilder;
use Illuminate\Support\Str;

class Box
{
    /**
     * The name of the work filter.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the work filter.
     *
     * @var string
     */
    public string $description;

    /**
     * The slug representation of the work filter.
     *
     * @var string
     */
    public string $slug;

    /**
     * @param string     $name
     * @param string     $description
     * @param BoxBuilder $boxBuilder
     */
    public BoxBuilder $boxBuilder;

    /**
     * Create a new WorkFilter instance.
     *
     * @param string     $name
     * @param string     $description
     * @param string     $method
     * @param BoxBuilder $boxBuilder
     */
    public function __construct(string $name, string $description, BoxBuilder $boxBuilder)
    {
        $this->name = $name;
        $this->description = $description;
        $this->slug = $this->getSlugByClass($boxBuilder);
        $this->boxBuilder = $boxBuilder;
    }

    private function getSlugByClass(BoxBuilder $boxBuilder): string
    {
        return Str::slug(Str::headline(class_basename($boxBuilder)));
    }

    /**
     * Get the value of boxBuilder
     *
     * @return BoxBuilder
     */
    public function getBoxBuilder(): BoxBuilder
    {
        return $this->boxBuilder;
    }
}
