<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Throwable;

class RoleController extends Controller
{
    public RoleService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(Role::class);
        $this->service = new RoleService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RoleService $service): Response
    {
        return Inertia::render('roles/Index', [
            'dataTable' => $service->getIndexMethodDataTable()->run(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('roles/Create', [
            'allPermissions' => fn () => Permission::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $role = $this->service->createRoleAndAttachPermissions($request);

            DB::commit();

            return redirect()->route('roles.edit', ['role' => $role->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        $role->load(['permissions', 'changeLogs']);

        return Inertia::render('roles/Edit', [
            'role'           => fn () => $role,
            'allPermissions' => fn () => Permission::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role, RoleService $service): RedirectResponse
    {
        DB::beginTransaction();

        try {

            $service->updateRoleAndPermissions($request, $role);

            DB::commit();

            return redirect()->route('roles.edit', ['role' => $role->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }
}
