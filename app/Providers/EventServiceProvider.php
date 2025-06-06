<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AssignedToWorkOrderTask;
use App\Events\InternalRemarksUpdated;
use App\Events\RedFlagDetected;
use App\Listeners\SendNewUserNotification;
use App\Listeners\SendRedFlagNotification;
use App\Listeners\SendWorkOrderTaskNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        InternalRemarksUpdated::class => [
            SendNewUserNotification::class,
        ],
        AssignedToWorkOrderTask::class => [
            SendWorkOrderTaskNotification::class,
        ],
        RedFlagDetected::class => [
            SendRedFlagNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
