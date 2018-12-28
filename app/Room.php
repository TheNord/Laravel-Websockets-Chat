<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Room extends Model
{
	protected $fillable = ['user_first', 'user_second'];
}
