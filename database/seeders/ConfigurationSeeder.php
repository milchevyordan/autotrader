<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Configuration;

class ConfigurationSeeder extends BaseSeeder
{
    /**
     * Company workflow namespaces.
     *
     * @var array
     */
    private array $companyWorkflowNamespaces = [
        [
            'configurable_type' => 'App\Models\Company',
            'configurable_id'   => 1,
            'type'              => 'workflowNamespace',
            'value'             => 'Vehicx',
        ],

        // [
        //     'configurable_type' => 'App\Models\Company',
        //     'configurable_id'   => 2,
        //     'type'              => 'workflowNamespace',
        //     'value'             => 'Company2',
        // ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed the workflowNamespaces
        Configuration::insert($this->companyWorkflowNamespaces);
    }
}
