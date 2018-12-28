<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Room;
use App\Message;
use App\Events\RoomCreate;

class RoomsController extends Controller
{
	/** Получаем сообщения для текущей комнаты */
	public function fetchRoomMessages(Request $request)
    {
    	try {
    		$this->checkAccess($request->id);
    		return Message::where('room_id', $request->id)->with('user')->orderBy('created_at', 'desc')->get();
    	} catch (\Exception $e) {
    		return ['status' => $e->getMessage()]; 
    	}
    }

    public function createRoom(Request $request) {
    	
    	try {
    		$this->checkDoubleRoom($request->get('user'));
    		$this->checkSelfCreate($request->get('user'));

    		$room = Room::create([
    		'user_first' => Auth::id(),
    		'user_second' => $request->get('user'),
	    	]);

	    	$addedUser = User::find($request->get('user'));

	    	broadcast(new RoomCreate(Auth()->user(), $addedUser, $room))->toOthers();

	    	// добавляем имя приглашаемого пользователя в комнату к ответу
	    	// чтобы у пользователя который создал комнату вывести ее название
	    	$room->name = User::find($request->get('user'))->name;

	    	return $room;	
    	} catch (\Exception $e) {
    		return ['error' => $e->getMessage()];
    	}	
    }

    /** Выводим список комнат пользователя */ 
    public function getRooms() {
    	// получаем ид пользователя
    	$userId = Auth::id();

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
