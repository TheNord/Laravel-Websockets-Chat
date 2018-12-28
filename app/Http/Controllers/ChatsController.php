<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\PrivateMessage;
use App\Message;
use App\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('chat');
    }

    public function sendMessage(Request $request)
    {
        $roomId = $request->get('room');

        $message = auth()->user()->messages()->create([
            'message' => $request->get('message')['message'],
            'room_id' => $roomId
        ]);

        // определяем приватная комната или общая, если приватная отправляем нужному пользователю
        // если общая то отправляем всем
        if ($roomId !== 0) {
            $room = Room::findOrFail($roomId);
            broadcast(new PrivateMessage(auth()->user(), $message, $room))->toOthers();
        } else {
            broadcast(new MessageSent(auth()->user(), $message))->toOthers();
        }
        
        $message = Message::with('user')->find($message->id);

        return $message;
    }
}