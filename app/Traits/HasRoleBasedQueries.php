<?php

declare(strict_types=1);

namespace App\Traits;

trait HasRoleBasedQueries
{
    /**
     * Add to query ::forRole('Management') for example.
     *
     * This method dynamically calls a role-specific scope method based on the
     * provided role. If the corresponding scope method does not exist, it aborts
     * with a 401 status code.
     *
     * @param        $query
     * @param        $role
     * @return mixed
     */
    public function scopeForRole($query, $role): mixed
    {
        $scopeMethodName = 'scopeFor'.str_replace(' ', '', $role);
        if (! method_exists($this, $scopeMethodName)) {
            abort(401);
        }

        return $this->{$scopeMethodName}($query);
    }

    /**
     * Scope a query to include results for Super Manager role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForSuperManager($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Management role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForManagement($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Company Purchaser role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForCompanyPurchaser($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Manager SalesPurchasing role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForManagerSalesPurchasing($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Back Office Employee role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForBackOfficeEmployee($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Back Office Manager role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForBackOfficeManager($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Logistics Employee role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForLogisticsEmployee($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for Finance Employee role.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForFinanceEmployee($query): mixed
    {
        return $query->whereHas('creator', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    /**
     * Scope a query to include results for customer.
     *
     * @param        $query
     * @return mixed
     */
    public function scopeForCustomer($query): mixed
    {
        return $query->where('customer_id', auth()->id());
    }
}
