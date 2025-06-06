<?php

declare(strict_types=1);

namespace App\Services\Workflow\Exceptions;

use Exception;

class ClassNotFoundException extends Exception
{
    /**
     * @param string         $className the name of the missing class
     * @param int            $code      the error code (optional)
     * @param null|Exception $previous  the previous exception for chaining (optional)
     */
    public function __construct(string $className, int $code = 0, ?Exception $previous = null)
    {
        $message = "The class '{$className}' does not exist.";
        parent::__construct($message, $code, $previous);
    }
}
