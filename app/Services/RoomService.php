<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\RoomCreate;
use App\Services\RoomService;
use App\User;
use App\Room;
use App\Message;

class RoomService
{
	public function fetchMessage($room) {
		$this->checkAccess($room);
		return Message::where('room_id', $room)->with('user')->orderBy('created_at', 'desc')->get();
	}

	public function createRoom(Request $request) {
		// проверяем на создание комнаты с самим собой
		// и создание больше чем одной комнаты
		$this->checkDoubleRoom($request->get('user'));
    	$this->checkSelfCreate($request->get('user'));

		$room = Room::create([
    		'user_first' => Auth::id(),
    		'user_second' => $request->get('user'),
    	]);

    	$addedUser = User::find($request->get('user'));

    	// отправляем уведомление пользователю с которым была создана комната
    	broadcast(new RoomCreate(Auth()->user(), $addedUser, $room))->toOthers();

    	// добавляем имя приглашаемого пользователя в комнату к ответу
    	// чтобы у пользователя который создал комнату вывести ее название
    	$room->name = User::find($request->get('user'))->name;

    	return $room;
	}

	public function getUserRoom($userId) {
    	// находим комнату
    	$rooms = Room::where('user_first', '=', $userId)
         	 ->orWhere('user_second', '=', $userId)
         	 ->get();

        // проходим циклом по комнатам
        // определяем имя комнаты
        // именем комнаты будет второй участник диалога (не текущий пользователь) 	 
        foreach ($rooms as $room) {
        	if ($room->user_first !== $userId) {
        		$name = User::find($room->user_first)->name;
        		$room->name = $name;
        	} elseif ($room->user_second !== $userId) {
        		$name = User::find($room->user_second)->name;
        		$room->name = $name;
        	
        	}
        } 	 

        return $rooms; 	 
	}

	// Определяем необхоимые проверки

	/** Проверка доступа пользователя к комнате */
    public function checkAccess($room) {
    	if ($room === '0') {
    		return true;
    	}

    	$room = Room::find($room);
    	$userId = Auth::id();

    	if($room->user_first === $userId) {
    		return true;
    	}

    	if($room->user_second === $userId) {
    		return true;
    	}

    	throw new \Exception('You do not have access to this room');
    }

    public function checkDoubleRoom($user) {
    	$firstTry = Room::where('user_first', Auth::id())
    	->where('user_second', $user)
    	->count(); 

    	$secondTry = Room::where('user_second', Auth::id())
    	->where('user_first', $user)
    	->count();

    	if ($firstTry > 0 || $secondTry > 0) {
    		throw new \Exception("Cannot create more than one room with this user.");
    	}
    }

    public function checkSelfCreate($user) {
    	if (Auth()->id() == $user) {
    		throw new \Exception("You can't create a room with yourself.");
    	}
    }
}