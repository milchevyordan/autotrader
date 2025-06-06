<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx;

use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\ServiceOrder;
use App\Models\TransportOrder;
use App\Models\WorkOrder;
use App\Services\Workflow\BaseDatabaseRelationsInitiator;

class DatabaseRelationsInitiator extends BaseDatabaseRelationsInitiator
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getRelations(): array
    {
        return [
            'App\\Models\\Vehicle' => [
                'columnsToSelect' => [
                    'id',
                    'creator_id',
                    'image_path',
                    'current_registration',
                    'make_id',
                    'vehicle_model_id',
                    'variant_id',
                    'body',
                    'engine_id',
                    'creator_id',
                    'specific_exterior_color',
                    'transmission',
                    'interior_material',
                    'specific_interior_color',
                    'vin',
                    'kw',
                    'supplier_company_id',
                    'supplier_id',
                    'nl_registration_number',
                    'identification_code',
                    'co2_wltp',
                    'co2_nedc',
                    'stock',
                    'kilometers',
                    'first_registration_date',
                    'factory_name_color',
                    'created_at',
                ],

                'relations' => [
                    'engine:id,name',
                    'make:id,name',
                    'variant:id,name',
                    'vehicleModel:id,name',
                    'supplier:id,email',
                    'supplierCompany' => function ($query) {
                        $query->select('id', 'name', 'email');
                    },
                    'supplierCompany.addresses' => function ($query) {
                        $query->select('id', 'company_id', 'type', 'address');
                    },
                    'purchaseOrder' => function ($query) {
                        $query->select(PurchaseOrder::$summarySelectFields);
                    },
                    'purchaseOrder.supplier:id,email',
                    'purchaseOrder.supplierCompany' => function ($query) {
                        $query->select('id', 'name', 'email');
                    },
                    'purchaseOrder.intermediary:id,company_id',
                    'purchaseOrder.intermediary.company' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'purchaseOrder.purchaser:id,full_name',
                    'purchaseOrder.files',
                    'salesOrder' => function ($query) {
                        $query->select(array_merge(SalesOrder::$summarySelectFields, ['sales_orders.delivery_week']));
                    },
                    'salesOrder.customer:id,company_id,full_name,email',
                    'salesOrder.customer.company' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'salesOrder.seller:id,full_name',
                    'salesOrder.orderServices',
                    'salesOrder.orderItems',
                    'salesOrder.files',
                    'quotes' => function ($query) {
                        $query->select(Quote::$summarySelectFields);
                    },
                    'quotes.creator',
                    'quotes.customer:id,company_id,full_name',
                    'quotes.customer.company' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'transportOrders' => function ($query) {
                        $query->select(array_merge(TransportOrder::$summarySelectFields, ['transport_orders.created_at', 'deliver_before_date', 'pick_up_after_date', 'deliver_before_date', 'planned_delivery_date']));
                    },
                    'transportOrders.statuses',
                    'transportOrders.creator:id,full_name',
                    'transportOrders.transporter:id,company_id,full_name',
                    'transportOrders.transporter.company' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'transportOrders.files',
                    'workOrder' => function ($query) {
                        $query->select(WorkOrder::$summarySelectFields);
                    },
                    'workOrder.files',
                    'workOrder.statuses',
                    'workOrder.creator',
                    'workOrder.tasks.files',
                    'workOrder.tasks:id,work_order_id,created_at,type,planned_date,completed_at,status',
                    'documents' => function ($query) {
                        $query->select(array_merge(Document::$summarySelectFields, ['documents.created_at', 'paid_at']));
                    },
                    'documents.creator',
                    'documents.statuses',
                    'documents.files',
                    'documents.lines',
                    'creator',
                ],
            ],

            'App\\Models\\ServiceVehicle' => [
                'columnsToSelect' => [
                    'id',
                    'creator_id',
                    'make_id',
                    'vehicle_model_id',
                    'current_registration',
                    'vehicle_type',
                    'co2_type',
                    'kilometers',
                    'vin',
                    'first_registration_date',
                    'created_at',
                ],

                'relations' => [
                    'make:id,name',
                    'vehicleModel:id,name',
                    'serviceOrder' => function ($query) {
                        $query->select(ServiceOrder::$summarySelectFields);
                    },
                    'serviceOrder.creator:id,full_name',
                    'serviceOrder.customer:id,company_id,full_name',
                    'serviceOrder.customer.company' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'serviceOrder.seller:id,full_name',
                    'serviceOrder.files',
                    'serviceOrder.images',
                    'transportOrders' => function ($query) {
                        $query->select(array_merge(TransportOrder::$summarySelectFields, ['transport_orders.created_at', 'deliver_before_date', 'pick_up_after_date', 'deliver_before_date', 'planned_delivery_date']));
                    },
                    'transportOrders.statuses',
                    'transportOrders.creator:id,full_name',
                    'transportOrders.transporter:id,company_id,full_name',
                    'transportOrders.transportCompany' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'transportOrders.files',
                    'workOrder' => function ($query) {
                        $query->select(WorkOrder::$summarySelectFields);
                    },
                    'workOrder.files',
                    'workOrder.statuses',
                    'workOrder.creator',
                    'workOrder.tasks.files',
                    'workOrder.tasks:id,work_order_id,created_at,type,planned_date,completed_at,status',
                    'documents' => function ($query) {
                        $query->select(array_merge(Document::$summarySelectFields, ['documents.created_at', 'paid_at']));
                    },
                    'documents.creator',
                    'documents.statuses',
                    'documents.files',
                    'documents.lines',
                    'creator',
                ],
            ],
        ];
    }
}
