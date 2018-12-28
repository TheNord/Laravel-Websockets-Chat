<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['message', 'room_id'];

    protected $appends = ['createdDate'];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function getCreatedDateAttribute()
    {
        return $this->created_at->format('d-m-Y h:m');
    }

}
