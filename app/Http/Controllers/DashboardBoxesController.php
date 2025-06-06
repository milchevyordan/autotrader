<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\RedFlag\Initiator;
use App\Support\StringHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DashboardBoxesController extends Controller
{
    /**
     * Show resource based on work filter condition.
     *
     * @param  string   $slug
     * @return Response
     */
    public function index(string $slug): Response
    {
        $className = Str::studly($slug);
        $builder = new ("App\\Services\\Dashboard\\Boxes\\Builders\\{$className}");

        return Inertia::render('work-filters/Index', [
            'method'        => fn () => StringHelper::camelCaseToTitle($className),
            'dataTableType' => fn () => Str::ucfirst($builder->dataTableMethod->value),
            'dataTable'     => fn () => $builder->toDataTable()->run(),
        ]);
    }
}
