<?php

declare(strict_types=1);

namespace App\Services\Workflow\Exceptions;

use Exception;

class ValidationRequirementNotMetException extends Exception
{
    /**
     * @param string         Requirement for workflow creating not met
     * @param int            $code     the error code (optional)
     * @param null|Exception $previous the previous exception for chaining (optional)
     * @param string         $message
     */
    public function __construct(string $message, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
