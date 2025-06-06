<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Interfaces\Exceptions\CustomResponseExceptionInterface;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

/**
 * Custom exception for a property not found.
 */
class IntegrityConstraintViolationException extends Exception implements CustomResponseExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @return RedirectResponse
     */
    public function render(): RedirectResponse
    {
        return Redirect::back()->withErrors([__('The record cannot be deleted due to its relations')]);
    }
}
