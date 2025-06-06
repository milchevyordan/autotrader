<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasIntermediary
{
    /**
     * Return resource's supplier company.
     *
     * @return BelongsTo
     */
    public function intermediaryCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'intermediary_company_id');
    }

    /**
     * Return resource's intermediary.
     *
     * @return BelongsTo
     */
    public function intermediary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intermediary_id');
    }
}
