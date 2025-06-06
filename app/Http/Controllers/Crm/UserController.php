<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCrmUserRequest;
use App\Http\Requests\Crm\UpdateCrmUserRequest;

use App\Models\User;
use App\Services\CompanyService;
use App\Services\CrmUserService;
use App\Services\DataTable\DataTable;
use App\Services\MultiSelectService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class UserController extends Controller
{
    public CrmUserService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->service = new CrmUserService();
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            User::hasCrmRoles()->inThisCompany()->select(User::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('company', ['id', 'name'])
            ->setRelation('roles', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true, true)
            ->setColumn('company.name', __('Company'), true, true)
            ->setColumn('full_name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('roles.name', __('Role'), true)
            ->setTimestamps()
            ->run();

        return Inertia::render('crm/users/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('crm/users/Create', [
            'companies' => fn () => CompanyService::getCrmCompanies(),
            'crmRoles'  => fn () => (new MultiSelectService(RoleService::getCrmRoles()))->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCrmUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCrmUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $createdUser = $this->service->createCrmUser($request);

            DB::commit();

            return redirect()->route('crm.users.edit', ['user' => $createdUser->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User                      $user
     * @return RedirectResponse|Response
     */
    public function show(User $user): Response|RedirectResponse
    {
        return Inertia::render('crm/users/Show', $this->service->getCrmShowData($user));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User     $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        $user->load(['changeLogs']);

        return Inertia::render('crm/users/Edit', [
            'user'        => $user,
            'userRoles'   => $user->roles->pluck('id'),
            'companies'   => fn () => CompanyService::getCrmCompanies(),
            'tokenExists' => $user->passwordResetToken && $user->passwordResetToken->created_at->gt(now()->subMinutes(60)),
            'crmRoles'    => fn () => (new MultiSelectService(RoleService::getCrmRoles()))->dataForSelect(),
            'files'       => fn () => $user->getGroupedFiles(['idCardFiles']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCrmUserRequest $request
     * @param  User                 $user
     * @return RedirectResponse
     */
    public function update(UpdateCrmUserRequest $request, User $user): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->updateCrmUser($request, $user);

            DB::commit();

            return back()->with('success', __('The record been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}
