<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Room;

class RoomCreate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $addedUser;
    public $room;
    public $creator;

    public function __construct(User $creator, User $addedUser, Room $room)
    {
        $this->addedUser = $addedUser;
        $this->creator = $creator;
        $this->room = $room;
    }

    public function broadcastWith()
    {
        return ['room' => [
            'id' => $this->room->id,
            'name' => $this->creator->name
        ]];
    }

    
    public function broadcastOn()
    {
        return new PrivateChannel('private-chat-' . $this->addedUser->id);
    }
}
