<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OwnershipStatus;
use App\Models\Company;
use App\Models\Ownership;
use Database\Seeders\Traits\UserPermissionTrait;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;

abstract class OwnershipSeeder extends BaseSeeder
{
    use UserPermissionTrait;

    /**
     * The model for which ownership records are being created.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Get the model instance for which ownership records are being created.
     *
     * @return Model
     */
    abstract protected function getModel(): Model;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ownership::insert($this->generateOwnerships());
    }

    /**
     * Generate ownership insert data.
     *
     * @return array
     */
    private function generateOwnerships(): array
    {
        $faker = Factory::create();
        $companyIds = Company::get(['id'])->pluck('id');

        $entityInsertData = [];
        foreach ($companyIds as $companyId) {
            $usersCanCreateOwnershipIds = $this->getUsersWithPermissionIds('create-ownership', $companyId);

            $entityIds = $this
                ->getModel()
                ->inThisCompany($companyId)
                ->get(['id'])
                ->pluck('id');

            foreach ($entityIds as $entityId) {
                $entityInsertData[] = [
                    'creator_id'   => $faker->randomElement($usersCanCreateOwnershipIds),
                    'user_id'      => $faker->randomElement($usersCanCreateOwnershipIds),
                    'ownable_type' => $this->getModel()::class,
                    'ownable_id'   => $entityId,
                    'status'       => OwnershipStatus::Accepted,
                ];
            }
        }

        return $entityInsertData;
    }
}
