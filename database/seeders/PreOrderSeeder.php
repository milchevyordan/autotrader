<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PreOrderSeeder extends BaseSeeder
{
    /**
     * Array of users that have the permission to create preorder.
     *
     * @var array
     */
    private array $userCanCreatePreOrderIds;

    /**
     * Create a new PreOrderSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreatePreOrderIds = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-pre-order');
        })->pluck('company_id', 'id')->all();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preparedArrayData = [];

        foreach ($this->userCanCreatePreOrderIds as $userId => $companyId) {
            $crmCompanyIds = Company::crmCompanies(CompanyType::Supplier->value)
                ->whereHas('creator', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })->get(['id'])->pluck('id')->all();

            $crmData = [
                'supplierIds'     => User::whereIn('company_id', $crmCompanyIds)->role('Supplier')->pluck('id', 'company_id'),
                'intermediaryIds' => User::whereIn('company_id', $crmCompanyIds)->role('Intermediary')->pluck('id', 'company_id'),
                'purchaserIds'    => User::where('company_id', $companyId)->role('Company Purchaser')->pluck('id'),
            ];

            for ($i = 0; $i < $this->faker->numberBetween(3, 7); $i++) {
                $isVatDepositTrue = $this->faker->boolean();

                $isDownPaymentTrue = $this->faker->boolean();
                $downPayment = $isDownPaymentTrue ? $this->faker->numberBetween(500, 5000) : null;

                $supplier = $this->faker->randomElement($crmData['supplierIds']);
                $intermediary = $this->faker->randomElement($crmData['intermediaryIds']);

                $preparedArrayData[] = [
                    'creator_id'              => $userId,
                    'supplier_company_id'     => $crmData['supplierIds']->search($supplier),
                    'supplier_id'             => $this->faker->boolean() ? $supplier : null,
                    'intermediary_company_id' => $crmData['intermediaryIds']->search($intermediary),
                    'intermediary_id'         => $this->faker->boolean() ? $intermediary : null,
                    'purchaser_id'            => $this->faker->randomElement($crmData['purchaserIds']),
                    'type'                    => $this->faker->numberBetween(1, 3),
                    'document_from_type'      => $this->faker->numberBetween(1, 2),
                    'transport_included'      => $this->faker->boolean(),
                    'vat_deposit'             => $isVatDepositTrue,
                    'vat_percentage'          => $isVatDepositTrue ? 21 : null,
                    'down_payment'            => $isDownPaymentTrue,
                    'down_payment_amount'     => $downPayment,
                    'currency_rate'           => 1,
                    'currency_po'             => $this->faker->numberBetween(1, 6),
                    'contact_notes'           => $this->faker->sentence(),
                    'created_at'              => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    'updated_at'              => date('Y-m-d H:i:s'),
                ];
            }
        }

        DB::table('pre_orders')->insert($preparedArrayData);
    }
}
