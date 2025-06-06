<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyType;
use App\Enums\Country;
use App\Enums\Currency;
use App\Models\Company;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CrmCompanySeeder extends BaseSeeder
{
    /**
     * User that can create companies
     *
     * @var User
     */
    private User $userCanCreateCompanies;

    private array $countryMap;

    /**
     * Create a new CompanySeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userCanCreateCompanies = User::where('email', 'marnix@vehicx.nl')->first();
        $this->countryMap = [
            'A'   => Country::Austria->value,
            'AT'  => Country::Austria->value,
            'B'   => Country::Belgium->value,
            'BE'  => Country::Belgium->value,
            'BG'  => Country::Bulgaria->value,
            'CH'  => Country::Switzerland->value,
            'CZ'  => Country::Czech_Republic->value,
            'D'   => Country::Germany->value,
            'DE'  => Country::Germany->value,
            'DK'  => Country::Denmark->value,
            'E'   => Country::Spain->value,
            'ES'  => Country::Spain->value,
            'F'   => Country::France->value,
            'H'   => Country::Hungary->value,
            'HU'  => Country::Hungary->value,
            'I'   => Country::Italy->value,
            'IT'  => Country::Italy->value,
            'L'   => Country::Luxembourg->value,
            'LT'  => Country::Lithuania->value,
            'N'   => Country::Norway->value,
            'NL'  => Country::Netherlands->value,
            'NO'  => Country::Norway->value,
            'P'   => Country::Portugal->value,
            'PL'  => Country::Poland->value,
            'RO'  => Country::Romania->value,
            'S'   => Country::Sweden->value,
            'SE'  => Country::Sweden->value,
            'SI'  => Country::Slovenia->value,
            'SK'  => Country::Slovakia->value,
            'SLO' => Country::Slovenia->value,
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = storage_path('app/public/ImportVehicx20252104.xlsx');

        $spreadsheet = IOFactory::load($filePath);
        $sheet1 = $spreadsheet->getSheet(0)->toArray();

        $companyInserts = [];
        foreach (array_slice($sheet1, 1) as $row) {
            $companyInserts[] = [
                'id'                                => $row[0],
                'type'                              => CompanyType::General,
                'default_currency'                  => Currency::Euro_EUR->value,
                'creator_id'                        => $this->userCanCreateCompanies->id,
                'company_number_accounting_system'  => $row[1],
                'name'                              => $row[5],
                'creditor_number_accounting_system' => $row[6],
                'debtor_number_accounting_system'   => $row[7],
                'kvk_number'                        => $row[8],
                'phone'                             => $row[9],
                'email'                             => $row[10],
                'address'                           => $row[11],
                'street'                            => $row[12],
                'address_number'                    => $row[13],
                'address_number_addition'           => $row[14],
                'postal_code'                       => $row[15],
                'city'                              => $row[16],
                'country'                           => $this->countryMap[$row[17]],
            ];
        }

        Company::insert($companyInserts);
    }
}
