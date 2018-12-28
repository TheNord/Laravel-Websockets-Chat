<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Events\PrivateMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\RoomCreate;
use App\Services\RoomService;
use App\User;
use App\Room;
use App\Message;

class ChatService
{
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

		// добавляем пользователя к сообщению
		$message = Message::with('user')->find($message->id);

		return $message;
	}
}