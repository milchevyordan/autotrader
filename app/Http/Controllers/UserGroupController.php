<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserGroupRequest;
use App\Http\Requests\UpdateUserGroupRequest;
use App\Models\UserGroup;
use App\Services\DataTable\Paginator;
use App\Services\UserGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class UserGroupController extends Controller
{
    private UserGroupService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(UserGroup::class);
        $this->service = new UserGroupService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $expandGroupService = $this->service->getExpandGroupService();

        return Inertia::render('crm/user-groups/Index', [
            'paginator'  => new Paginator($expandGroupService->getExpandedGroupPaginator()),
            'userGroups' => $expandGroupService->getExpandedGroupPaginator()->items(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('crm/user-groups/Create', [
            'dataTable' => $this->service->getUsersDataTable()->run(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreUserGroupRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserGroupRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createUserGroup($request);

            DB::commit();

            return redirect()->route('crm.user-groups.edit', ['user_group' => $this->service->getUserGroup()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  UserGroup $userGroup
     * @return Response
     */
    public function edit(UserGroup $userGroup): Response
    {
        $userGroup->load('users');

        $this->service->setUserGroup($userGroup);

        return Inertia::render('crm/user-groups/Edit', [
            'userGroup' => $userGroup,
            'dataTable' => $this->service->getEditMethodDataTable(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUserGroupRequest $request
     * @param  UserGroup              $userGroup
     * @return RedirectResponse
     */
    public function update(UpdateUserGroupRequest $request, UserGroup $userGroup): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setUserGroup($userGroup)
                ->updateUserGroup($request);

            DB::commit();

            return redirect()->route('crm.user-groups.edit', ['user_group' => $userGroup->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  UserGroup        $userGroup
     * @return RedirectResponse
     */
    public function destroy(UserGroup $userGroup): RedirectResponse
    {
        try {
            $userGroup->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}
