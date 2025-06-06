<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCustomer
{
    /**
     * Return resource's customer company.
     *
     * @return BelongsTo
     */
    public function customerCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'customer_company_id');
    }

    /**
     * Return resource's customer.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
