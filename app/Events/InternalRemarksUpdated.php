<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InternalRemarksUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Model $model;

    public string $note;

    public array $users;

    public array $roles;

    /**
     * Create a new event instance.
     *
     * @param mixed $model
     * @param mixed $users
     * @param mixed $note
     * @param array $roles
     */
    public function __construct(Model $model, array $users, array $roles, string $note)
    {
        $this->model = $model;
        $this->users = $users;
        $this->roles = $roles;
        $this->note = $note;
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
