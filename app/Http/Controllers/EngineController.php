<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FuelType;
use App\Http\Requests\StoreEngineRequest;
use App\Http\Requests\UpdateEngineRequest;
use App\Models\Engine;
use App\Services\DataTable\DataTable;
use App\Services\MakeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EngineController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(Engine::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            Engine::inThisCompany()->select(Engine::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('name', __('Engine'), true, true)
            ->setColumn('fuel', __('Fuel type'), true, true)
            ->setTimestamps()
            ->setEnumColumn('fuel', FuelType::class)
            ->run();

        return Inertia::render('engines/Index', [
            'dataTable' => fn () => $dataTable,
            'make'      => fn () => MakeService::getMakes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEngineRequest $request
     * @return RedirectResponse
     */
    public function store(StoreEngineRequest $request): RedirectResponse
    {
        $engine = new Engine();
        $engine->fill($request->validated());
        $engine->creator_id = auth()->id();
        if ($engine->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateEngineRequest $request
     * @param  Engine              $engine
     * @return RedirectResponse
     */
    public function update(UpdateEngineRequest $request, Engine $engine): RedirectResponse
    {
        if ($engine->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Engine           $engine
     * @return RedirectResponse
     */
    public function destroy(Engine $engine): RedirectResponse
    {
        try {
            $engine->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}
