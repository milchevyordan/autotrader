<?php

declare(strict_types=1);

namespace App\Services\Images\Compressor\Exception;

use Exception;

class CompressionException extends Exception
{
    /**
     * Create a new CompressionException instance.
     */
    public function __construct()
    {
        parent::__construct('Failed to compress the image during the compression');
    }
}
