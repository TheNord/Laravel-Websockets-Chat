<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatService;

class ChatsController extends Controller
{
    public function __construct(ChatService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        return view('chat');
    }

    public function sendMessage(Request $request)
    {
        try {
            return $this->service->sendMessage($request);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}