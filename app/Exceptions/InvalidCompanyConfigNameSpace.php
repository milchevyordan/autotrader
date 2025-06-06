<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class InvalidCompanyConfigNameSpace extends Exception
{
    /**
     * @param string         $nameSpace
     * @param int            $code      the error code (optional)
     * @param null|Exception $previous  the previous exception for chaining (optional)
     */
    public function __construct(string $nameSpace, int $code = 0, ?Exception $previous = null)
    {
        $message = "Invalid company config namespace `{$nameSpace}` in the database table: 'configurations'.";
        parent::__construct($message, $code, $previous);
    }
}
