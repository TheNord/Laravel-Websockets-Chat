@extends('layouts.app')

@section('content')
    <div class="container chats">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="card card-default">
                    <div class="card-header">Chats</div>
                    
                    <div class="card-body">
                        <chat-messages :messages="messages"></chat-messages>
                    </div>
                    <div class="card-footer">
                        <chat-form
                                @messagesent="addMessage"
                                :user="{{ auth()->user() }}"
                        ></chat-form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-default card-header">Active users</div>
                <ul class="list-group">
                    <li class="list-group-item" v-for="user in users">
                        <button class="btn-chat" v-on:click.prevent="addRoom(user.id)">@{{ user.name }}</button> <span v-if="user.typing" class="badge badge-primary">typing...</span>
                    </li>
                </ul>
                <div class="card card-default card-header">Channel rooms</div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <button class="btn-chat" v-on:click.prevent="changeRoom(0)">Main channel</button>
                    </li>
                    <li class="list-group-item" v-for="room in rooms">
                        <button class="btn-chat" v-on:click.prevent="changeRoom(room.id)"> Chat with user <b>@{{ room.name }}</b></button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection