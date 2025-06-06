<?php

declare(strict_types=1);

namespace App\Services\Images\Compressor\Exception;

use Exception;

class ImageInfoException extends Exception
{
    /**
     * Create a new ImageInfoException instance.
     */
    public function __construct()
    {
        parent::__construct('Failed to get image info during the compression');
    }
}
