<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Services\DataTable\DataTable;

interface DataTableProviderContract
{
    public function getDataTable(): DataTable;
}
