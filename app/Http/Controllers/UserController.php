<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Company;
use App\Models\User;
use App\Notifications\NewUserPasswordCreate;
use App\Services\ChangeLoggerService;
use App\Services\CompanyGroupService;
use App\Services\CompanyService;
use App\Services\DataTable\DataTable;
use App\Services\DocumentService;
use App\Services\EngineService;
use App\Services\ItemService;
use App\Services\MakeService;
use App\Services\MultiSelectService;
use App\Services\PreOrderService;
use App\Services\PurchaseOrderService;
use App\Services\QuoteService;
use App\Services\RoleService;
use App\Services\SalesOrderService;
use App\Services\ServiceLevelService;
use App\Services\ServiceOrderService;
use App\Services\TransportOrderService;
use App\Services\UserGroupService;
use App\Services\UserService;
use App\Services\VariantService;
use App\Services\VehicleGroupService;
use App\Services\VehicleModelService;
use App\Services\Vehicles\PreOrderVehicleService;
use App\Services\Vehicles\ServiceVehicleService;
use App\Services\Vehicles\SystemVehicleService;
use App\Services\WorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public UserService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->service = new UserService();
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $authIsRoot = auth()->user()->hasRole('Root');

        $users = $authIsRoot ?
            User::where('creator_id', auth()->id()) :
            User::inThisCompany()->whereHas('roles', function ($rolesQuery) {
                $rolesQuery->whereIn('name', RoleService::getMainCompanyRoleNames());
            });

        $dataTable = (new DataTable(
            $users->select(User::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('company', ['id', 'name'])
            ->setRelation('roles', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('company.name', __('Company'), true)
            ->setColumn('full_name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('roles.name', __('Role'), true)
            ->setTimestamps()
            ->run();

        return Inertia::render('users/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        /**
         * @var User
         */
        $authUser = Auth::user();

        $authCompanies = Company::where('creator_id', auth()->id())
            ->whereDoesntHave('users', function ($usersQuery) {
                $usersQuery->whereHas('roles', function ($rolesQuery) {
                    $rolesQuery->where('name', 'Company Administrator');
                });
            });

        $authCanCreateRoles = $authUser->hasRole('Root') ? Role::where('name', 'Company Administrator') : RoleService::getMainCompanyRoles();

        return Inertia::render('users/Create', [
            'companies' => fn () => (new MultiSelectService($authCompanies))->dataForSelect(),
            'roles'     => fn () => (new MultiSelectService($authCanCreateRoles))->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            /**
             * @var User
             */
            $authUser = Auth::user();
            $validatedRequest = $request->validated();

            $user = new User();
            $user->fill($validatedRequest);
            $user->full_name = UserService::getFullName($validatedRequest);
            $user->company_id = $authUser->hasRole('Root') ? $request->company_id : $authUser->company_id;
            $user->password = Hash::make(Str::random(15));
            $user->creator_id = $authUser->id;
            $user->save();
            $user->assignRole($validatedRequest['roles']);

            $token = app('auth.password.broker')->createToken($user);
            Notification::send($user, new NewUserPasswordCreate($token));

            DB::commit();

            return redirect()->route('users.edit', ['user' => $user->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User     $user
     * @return Response
     */
    public function show(User $user): Response
    {
        $user->loadCount([
            'preOrders', 'purchaseOrders', 'salesOrders', 'serviceOrders', 'workOrders',
            'transportOrders', 'serviceLevels', 'items', 'documents', 'vehicles', 'preOrderVehicles',
            'serviceVehicles', 'quotes', 'makes', 'vehicleModels', 'vehicleGroups', 'engines',
            'variants', 'companies', 'companyGroups', 'users', 'createdUserGroups',
        ]);

        $this->service->setUser($user);

        return Inertia::render('users/Show', [
            'user'              => $user,
            'preOrders'         => Inertia::lazy(fn () => PreOrderService::getPreOrdersDataTableByBuilder($user->preOrders()->getQuery())->run()),
            'purchaseOrders'    => Inertia::lazy(fn () => PurchaseOrderService::getPurchaseOrdersDataTableByBuilder($user->purchaseOrders()->getQuery())->run()),
            'salesOrders'       => Inertia::lazy(fn () => SalesOrderService::getSalesOrdersDataTableByBuilder($user->salesOrders()->getQuery())->run()),
            'serviceOrders'     => Inertia::lazy(fn () => ServiceOrderService::getServiceOrdersDataTableByBuilder($user->serviceOrders()->getQuery())->run()),
            'workOrders'        => Inertia::lazy(fn () => WorkOrderService::getWorkOrdersDataTableByBuilder($user->workOrders()->getQuery())->run()),
            'transportOrders'   => Inertia::lazy(fn () => TransportOrderService::getTransportOrdersDataTableByBuilder($user->transportOrders()->getQuery())->run()),
            'serviceLevels'     => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelsDataTableByBuilder($user->serviceLevels()->getQuery())->run()),
            'items'             => Inertia::lazy(fn () => ItemService::getItemsDataTableByBuilder($user->items()->getQuery())->run()),
            'documents'         => Inertia::lazy(fn () => DocumentService::getDocumentsDataTableByBuilder($user->documents()->getQuery())->run()),
            'vehicles'          => Inertia::lazy(fn () => SystemVehicleService::getVehiclesDataTableByBuilder($user->vehicles()->getQuery())->run()),
            'preOrderVehicles'  => Inertia::lazy(fn () => PreOrderVehicleService::getPreOrderVehiclesDataTableByBuilder($user->preOrderVehicles()->getQuery())->run()),
            'serviceVehicles'   => Inertia::lazy(fn () => ServiceVehicleService::getServiceVehiclesDataTableByBuilder($user->serviceVehicles()->getQuery())->run()),
            'quotes'            => Inertia::lazy(fn () => QuoteService::getQuotesDataTableByBuilder($user->quotes()->getQuery())->run()),
            'makes'             => Inertia::lazy(fn () => MakeService::getMakesDataTableByBuilder($user->makes()->getQuery())->run()),
            'vehicleModels'     => Inertia::lazy(fn () => VehicleModelService::getVehicleModelsDataTableByBuilder($user->vehicleModels()->getQuery())->run()),
            'vehicleGroups'     => Inertia::lazy(fn () => VehicleGroupService::getVehicleGroupsDataTableByBuilder($user->vehicleGroups()->getQuery())->run()),
            'engines'           => Inertia::lazy(fn () => EngineService::getEnginesDataTableByBuilder($user->engines()->getQuery())->run()),
            'variants'          => Inertia::lazy(fn () => VariantService::getVariantsDataTableByBuilder($user->variants()->getQuery())->run()),
            'companies'         => Inertia::lazy(fn () => CompanyService::getCompaniesDataTableByBuilder($user->companies()->getQuery())->run()),
            'companyGroups'     => Inertia::lazy(fn () => CompanyGroupService::getCompanyGroupsDataTableByBuilder($user->companyGroups()->getQuery())->run()),
            'users'             => Inertia::lazy(fn () => $this->service->getUsers()),
            'createdUserGroups' => Inertia::lazy(fn () => UserGroupService::getUserGroupsDataTableByBuilder($user->createdUserGroups()->getQuery())->run()),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User     $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        $user->load(['changeLogs', 'passwordResetToken']);

        return Inertia::render('users/Edit', [
            'user'        => $user,
            'userRoles'   => $user->roles->pluck('id'),
            'tokenExists' => $user->passwordResetToken && $user->passwordResetToken->created_at->gt(now()->subMinutes(60)),
            'companies'   => (new MultiSelectService(Company::baseCompanies()))->dataForSelect(),
            'roles'       => (new MultiSelectService(auth()->user()->hasRole('Root') ? Role::where('name', 'Company Administrator') : RoleService::getMainCompanyRoles()))->dataForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUserRequest $request
     * @param  User              $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $changeLoggerService = new ChangeLoggerService($user);

            $validatedRequest = $request->validated();
            $user->full_name = UserService::getFullName($validatedRequest);
            $user->update($validatedRequest);

            $changeLoggerService->logChanges($user);

            DB::commit();

            return back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}
