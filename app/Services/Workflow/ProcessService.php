<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Services\ConfigurationService;
use App\Support\NamespaceResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

class ProcessService
{
    /**
     * Get all company processes, including their keys, names, and class names.
     *
     * @return Collection
     */
    public static function getCompanyProcesses(): Collection
    {
        $configurationService = new ConfigurationService();

        $companyNamespace = $configurationService->getConfig(Auth::user()->creator->company, 'workflowNamespace');
        $namespace = "App/Services/Workflow/Companies/{$companyNamespace}/Processes";

        // Get all class namespaces in the specified namespace
        $processNamespaces = NamespaceResolver::getClassesInNamespace($namespace);

        $processes = new Collection();

        foreach ($processNamespaces as $key => $processNamespace) {
            $reflectionClass = new ReflectionClass($processNamespace);

            $name = $reflectionClass->getReflectionConstant('NAME')->getValue();
            $className = $reflectionClass->getShortName();

            $processes->put($key, [
                'className' => $className,
                'name'      => $name,
            ]);
        }

        return $processes;
    }
}
