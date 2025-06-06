<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\WorkOrderTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignedToWorkOrderTask
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public WorkOrderTask $task;

    /**
     * Create a new event instance.
     *
     * @param WorkOrderTask $task
     */
    public function __construct(WorkOrderTask $task)
    {
        $this->task = $task;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
