<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\QuoteInvitationStatus;
use App\Models\QuoteInvitation;
use App\Models\User;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class ConceptQuoteInvitations extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::QuoteInvitationsDataTable;
    }

    public function getBuilder(): Builder
    {
        $authUser = Auth::user();

        return QuoteInvitation::forRole($authUser->roles[0]->name)
            ->where('status', QuoteInvitationStatus::Concept);
    }
}
