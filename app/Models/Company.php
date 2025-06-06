<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyAddressType;
use App\Enums\CompanyType;
use App\Enums\Country;
use App\Enums\Currency;
use App\Enums\Locale;
use App\Enums\NationalEuOrWorldType;
use App\Traits\HasChangeLogs;
use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasImages;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Company extends Model
{
    use HasFactory;
    use HasCreator;
    use MapByColum;
    use SoftDeletes;
    use HasImages;
    use HasFiles;
    use HasChangeLogs;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',
        'default_currency',
        'country',
        'vat_percentage',
        'purchase_type',
        'locale',
        'name',
        'postal_code',
        'city',
        'address',
        'vat_number',
        'number',
        'number_addition',
        'company_number_accounting_system',
        'debtor_number_accounting_system',
        'creditor_number_accounting_system',
        'website',
        'email',
        'phone',
        'province',
        'street',
        'address_number',
        'address_number_addition',
        'main_user_id',
        'company_group_id',
        'iban',
        'swift_or_bic',
        'bank_name',
        'kvk_number',
        'billing_remarks',
        'logistics_times',
        'pdf_footer_text',
        'logistics_remarks',
        'billing_contact_id',
        'logistics_contact_id',
        'kvk_expiry_date',
        'vat_expiry_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default_currency' => Currency::class,
        'country'          => Country::class,
        'locale'           => Locale::class,
        'purchase_type'    => NationalEuOrWorldType::class,
        'type'             => CompanyType::class,
        'kvk_expiry_date'  => 'datetime:Y-m-d',
        'vat_expiry_date'  => 'datetime:Y-m-d',
    ];

    /**
     * Section where the profile images are.
     *
     * @var string
     */
    public string $profileImageSection = 'logo';

    /**
     * Array holding fields that should be selected in the default dataТable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'type', 'name', 'email', 'kvk_number'];

    /**
     * Return users in the company.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Return delivery contact user in the company.
     *
     * @return BelongsTo
     */
    public function logisticsContact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistics_contact_id');
    }

    /**
     * Return default setting for the company.
     *
     * @return HasOne
     */
    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Return company's main contact.
     *
     * @return BelongsTo
     */
    public function mainUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_user_id');
    }

    /**
     * Return all base companies.
     *
     * @return mixed
     */
    public static function baseCompanies(): mixed
    {
        return self::where('type', CompanyType::Base->value);
    }

    /**
     * Return all crm companies.
     *
     * @return mixed
     */
    public static function crmCompanies(?int $type = null)
    {
        if ($type) {
            return self::whereIn('type', [$type, CompanyType::General->value]);
        }

        return self::whereNot('type', CompanyType::Base->value);
    }

    /**
     * Return addresses related to the company.
     *
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    /**
     * Return logistics addresses related to the company.
     *
     * @return HasMany
     */
    public function logisticsAddresses(): HasMany
    {
        return $this->addresses()?->where('type', CompanyAddressType::Logistics->value);
    }

    /**
     * Return workflow processes related to the company.
     *
     * @return HasMany
     */
    public function workflowProcesses(): HasMany
    {
        return $this->hasMany(WorkflowProcess::class);
    }

    /**
     * Return configurations related to the company.
     *
     * @return MorphMany
     */
    public function configurations(): MorphMany
    {
        return $this->morphMany(Configuration::class, 'configurable');
    }
}
