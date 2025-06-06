<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleGroupSeeder extends BaseSeeder
{
    /**
     * Vehicle group names dummy data.
     *
     * @var array|string[]
     */
    private array $groupNames = [
        'Volkswagen Aktiengesellschaft (VAG) Group',
        'Toyota Group',
        'General Motors (GM) Group',
        'Ford Motor Company',
        'Stellantis',
        'BMW Group',
        'Daimler AG',
        'Nissan Motor Corporation',
        'Hyundai Motor Group',
        'Volvo Group',
        'Mitsubishi Motors Corporation',
        'Subaru Corporation',
        'Honda Motor Co., Ltd.',
        'Tata Motors Limited',
        'Renault Group',
        'Geely Auto Group',
        'Mazda Motor Corporation',
        'Suzuki Motor Corporation',
        'Fiat Chrysler Automobiles (FCA)',
        'Kia Motors Corporation',
        'PSA Group (Peugeot Société Anonyme)',
        'Saab Automobile AB',
        'Tesla, Inc.',
        'BYD Company Limited',
        'Changan Automobile Group',
        'SAIC Motor Corporation Limited',
        'FAW Group Corporation',
        'Dongfeng Motor Corporation',
        'GAC Group (Guangzhou Automobile Group Co., Ltd.)',
        'Great Wall Motors',
        'Chery Automobile Co., Ltd.',
        'Isuzu Motors Limited',
        'Hino Motors, Ltd.',
        'Mahindra & Mahindra Limited',
        'McLaren Automotive',
        'Aston Martin Lagonda Global Holdings plc',
        'Jaguar Land Rover Automotive plc',
        'Ferrari N.V.',
        'Porsche Automobil Holding SE',
        'Lamborghini (Automobili Lamborghini S.p.A.)',
        'Bentley Motors Limited',
        'Rolls-Royce Motor Cars Limited',
        'Maserati S.p.A.',
        'Pagani Automobili S.p.A.',
        'Lucid Motors',
        'Rivian Automotive, LLC',
        'NIO Inc.',
        'XPeng Motors',
        'VinFast',
        'Koenigsegg Automotive AB',
        'Fisker Inc.',
        'Alfa Romeo Automobiles S.p.A.',
        'Lancia Automobiles S.p.A.',
        'Smart Automobile Co., Ltd.',
        'SEAT, S.A.',
        'Cupra',
        'Skoda Auto',
        'Scania AB',
        'MAN SE',
        'Daihatsu Motor Co., Ltd.',
        'Proton Holdings Berhad',
        'Perodua',
        'BAIC Group (Beijing Automotive Group Co., Ltd.)',
        'Brilliance Auto Group',
        'Foton Motor Group',
        'Navistar International Corporation',
        'Paccar Inc.',
        'Troller Veículos Especiais S/A',
        'Wuling Motors',
        'Zotye Auto',
        'JAC Motors (Anhui Jianghuai Automobile Co., Ltd.)',
        'Yutong Group',
        'King Long Motor Group',
        'Lion Electric Company',
        'Nikola Corporation',
        'Workhorse Group Inc.',
        'Lordstown Motors Corp.',
        'Polestar',
    ];

    /**
     * Create a new VehicleGroupSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $groupInserts = [];

        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyAdministratorId) {
            foreach ($this->groupNames as $groupName) {
                $groupInserts[] = [
                    'creator_id' => $companyAdministratorId,
                    'name'       => $groupName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('vehicle_groups')->insert($groupInserts);
    }
}
