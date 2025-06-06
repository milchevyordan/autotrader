<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\Crm\StoreCrmUserRequest;
use App\Http\Requests\Crm\UpdateCrmUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\DataTable\DataTable;
use App\Services\Files\UploadHelper;
use App\Services\Vehicles\PreOrderVehicleService;
use App\Services\Vehicles\SystemVehicleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CrmUserService extends Service
{
    /**
     * Create a new UserService instance.
     */
    public function __construct()
    {
    }

    public function getUsersDataTableByCompany(int $companyId): DataTable
    {
        return (new DataTable(
            User::where('company_id', $companyId)
                ->select(User::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('roles', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('full_name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('roles.name', __('Role'), true);

    }

    /**
     * @param StoreCrmUserRequest $request
     * @return User Created User
     */
    public function createCrmUser(StoreCrmUserRequest $request): User
    {
        $validatedRequest = $request->validated();

        $authUser = Auth::user();
        $requestRoles = Role::whereIn('id', $validatedRequest['roles'])->get();

        $user = new User();
        $user->fill($validatedRequest);
        $user->full_name = UserService::getFullName($validatedRequest);
        $user->company_id = $validatedRequest['company_id'];
        $user->password = Hash::make(Str::random(15));
        $user->creator_id = $authUser->id;
        $user->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'id_card_files'), 'idCardFiles');
        $user->assignRole($requestRoles);

        $user->load([
            'company:id,name',
            'company.users:id,full_name',
        ]);

        if ($user->company->users->count() == 0) {
            $companyService = new CompanyService();
            $companyService->assignMainUserToCompany($user->company, $user);
        }

        return $user;
    }

    /**
     * Update the crm user
     *
     * @param UpdateCrmUserRequest $request
     * @param User $user
     * @return void
     */
    public function updateCrmUser(UpdateCrmUserRequest $request, User $user): void
    {
        $validatedRequest = $request->validated();

        $requestRoles = Role::whereIn('id', $validatedRequest['roles'])->get();

        $changeLoggerService = new ChangeLoggerService($user);
        $user->full_name = UserService::getFullName($validatedRequest);
        $user->company_id = $validatedRequest['company_id'];
        $user->update($validatedRequest);
        $user->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'id_card_files'), 'idCardFiles');
        $user->syncRoles($requestRoles);
        $changeLoggerService->logChanges($user);
    }

    /**
     * Return assigned to resources.
     *
     * @param User $user
     * @return array
     */
    public function getCrmShowData(User $user): array
    {
        $resourcesCount = [];
        $data = [];

        foreach ($user->roles as $role) {
            switch ($role->name) {
                case 'Supplier':
                    $user->loadCount([
                        'supplierPreOrders', 'supplierPurchaseOrders', 'assignedToWorkOrders',
                        'supplierVehicles', 'supplierPreOrderVehicles',
                    ]);

                    $resourcesCount = array_merge_recursive($resourcesCount, [
                        'pre_orders_count'         => [$user->supplier_pre_orders_count],
                        'purchase_orders_count'    => [$user->supplier_purchase_orders_count],
                        'work_orders_count'        => [$user->assigned_to_work_orders_count],
                        'vehicles_count'           => [$user->supplier_vehicles_count],
                        'pre_order_vehicles_count' => [$user->supplier_pre_order_vehicles_count],
                    ]);

                    $data = array_merge($data, [
                        'preOrders'        => Inertia::lazy(fn () => PreOrderService::getPreOrdersDataTableByBuilder($user->supplierPreOrders()->getQuery(), true)->run()),
                        'purchaseOrders'   => Inertia::lazy(fn () => PurchaseOrderService::getPurchaseOrdersDataTableByBuilder($user->supplierPurchaseOrders()->getQuery(), true)->run()),
                        'workOrders'       => Inertia::lazy(fn () => WorkOrderService::getWorkOrdersDataTableByBuilder($user->assignedToWorkOrders()->getQuery(), true)->run()),
                        'vehicles'         => Inertia::lazy(fn () => SystemVehicleService::getVehiclesDataTableByBuilder($user->supplierVehicles()->getQuery(), true)->run()),
                        'preOrderVehicles' => Inertia::lazy(fn () => PreOrderVehicleService::getPreOrderVehiclesDataTableByBuilder($user->supplierPreOrderVehicles()->getQuery(), true)->run()),
                    ]);
                    break;

                case 'Transport Supplier':
                    $user->loadCount(['transportCompanyTransportOrders', 'assignedToWorkOrders']);

                    $resourcesCount = array_merge_recursive($resourcesCount, [
                        'transport_orders_count' => [$user->transport_company_transport_orders_count],
                        'work_orders_count'      => [$user->assigned_to_work_orders_count],
                    ]);

                    $data = array_merge($data, [
                        'transportOrders' => Inertia::lazy(fn () => TransportOrderService::getTransportOrdersDataTableByBuilder($user->transportCompanyTransportOrders()->getQuery(), true)->run()),
                        'workOrders'      => Inertia::lazy(fn () => WorkOrderService::getWorkOrdersDataTableByBuilder($user->assignedToWorkOrders()->getQuery(), true)->run()),
                    ]);
                    break;

                case 'Intermediary':
                    $user->loadCount(['intermediaryPreOrders', 'intermediaryPurchaseOrders', 'assignedToWorkOrders']);

                    $resourcesCount = array_merge_recursive($resourcesCount, [
                        'pre_orders_count'      => [$user->intermediary_pre_orders_count],
                        'purchase_orders_count' => [$user->intermediary_purchase_orders_count],
                        'work_orders_count'     => [$user->assigned_to_work_orders_count],
                    ]);

                    $data = array_merge($data, [
                        'preOrders'      => Inertia::lazy(fn () => PreOrderService::getPreOrdersDataTableByBuilder($user->intermediaryPreOrders()->getQuery(), true)->run()),
                        'purchaseOrders' => Inertia::lazy(fn () => PurchaseOrderService::getPurchaseOrdersDataTableByBuilder($user->intermediaryPurchaseOrders()->getQuery(), true)->run()),
                        'workOrders'     => Inertia::lazy(fn () => WorkOrderService::getWorkOrdersDataTableByBuilder($user->assignedToWorkOrders()->getQuery(), true)->run()),
                    ]);
                    break;

                case 'Purchaser':
                    $user->loadCount(['purchaserPreOrders', 'purchaserPurchaseOrders', 'sellerSalesOrders', 'assignedToWorkOrders']);

                    $resourcesCount = array_merge_recursive($resourcesCount, [
                        'pre_orders_count'      => [$user->purchaser_pre_orders_count],
                        'purchase_orders_count' => [$user->purchaser_purchase_orders_count],
                        'sales_orders_count'    => [$user->seller_sales_orders_count],
                        'work_orders_count'     => [$user->assigned_to_work_orders_count],
                    ]);

                    $data = array_merge($data, [
                        'preOrders'      => Inertia::lazy(fn () => PreOrderService::getPreOrdersDataTableByBuilder($user->purchaserPreOrders()->getQuery(), true)->run()),
                        'purchaseOrders' => Inertia::lazy(fn () => PurchaseOrderService::getPurchaseOrdersDataTableByBuilder($user->purchaserPurchaseOrders()->getQuery(), true)->run()),
                        'salesOrders'    => Inertia::lazy(fn () => SalesOrderService::getSalesOrdersDataTableByBuilder($user->sellerSalesOrders()->getQuery(), true)->run()),
                        'workOrders'     => Inertia::lazy(fn () => WorkOrderService::getWorkOrdersDataTableByBuilder($user->assignedToWorkOrders()->getQuery(), true)->run()),
                    ]);
                    break;

                case 'Customer':
                    $user->loadCount(['customerSalesOrders', 'customerServiceOrders', 'assignedToWorkOrders', 'customerDocuments', 'customerQuotes']);

                    $resourcesCount = array_merge_recursive($resourcesCount, [
                        'sales_orders_count'   => [$user->customer_sales_orders_count],
                        'service_orders_count' => [$user->customer_service_orders_count],
                        'work_orders_count'    => [$user->assigned_to_work_orders_count],
                        'documents_count'      => [$user->customer_documents_count],
                        'quotes_count'         => [$user->customer_quotes_count],
                    ]);

                    $data = array_merge($data, [
                        'salesOrders'   => Inertia::lazy(fn () => SalesOrderService::getSalesOrdersDataTableByBuilder($user->customerSalesOrders()->getQuery(), true)->run()),
                        'serviceOrders' => Inertia::lazy(fn () => ServiceOrderService::getServiceOrdersDataTableByBuilder($user->customerServiceOrders()->getQuery(), true)->run()),
                        'documents'     => Inertia::lazy(fn () => DocumentService::getDocumentsDataTableByBuilder($user->customerDocuments()->getQuery(), true)->run()),
                        'quotes'        => Inertia::lazy(fn () => QuoteService::getQuotesDataTableByBuilder($user->customerQuotes()->getQuery(), true)->run()),
                        'workOrders'    => Inertia::lazy(fn () => WorkOrderService::getWorkOrdersDataTableByBuilder($user->assignedToWorkOrders()->getQuery(), true)->run()),
                    ]);
                    break;
            }
        }

        foreach ($resourcesCount as $key => $values) {
            $resourcesCount[$key] = array_sum($values);
        }

        return array_merge(['resourcesCount' => $resourcesCount], $data);
    }
}
