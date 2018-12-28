<?php

use App\Room;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat', function ($user) {
	return $user;
});

// Регистрируем приватный канал для создания чатрума и других приватных событий
Broadcast::channel('private-chat-{userId}', function ($user, $userId) {
	return Auth()->id() == $userId;
});