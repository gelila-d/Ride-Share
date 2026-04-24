<?php

namespace App\Events;

use App\Models\User;
use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class TripAccepted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $trip;
    private $user;

    public function __construct(Trip $trip, User $user)
    {
        $this->trip = $trip;
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('passenger.' . $this->user->id)
        ];
    }
}