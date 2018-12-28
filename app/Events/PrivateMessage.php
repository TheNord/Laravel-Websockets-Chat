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
use App\Message;

class PrivateMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $room;
    public $receiverId;

    public function __construct(User $user, Message $message, Room $room)
    {
        $this->user = $user;
        $this->message = $message;
        //$this->room = $room;
        $this->findReceiver($room);
    }

    public function findReceiver($room) {
        // находим в каком поле (user_first или user_second) у нас получатель сообщения
        if ($room->user_first === $this->user->id) {
            $this->receiverId = $room->user_second;
        } else {
            $this->receiverId = $room->user_first;
        }
    }
    
    public function broadcastOn()
    {
        return new PrivateChannel('private-chat-' . $this->receiverId);
    }
}
