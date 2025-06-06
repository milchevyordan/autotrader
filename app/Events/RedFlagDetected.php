<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RedFlagDetected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Model $model;

    public string $message;

    public string $route;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param mixed $message
     * @param mixed $route
     */
    public function __construct(Model $model, string $message, string $route)
    {
        $this->model = $model;
        $this->message = $message;
        $this->route = $route;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
