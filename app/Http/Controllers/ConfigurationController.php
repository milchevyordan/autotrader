<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Configuration;
use Inertia\Inertia;
use Inertia\Response;

class ConfigurationController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(Configuration::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('configurations/Index');
    }
}
