<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ItemType;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends BaseSeeder
{
    /**
     * Ids of the users that have the permission to create items.
     *
     * @var array
     */
    private array $itemInserts = [];

    /**
     * Services dummy data.
     *
     * @var array|array[]
     */
    private array $services = [
        [
            'shortcode'   => 'Prof detailing',
            'description' => 'Comprehensive professional vehicle detailing service to restore and enhance your vehicle\'s appearance. Includes thorough cleaning, polishing, and waxing.',
        ],
        [
            'shortcode'   => 'Paint film install',
            'description' => 'Expert installation of paint protection film to shield your vehicle\'s paint from chips, scratches, and environmental damage.',
        ],
        [
            'shortcode'   => 'Engine tuning',
            'description' => 'Specialized engine tuning and performance upgrades to optimize your vehicle\'s power, fuel efficiency, and overall performance.',
        ],
        [
            'shortcode'   => 'Custom wrapping',
            'description' => 'Unique and custom vehicle wrapping services to transform and personalize your vehicle\'s appearance.',
        ],
        [
            'shortcode'   => 'Window tinting',
            'description' => 'Professional window tinting services to enhance privacy, reduce glare, and protect your vehicle\'s interior from UV rays.',
        ],
        [
            'shortcode'   => 'Interior restoration',
            'description' => 'Comprehensive vehicle interior restoration to bring new life to worn or damaged interior components. Includes cleaning, repairs, and refinishing.',
        ],
        [
            'shortcode'   => 'Mobile wash & wax',
            'description' => 'Convenient and high-quality mobile vehicle wash and wax services, bringing professional cleaning to your location.',
        ],
        [
            'shortcode'   => 'Ceramic coating',
            'description' => 'Application of ceramic coating for long-lasting protection, enhancing the shine of your vehicle\'s paint and providing resistance against contaminants.',
        ],
        [
            'shortcode'   => 'Rim repair',
            'description' => 'Rim repair and refinishing services to restore the appearance and functionality of damaged or worn-out rims.',
        ],
        [
            'shortcode'   => 'Upholstery repair',
            'description' => 'Expert automotive upholstery repair to fix and renew your vehicle\'s seats, vehiclepets, and interior surfaces.',
        ],
        [
            'shortcode'   => 'LED lighting',
            'description' => 'Custom LED lighting installation to add a unique and stylish touch to your vehicle\'s interior or exterior.',
        ],
        [
            'shortcode'   => 'Audio system upgrade',
            'description' => 'Enhance your driving experience with a professional vehicle audio system upgrade, including speakers, amplifiers, and head units.',
        ],
        [
            'shortcode'   => 'Windshield repair',
            'description' => 'Prompt and effective windshield repair and replacement services to address chips, cracks, or other damages.',
        ],
        [
            'shortcode'   => 'Diagnostics & repair',
            'description' => 'Advanced vehicle diagnostics and repair services to identify and fix issues with your vehicle\'s performance and systems.',
        ],
        [
            'shortcode'   => 'Alarm installation',
            'description' => 'Installation of a reliable vehicle alarm system to enhance the security of your vehicle and deter theft.',
        ],
        [
            'shortcode'   => 'Headlight restoration',
            'description' => 'Professional headlight restoration to improve visibility and appearance by addressing cloudiness, yellowing, or scratches.',
        ],
        [
            'shortcode'   => 'Wheel alignment',
            'description' => 'Precise wheel alignment and balancing services to ensure optimal tire wear, handling, and overall vehicle performance.',
        ],
        [
            'shortcode'   => 'Exhaust system install',
            'description' => 'Installation of a performance exhaust system to enhance your vehicle\'s horsepower, torque, and exhaust note.',
        ],
        [
            'shortcode'   => 'Body repair & painting',
            'description' => 'Expert auto body repair and painting services to restore the aesthetics and structural integrity of your vehicle.',
        ],
        [
            'shortcode'   => 'Hybrid & electric services',
            'description' => 'Specialized services for hybrid and electric vehicles, including maintenance, repairs, and performance enhancements.',
        ],
        [
            'shortcode'   => 'Battery replacement',
            'description' => 'Swift and professional vehicle battery replacement services to ensure your vehicle\'s reliable starting power.',
        ],
        [
            'shortcode'   => 'Brake system upgrade',
            'description' => 'Upgrade and repair services for your vehicle\'s brake system, ensuring optimal stopping power and safety.',
        ],
        [
            'shortcode'   => 'Suspension modification',
            'description' => 'Custom suspension system modifications to enhance your vehicle\'s handling, comfort, and overall performance.',
        ],
        [
            'shortcode'   => 'Engine rebuilding',
            'description' => 'Comprehensive engine rebuilding services to restore or enhance your vehicle\'s engine performance and longevity.',
        ],
        [
            'shortcode'   => 'Tire rotation',
            'description' => 'Regular tire rotation and balancing services to ensure even tire wear, better handling, and improved fuel efficiency.',
        ],
    ];

    /**
     * Products dummy data.
     *
     * @var array|array[]
     */
    private array $products = [
        [
            'shortcode'   => 'Tire',
            'description' => 'High-performance all-season tire for a smooth and safe ride',
        ],
        [
            'shortcode'   => 'Vehicle Mirror',
            'description' => 'Replacement side mirror with wide-angle view for enhanced safety',
        ],
        [
            'shortcode'   => 'Vehicle Door',
            'description' => 'OEM-quality vehicle door for reliable and secure vehicle entry',
        ],
        [
            'shortcode'   => 'Engine Oil',
            'description' => 'Premium synthetic engine oil for optimal lubrication and performance',
        ],
        [
            'shortcode'   => 'Vehicle Battery',
            'description' => 'Long-lasting and reliable vehicle battery for consistent power',
        ],
        [
            'shortcode'   => 'Windshield Wipers',
            'description' => 'Durable and streak-free windshield wipers for clear visibility',
        ],
        [
            'shortcode'   => 'Brake Pads',
            'description' => 'High-performance brake pads for responsive and safe braking',
        ],
        [
            'shortcode'   => 'Air Filter',
            'description' => 'Efficient air filter for improved engine air quality and performance',
        ],
        [
            'shortcode'   => 'Vehicle Seat Covers',
            'description' => 'Comfortable and stylish seat covers for a personalized interior',
        ],
        [
            'shortcode'   => 'Steering Wheel Cover',
            'description' => 'Grip-enhancing steering wheel cover for a comfortable drive',
        ],
        [
            'shortcode'   => 'Vehicle Floor Mats',
            'description' => 'All-weather floor mats to protect your vehicle`s interior',
        ],
        [
            'shortcode'   => 'Vehicle Polish',
            'description' => 'Professional-grade vehicle polish for a shiny and protective finish',
        ],
        [
            'shortcode'   => 'Vehicle Wax',
            'description' => 'Premium vehicle wax for long-lasting paint protection and shine',
        ],
        [
            'shortcode'   => 'Vehicle Shampoo',
            'description' => 'Gentle and effective vehicle shampoo for a thorough cleaning',
        ],
        [
            'shortcode'   => 'Spark Plugs',
            'description' => 'High-performance spark plugs for improved engine efficiency',
        ],
        [
            'shortcode'   => 'Radiator Coolant',
            'description' => 'Premium radiator coolant for optimal engine temperature control',
        ],
        [
            'shortcode'   => 'Transmission Fluid',
            'description' => 'Transmission fluid for smooth and reliable gear shifts',
        ],
        [
            'shortcode'   => 'Vehicle Jack',
            'description' => 'Heavy-duty vehicle jack for safe and easy lifting',
        ],
        [
            'shortcode'   => 'Vehicle Tool Kit',
            'description' => 'Comprehensive tool kit for basic vehicle maintenance and repairs',
        ],
        [
            'shortcode'   => 'Vehicle GPS Navigation System',
            'description' => 'Advanced GPS navigation system with real-time traffic updates',
        ],
        [
            'shortcode'   => 'Vehicle Alarm System',
            'description' => 'State-of-the-art vehicle alarm system for enhanced security',
        ],
        [
            'shortcode'   => 'Roof Rack',
            'description' => 'Sturdy roof rack for extra vehiclego space during travels',
        ],
        [
            'shortcode'   => 'Vehicle Phone Mount',
            'description' => 'Adjustable vehicle phone mount for safe and convenient hands-free use',
        ],
        [
            'shortcode'   => 'Jump Starter',
            'description' => 'Portable jump starter for emergency battery boosts',
        ],
        [
            'shortcode'   => 'Microfiber Cloth',
            'description' => 'Ultra-absorbent microfiber cloth for effective vehicle cleaning',
        ],
    ];

    /**
     * Create a new ItemSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyAdministratorId) {
            $this->itemInserts = array_merge(
                $this->itemInserts,
                $this->prepareDefaultItemsInsert($companyAdministratorId)
            );

            $this->getItemInsertsForType($this->services, $companyAdministratorId, ItemType::Service->value);
            $this->getItemInsertsForType($this->products, $companyAdministratorId, ItemType::Product->value);
        }

        DB::table('items')->insert($this->itemInserts);
    }

    private function getItemInsertsForType(array $hardcodedArray, int $companyAdministratorId, int $type): void
    {
        foreach ($hardcodedArray as $item) {
            $isVat = $this->faker->boolean();

            $this->itemInserts[] = [
                'creator_id'     => $companyAdministratorId,
                'unit_type'      => 1,
                'type'           => $type,
                'is_vat'         => $isVat,
                'vat_percentage' => 21,
                'shortcode'      => $item['shortcode'],
                'description'    => $item['description'],
                'purchase_price' => $this->faker->boolean ? $this->faker->numberBetween(1000, 25000) : null,
                'sale_price'     => $this->faker->numberBetween(1000, 25000),
                'created_at'     => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            ];
        }
    }

    /**
     * Default items template.
     *
     * @param  int   $companyAdministratorId
     * @return array
     */
    private function prepareDefaultItemsInsert(int $companyAdministratorId): array
    {
        return [
            [
                'creator_id'     => $companyAdministratorId,
                'unit_type'      => 1,
                'type'           => ItemType::Product->value,
                'is_vat'         => true,
                'vat_percentage' => 21,
                'shortcode'      => 'Luxury licenceplates',
                'description'    => 'Two luxury licence plates incl. mounting',
                'purchase_price' => 2800,
                'sale_price'     => 4400,
                'created_at'     => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            ],
            [
                'creator_id'     => $companyAdministratorId,
                'unit_type'      => 1,
                'type'           => ItemType::Service->value,
                'is_vat'         => true,
                'vat_percentage' => 21,
                'shortcode'      => 'Delivery to location',
                'description'    => 'Delivery to location of client',
                'purchase_price' => 7500,
                'sale_price'     => 12500,
                'created_at'     => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            ],
            [
                'creator_id'     => $companyAdministratorId,
                'unit_type'      => 1,
                'type'           => ItemType::Service->value,
                'is_vat'         => true,
                'vat_percentage' => 21,
                'shortcode'      => 'Reconditioning, interior cleaning, showroomready',
                'description'    => 'Reconditioning, interior cleaning, showroomready',
                'purchase_price' => 15000,
                'sale_price'     => 27500,
                'created_at'     => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            ],
        ];
    }
}
