<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RoomService;

class RoomsController extends Controller
{
	private $service;

	public function __construct(RoomService $service) 
	{
		$this->service = $service;
	}

	/** Получаем сообщения для текущей комнаты */
	public function fetchRoomMessages(Request $request)
    {
    	try {
    		return $this->service->fetchMessage($request->id);
    	} catch (\Exception $e) {
    		return ['error' => $e->getMessage()]; 
    	}
    }

	/** Создание новой приватной комнаты */
    public function createRoom(Request $request) 
    {
    	try {
    		return $this->service->createRoom($request);
    	} catch (\Exception $e) {
    		return ['error' => $e->getMessage()];
    	}	
    }

    /** Выводим список комнат пользователя */ 
    public function getRooms() 
    {
    	try {
    		return $this->service->getUserRoom(Auth::id());
    	} catch (\Exception $e) {
    		return ['error' => $e->getMessage()];
    	}
    }
}
