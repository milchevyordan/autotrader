<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Traits\FakerDateTimeTrait;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    use FakerDateTimeTrait;

    /**
     * @var Generator
     */
    protected Generator $faker;

    /**
     * Return array where key is company id and value is company administrator user id.
     *
     * @var array|int[]
     */
    protected array $companyCompanyAdministratorIdsMap = [];

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Filter users.
     *
     * @param  array $users
     * @param  int   $companyId
     * @return array
     */
    public function getUsersByCompany(array $users, int $companyId): array
    {
        return array_filter($users, function ($company) use ($companyId) {
            return $companyId == $company;
        });
    }

    private function setCompanyCompanyAdministratorIdsMap(): void
    {
        $this->companyCompanyAdministratorIdsMap = [
            1 => 2,
        ];
    }

    /**
     * Get the creator company map, company id is key, value is company administrator id.
     *
     * @return int[]
     */
    public function getCompanyCompanyAdministratorIdsMap(): array
    {
        if (! $this->companyCompanyAdministratorIdsMap) {
            $this->setCompanyCompanyAdministratorIdsMap();
        }

        return $this->companyCompanyAdministratorIdsMap;
    }
}
