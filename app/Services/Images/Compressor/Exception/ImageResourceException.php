<?php

declare(strict_types=1);

namespace App\Services\Images\Compressor\Exception;

use Exception;

class ImageResourceException extends Exception
{
    /**
     * Create a new ImageResourceException instance.
     */
    public function __construct()
    {
        parent::__construct('Failed to create image resource during the compression');
    }
}
