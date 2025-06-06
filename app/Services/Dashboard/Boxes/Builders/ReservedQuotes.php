<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Models\Quote;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class ReservedQuotes extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::QuotesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Quote::inThisCompany()
            ->whereNotNull('reservation_customer_id')
            ->whereNotNull('reservation_until')
            ->where('reservation_until', '>', now());
    }
}
