<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Enums\Transmission;
use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class Inspect implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select('id', 'make_id', 'vehicle_model_id', 'vin', 'supplier_company_id')
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            // ->setRelation('workflow.finishedStepsManagement')
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('salesOrder.customerCompany', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('salesOrder.customerCompany.name', __('End User / Customer'), true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('transmission', __('Transmission'), true, true)
            ->setColumn('workflow.hasReceivedOriginalDocuments', __('Received Original Documents'))
            ->setColumn('workflow.documentsInbound', __('Documents Inbound'))
            ->setColumn('inspection_photos', __('Inspection Photos'))
            ->setEnumColumn('transmission', Transmission::class)
            ->run(config('app.default.pageResults', 10), function ($model) {
                // return $model->whereHas('workflow', function ($workflowQuery) {
                //     $workflowQuery->whereHas('process', function ($processQuery) {
                //         $processQuery->whereHas('subprocesses', function ($subprocessQuery) {
                //             $subprocessQuery->where('name', 'Import');
                //         });
                //     })
                //         ->whereDoesntHave('finishedSteps', function ($finishedStepQuery) {
                //             $finishedStepQuery->whereHas('step', function ($stepQuery) {
                //                 $stepQuery->whereIn('variable_map_name', [
                //                     'hasSampleInspectionApproval',
                //                     'hasPlannedSampleInspection',
                //                     'hasPassedSampleInspection',
                //                 ]);
                //             })
                //                 ->groupBy('workflow_id')
                //                 ->havingRaw('COUNT(DISTINCT workflow_step_id) = 3');
                //         });
                // });
            });
    }
}
