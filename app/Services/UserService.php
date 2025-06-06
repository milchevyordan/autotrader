<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompanyType;
use App\Models\Company;

use App\Models\User;
use App\Services\DataTable\DataTable;
use App\Services\Vehicles\PreOrderVehicleService;
use App\Services\Vehicles\SystemVehicleService;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class UserService extends Service
{
    /**
     * The user model.
     *
     * @var User
     */
    public User $user;

    /**
     * Create a new UserService instance.
     */
    public function __construct()
    {
        $this->setUser(new User());
    }

    /**
     * Get the model of the user.
     *
     * @return User
     */
    private function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set the model of the user.
     *
     * @param  User $user
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get multiselect with users that are suppliers.
     *
     * @param  ?int       $companyId
     * @return Collection
     */
    public static function getSuppliers(?int $companyId = null): Collection
    {
        $selectedSupplierCompanyId = request(null)->input(
            'supplier_company_id',
            $companyId
        );

        return (new MultiSelectService(
            User::when(
                $selectedSupplierCompanyId,
                fn ($query) => $query->role('Supplier')->where('company_id', $selectedSupplierCompanyId),
                fn ($query) => $query->whereNull('id')
            )
        ))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Get multiselect with users that are intermediaries.
     *
     * @param  ?int       $companyId
     * @return Collection
     */
    public static function getIntermediaries(?int $companyId = null): Collection
    {
        $selectedIntermediaryCompanyId = request(null)->input(
            'intermediary_company_id',
            $companyId
        );

        return (new MultiSelectService(
            User::when(
                $selectedIntermediaryCompanyId,
                fn ($query) => $query->role('Intermediary')->where('company_id', $selectedIntermediaryCompanyId),
                fn ($query) => $query->whereNull('id')
            )
        ))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Get multiselect with users that are purchasers.
     *
     * @param  ?int       $companyId
     * @return Collection
     */
    public static function getPurchasers(?int $companyId = null): Collection
    {
        $selectedPurchaserCompanyId = request(null)->input(
            'purchaser_company_id',
            $companyId
        );

        return (new MultiSelectService(
            User::when(
                $selectedPurchaserCompanyId,
                fn ($query) => $query->role('Purchaser')->where('company_id', $selectedPurchaserCompanyId),
                fn ($query) => $query->whereNull('id')
            )
        ))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Get multiselect with users that are company purchasers.
     *
     * @return Collection
     */
    public static function getCompanyPurchasers(): Collection
    {
        return (new MultiSelectService(
            User::where('company_id', auth()->user()->company_id)->role('Company Purchaser')
        ))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Get multiselect with users that are customers.
     *
     * @param  ?int       $companyId
     * @return Collection
     */
    public static function getCustomers(?int $companyId = null): Collection
    {
        $selectedCustomerCompanyId = request(null)->input(
            'customer_company_id',
            $companyId
        );

        return (new MultiSelectService(
            User::when(
                $selectedCustomerCompanyId,
                fn ($query) => $query->role('Customer')->where('company_id', $selectedCustomerCompanyId),
                fn ($query) => $query->whereNull('id')
            )
        ))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Get multiselect with users that are customers.
     *
     * @return Collection
     */
    public static function getAllCustomers(): Collection
    {
        return new MultiSelectService(
            User::role('Customer')
                ->whereIn('company_id', Company::crmCompanies(CompanyType::General->value)->inThisCompany()->pluck('id')))
            ->setTextColumnName('full_name')
            ->dataForSelect();
    }

    /**
     * Get the value of transportCompanies.
     *
     * @param  null|int   $companyId
     * @return Collection
     */
    public static function getTransportSuppliers(?int $companyId = null): Collection
    {
        $selectedTransportCustomerCompanyId = request()->input(
            'transport_company_id',
            $companyId
        );

        return (new MultiSelectService(
            User::when(
                $selectedTransportCustomerCompanyId,
                fn ($query) => $query->role('Transport Supplier')->where('company_id', $selectedTransportCustomerCompanyId),
                fn ($query) => $query->whereNull('id')
            )
        ))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Get multiselect with users that have crm roles.
     *
     * @return Collection
     */
    public static function getCrmRoleUsers(): Collection
    {
        return (new MultiSelectService(User::inThisCompany()->whereHas('roles', function ($rolesQuery) {
            $rolesQuery->whereIn('name', RoleService::getCrmRoles()->pluck('name')->all());
        })))->setTextColumnName('full_name')->dataForSelect();
    }

    /**
     * Creates full name used everywhere based on first, middle and last names.
     *
     * @param         $request
     * @return string
     */
    public static function getFullName($request): string
    {
        return "{$request['prefix']} {$request['first_name']} {$request['last_name']}";
    }

    /**
     * Return User created Users.
     *
     * @return DataTable
     */
    public function getUsers(): DataTable
    {
        return (new DataTable(
            $this->getUser()->users()->getQuery()->select(User::$defaultSelectFields)
        ))
            ->setRelation('company', ['id', 'name'])
            ->setRelation('roles', ['id', 'name'])
            ->setColumn('id', '#', true, true)
            ->setColumn('company.name', __('Company'), true, true)
            ->setColumn('full_name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('roles.name', __('Role'), true)
            ->setTimestamps()
            ->run();
    }
}
