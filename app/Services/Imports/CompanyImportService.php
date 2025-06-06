<?php

declare(strict_types=1);

namespace App\Services\Imports;

use App\Enums\CompanyType;
use App\Enums\Country;
use App\Enums\Currency;
use App\Models\Company;
use App\Models\User;
use App\Services\CsvReader;
use App\Services\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CompanyImportService extends Service
{
    const COLUMNS = [
        'id',
        'default_currency',
        'name',
        'company_number_accounting_system',
        'debtor_number_accounting_system',
        'kvk_number',
        'phone',
        'email',
        'address',
        'street',
        'address_number',
        'address_number_addition',
        'postal_code',
        'city',
        'country',
    ];

    public function importFromCsv(string $filePath, User $creator): void
    {
        $csvReader = new CsvReader($filePath);
        $csvReader->read(self::COLUMNS, ';');
        $companies = $csvReader->getData();
        $creatorId = $creator->id;

        $companyInserts = [];

        foreach (array_slice($companies, 1) as $company) {
            $companyEnumType = CompanyType::General;
            $currencyEnum = Currency::getCaseByName($this->toEnumCaseFormat($company['default_currency']));
            $countryEnum = Country::getCaseByName($this->toEnumCaseFormat($company['country']));

            $companyInserts[] = [
                ...$company,
                'type'             => $companyEnumType->value,
                'default_currency' => $currencyEnum->value,
                'creator_id'       => $creatorId,
                'country'          => $countryEnum->value,
            ];

        }

        Company::insert($companyInserts);

    }

    private function toEnumCaseFormat(string $input): string
    {
        return str_replace(' ', '_', $input);
    }
}
