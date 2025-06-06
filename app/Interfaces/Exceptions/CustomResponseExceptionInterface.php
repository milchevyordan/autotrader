<?php

declare(strict_types=1);

namespace App\Interfaces\Exceptions;

use Illuminate\Http\RedirectResponse;

/**
 * Interface for exceptions that need to be rendered into an HTTP response.
 */
interface CustomResponseExceptionInterface
{
    /**
     * Render an exception into an HTTP response.
     *
     * @return RedirectResponse
     */
    public function render(): RedirectResponse;
}
