<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders\Enums;

use App\Traits\Enum;

enum DataTableMethod: string
{
    use Enum;

    case VehiclesDataTable = 'vehiclesDataTable';
    case ServiceVehiclesDataTable = 'serviceVehiclesDataTable';
    case PurchaseOrdersDataTable = 'purchaseOrdersDataTable';
    case SalesOrdersDataTable = 'salesOrdersDataTable';
    case ServiceOrdersDataTable = 'serviceOrdersDataTable';
    case QuoteInvitationsDataTable = 'quoteInvitationsDataTable';
    case QuotesDataTable = 'quotesDataTable';
}
