<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasSupplier
{
    /**
     * Return resource's supplier.
     *
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Return resource's supplier company.
     *
     * @return BelongsTo
     */
    public function supplierCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'supplier_company_id');
    }
}
