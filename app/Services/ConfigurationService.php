<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InvalidCompanyConfigNameSpace;
use App\Models\Company;
use Illuminate\Support\Collection;

class ConfigurationService extends Service
{
    private ?Collection $companyConfigs = null;

    public function __construct()
    {
        // Initialize any required dependencies here if necessary
    }

    /**
     * Get the configuration for a company and type.
     *
     * @param  Company                       $company
     * @param  null|string                   $type
     * @return string
     * @throws InvalidCompanyConfigNameSpace
     */
    public function getConfig(Company $company, ?string $type = null): string
    {
        if ($this->companyConfigs === null) {
            $this->initCompanyConfigs($company);
        }

        if ($type === null) {
            return $this->getDefaultConfig();
        }

        $config = $this->companyConfigs->firstWhere('type', $type);

        if (! $config || ! $config->value) {
            throw new InvalidCompanyConfigNameSpace(sprintf('Configuration for type "%s" not found or invalid.', $type));
        }

        return $config->value;
    }

    /**
     * Initialize all configurations for a company.
     *
     * @param  Company $company
     * @return void
     */
    private function initCompanyConfigs(Company $company): void
    {
        $this->companyConfigs = $company->configurations;
    }

    /**
     * Get the default configuration if no type is provided.
     *
     * @return string
     * @throws InvalidCompanyConfigNameSpace
     */
    private function getDefaultConfig(): string
    {
        $defaultConfig = $this->companyConfigs->first();

        if (! $defaultConfig || ! $defaultConfig->value) {
            throw new InvalidCompanyConfigNameSpace('Default configuration is not available or invalid.');
        }

        return $defaultConfig->value;
    }
}
