<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleTransferToken extends Model
{
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['vehicle_id', 'token', 'created_at'];

    protected $primaryKey = 'vehicle_id';

    public $incrementing = false;

    /**
     * Relation to the vehicle
     *
     * @return BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
