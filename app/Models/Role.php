<?php

namespace App\Models;

use App\Traits\HasChangeLogs;

class Role extends \Spatie\Permission\Models\Role
{
    use HasChangeLogs;
}
