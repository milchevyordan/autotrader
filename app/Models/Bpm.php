<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FuelType;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bpm extends Model
{
    use HasFactory;

    /**
     * Get bpm values based on request provided.
     *
     * @param            $date
     * @param            $fuelId
     * @param            $co2
     * @return mixed
     * @throws Exception
     */
    public static function getValues($date, $fuelId, $co2): mixed
    {
        $year = (new DateTime($date))->format('Y');

        return (new self())
            ->where('year', $year)
            ->where('fuel_type', $fuelId)
            ->where(function ($q) use ($co2) {
                $q
                    ->where('co2_min', '<', $co2)
                    ->where(function ($q) use ($co2) {
                        $q
                            ->where('co2_max', '>=', $co2)
                            ->orWhereNull('co2_max');
                    });
            });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active'    => 'boolean',
        'fuel_type' => FuelType::class,
    ];
}
