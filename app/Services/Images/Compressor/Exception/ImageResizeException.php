<?php

declare(strict_types=1);

namespace App\Services\Images\Compressor\Exception;

use Exception;

class ImageResizeException extends Exception
{
    /**
     * Create a new ImageResizeException instance.
     */
    public function __construct()
    {
        parent::__construct('Failed to resize image during the compression');
    }
}
