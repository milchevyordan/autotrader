<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CacheTag;
use App\Enums\Gender;
use App\Enums\Locale;
use App\Enums\OwnershipStatus;
use App\Services\MultiSelectService;
use App\Services\RoleService;
use App\Traits\HasChangeLogs;
use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use HasImages;
    use HasCreator;
    use HasChangeLogs;
    use HasImages;
    use HasFiles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'prefix',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'other_phone',
        'gender',
        'language',
        'id_card_expiry_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'   => 'datetime',
        'id_card_expiry_date' => 'datetime:Y-m-d',
        'password'            => 'hashed',
        'gender'              => Gender::class,
        'language'            => Locale::class,
    ];

    /**
     * Profile image section from all images related to the user.
     *
     * @var string
     */
    public string $profileImageSection = 'profileImages';

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'company_id', 'creator_id', 'full_name', 'email', 'created_at', 'updated_at'];

    /**
     * User's company relation.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Return companies that the user has created.
     *
     * @return HasMany
     */
    public function createdCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'creator_id');
    }

    /**
     * Return vehicles that the user has followed.
     *
     * @return MorphToMany
     */
    public function followVehicles(): MorphToMany
    {
        return $this->morphedByMany(Vehicle::class, 'followable');
    }

    /**
     * Return users that have crm roles.
     *
     * @return mixed
     */
    public function scopeHasCrmRoles(): mixed
    {
        $crmRoleIds = RoleService::getCrmRoles()->pluck('id')->all();

        return $this->whereHas('roles', function ($rolesQuery) use ($crmRoleIds) {
            $rolesQuery->whereIn('roles.id', $crmRoleIds);
        });
    }

    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<DatabaseNotification, $this>
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable');
    }

    /**
     * Return users with crm roles in the company.
     *
     * @param        $crmCompanyId
     * @return mixed
     */
    public static function getCrmCompanyUsers($crmCompanyId = null): mixed
    {
        if (! $crmCompanyId) {
            return static::inThisCompany()->role(RoleService::getCrmRoles()->pluck('name')->all());
        }

        return static::where('company_id', $crmCompanyId);
    }

    /**
     * Return users with main roles in the company.
     *
     * @param        $companyId
     * @return mixed
     */
    public static function getMainCompanyUsers($companyId = null): mixed
    {
        if (! $companyId) {
            return static::inThisCompany()->role(RoleService::getMainCompanyRoles()->pluck('name')->all());
        }

        return static::where('company_id', $companyId);
    }

    /**
     * Return multiselect collection of users with crm roles in the company.
     *
     * @param             $crmCompanyId
     * @return Collection
     */
    public static function getCrmCompanyUsersSelect($crmCompanyId = null): Collection
    {
        return (new MultiSelectService(static::getCrmCompanyUsers($crmCompanyId)))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Return multiselect collection of users with main roles in the company.
     *
     * @param             $companyId
     * @return Collection
     */
    public static function getMainCompanyUsersSelect($companyId = null): Collection
    {
        return (new MultiSelectService(static::getMainCompanyUsers($companyId)))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Return user groups relation.
     *
     * @return BelongsToMany
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class);
    }

    /**
     * Return user map of users having role provided and map key value provided.
     *
     * @param        $role
     * @param        $key
     * @return array
     */
    public static function withRoleMapByColumn($role, $key): array
    {
        $mappedArray = [];

        $data = self::role($role)
            ->select($key, 'id')
            ->get();

        foreach ($data as $row) {
            $keyId = $row->{$key};
            $valueId = $row->id;

            if (! isset($mappedArray[$keyId])) {
                $mappedArray[$keyId] = [];
            }

            $mappedArray[$keyId][] = $valueId;
        }

        return $mappedArray;
    }

    /**
     * Return user's created preorders.
     *
     * @return HasMany
     */
    public function preOrders(): HasMany
    {
        return $this->hasMany(PreOrder::class, 'creator_id');
    }

    /**
     * Return preorders where user is supplier in.
     *
     * @return HasMany
     */
    public function supplierPreOrders(): HasMany
    {
        return $this->hasMany(PreOrder::class, 'supplier_id');
    }

    /**
     * Return preorders where user is intermediary in.
     *
     * @return HasMany
     */
    public function intermediaryPreOrders(): HasMany
    {
        return $this->hasMany(PreOrder::class, 'intermediary_id');
    }

    /**
     * Return preorders where user is purchaser in.
     *
     * @return HasMany
     */
    public function purchaserPreOrders(): HasMany
    {
        return $this->hasMany(PreOrder::class, 'purchaser_id');
    }

    /**
     * Return user's created purchase orders.
     *
     * @return HasMany
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'creator_id');
    }

    /**
     * Return purchase orders where user is supplier in.
     *
     * @return HasMany
     */
    public function supplierPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    /**
     * Return purchase orders where user is intermediary in.
     *
     * @return HasMany
     */
    public function intermediaryPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'intermediary_id');
    }

    /**
     * Return purchase orders where user is purchaser in.
     *
     * @return HasMany
     */
    public function purchaserPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'purchaser_id');
    }

    /**
     * Return user's created sales orders.
     *
     * @return HasMany
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'creator_id');
    }

    /**
     * Return sales orders where user is purchaser in.
     *
     * @return HasMany
     */
    public function sellerSalesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'seller_id');
    }

    /**
     * Return sales orders where user is customer in.
     *
     * @return HasMany
     */
    public function customerSalesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    /**
     * Return user's created service orders.
     *
     * @return HasMany
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'creator_id');
    }

    /**
     * Return service orders where user is customer in.
     *
     * @return HasMany
     */
    public function customerServiceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'customer_id');
    }

    /**
     * Return user's created work orders.
     *
     * @return HasMany
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'creator_id');
    }

    /**
     * Return work orders that user has assigned to him work order tasks.
     *
     * @return HasManyThrough
     */
    public function assignedToWorkOrders(): HasManyThrough
    {
        return $this->hasManyThrough(
            WorkOrder::class,
            WorkOrderTask::class,
            'assigned_to_id',
            'id',
            'id',
            'work_order_id'
        )->with([
            'creator',
        ])->distinct();
    }

    /**
     * Return user's created transport orders.
     *
     * @return HasMany
     */
    public function transportOrders(): HasMany
    {
        return $this->hasMany(TransportOrder::class, 'creator_id');
    }

    /**
     * Return transport orders where user is transport supplier in.
     *
     * @return HasMany
     */
    public function transportCompanyTransportOrders(): HasMany
    {
        return $this->hasMany(TransportOrder::class, 'transporter_id');
    }

    /**
     * Return user's created service levels.
     *
     * @return HasMany
     */
    public function serviceLevels(): HasMany
    {
        return $this->hasMany(ServiceLevel::class, 'creator_id');
    }

    /**
     * Return user's created items.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'creator_id');
    }

    /**
     * Return user's created documents.
     *
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'creator_id');
    }

    /**
     * Return documents where user is customer in.
     *
     * @return HasMany
     */
    public function customerDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'customer_id');
    }

    /**
     * Return user's created quotes.
     *
     * @return HasMany
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'creator_id');
    }

    /**
     * Return quotes where user is customer in.
     *
     * @return HasMany
     */
    public function customerQuotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'customer_id');
    }

    /**
     * Return user's created vehicles.
     *
     * @return HasMany
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'creator_id');
    }

    /**
     * Return vehicles where user is supplier in.
     *
     * @return HasMany
     */
    public function supplierVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'supplier_id');
    }

    /**
     * Return user's created preorder vehicles.
     *
     * @return HasMany
     */
    public function preOrderVehicles(): HasMany
    {
        return $this->hasMany(PreOrderVehicle::class, 'creator_id');
    }

    /**
     * Return preorder vehicles where user is supplier in.
     *
     * @return HasMany
     */
    public function supplierPreOrderVehicles(): HasMany
    {
        return $this->hasMany(PreOrderVehicle::class, 'supplier_id');
    }

    /**
     * Return user's created service vehicles.
     *
     * @return HasMany
     */
    public function serviceVehicles(): HasMany
    {
        return $this->hasMany(ServiceVehicle::class, 'creator_id');
    }

    /**
     * Return user's created makes.
     *
     * @return HasMany
     */
    public function makes(): HasMany
    {
        return $this->hasMany(Make::class, 'creator_id');
    }

    /**
     * Return user's created vehicle models.
     *
     * @return HasMany
     */
    public function vehicleModels(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'creator_id');
    }

    /**
     * Return user's created vehicle groups.
     *
     * @return HasMany
     */
    public function vehicleGroups(): HasMany
    {
        return $this->hasMany(VehicleGroup::class, 'creator_id');
    }

    /**
     * Return user's created engines.
     *
     * @return HasMany
     */
    public function engines(): HasMany
    {
        return $this->hasMany(Engine::class, 'creator_id');
    }

    /**
     * Return user's created variants.
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class, 'creator_id');
    }

    /**
     * Return user's created companies.
     *
     * @return HasMany
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'creator_id');
    }

    /**
     * Return user's created company groups.
     *
     * @return HasMany
     */
    public function companyGroups(): HasMany
    {
        return $this->hasMany(CompanyGroup::class, 'creator_id');
    }

    /**
     * Return user's created users.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany($this, 'creator_id');
    }

    /**
     * Return user's created user groups.
     *
     * @return HasMany
     */
    public function createdUserGroups(): HasMany
    {
        return $this->hasMany(UserGroup::class, 'creator_id');
    }

    /**
     * Return ownerships of the user.
     *
     * @return HasMany
     */
    public function ownerships(): HasMany
    {
        return $this->hasMany(Ownership::class, 'user_id');
    }

    /**
     * Return pending ownerships of the user.
     *
     * @return HasMany
     */
    public function pendingOwnerships(): HasMany
    {
        return $this->ownerships()->where('status', OwnershipStatus::Pending);
    }

    /**
     * Relation to password reset token
     *
     * @return HasOne
     */
    public function passwordResetToken(): HasOne
    {
        return $this->hasOne(PasswordResetToken::class, 'email', 'email');
    }
}
