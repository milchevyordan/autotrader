<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components;

use App\Models\ServiceVehicle;
use App\Models\Vehicle;
use App\Services\Workflow\Components\Processes\Process;
use Illuminate\Support\Collection;

final class Workflow
{
    /**
     * @var Vehicle|ServiceVehicle
     */
    public Vehicle|ServiceVehicle $vehicle;

    /**
     * @var Process
     */
    public Process $process;

    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $vehicleableType;

    /**
     * @var Collection
     */
    public Collection $images;

    /**
     * @var Collection
     */
    public Collection $files;

    /**
     * @param Vehicle|ServiceVehicle $vehicle
     * @param Process                     $process
     * @param string                      $vehicleableType
     * @param Collection                       $images
     * @param Collection                       $files
     * @param int                         $id
     */
    public function __construct(Vehicle|ServiceVehicle $vehicle, Process $process, int $id, string $vehicleableType, Collection $images, Collection $files)
    {
        $this->vehicle = $vehicle;
        $this->vehicleableType = $vehicleableType;
        $this->process = $process;
        $this->id = $id;
        $this->images = $images;
        $this->files = $files;
    }
}
