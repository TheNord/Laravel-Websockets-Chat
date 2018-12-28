
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Toasted from 'vue-toasted';

import Event from './event.js';

Vue.use(Toasted)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',

    data: {
        messages: [],
        users: [],
        rooms: [],
        createdDate: [],
        room: 0,
    },

    created() {
        this.fetchMessagesRoom(0);
        this.fetchRooms();

        // отслеживаем событие печати пользователя
        // отображаем только тогда когда комната в которой печатает пользователь - общая
        Event.$on('typing', (event) => {
            if(this.room == 0) {
                let channel = Echo.join('chat')
                setTimeout( () => {
                    channel.whisper('typing', event.user)
                }, 300)
            }
        });
        
        // слушаем приватный чат канал
        window.Echo.private(`private-chat-${Laravel.user.id}`)
            // создание комнаты, уведомляем пользователя
            .listen('RoomCreate', (event) => {
                this.rooms.push(event.room);
                this.$toasted.show("User " + event.room.name + " create room with you, please answer", { 
                    theme: "toasted-primary", 
                    position: "top-center", 
                    duration : 5000
                });
            })
            // появление нового сообщения в приватной комнате
            // нужно сделать чтоб появялялось окошко о непрочитанных сообщениях
            .listen('PrivateMessage', (event) => {
                // добавляем сообщение пользователю если он сейчас в нужной приватной комнате
                if(this.room == event.message.room_id)
                {
                    this.messages.unshift({
                        message: event.message.message,
                        user: event.user,
                        createdDate: event.message.createdDate,
                    });
                } else {
                    // отправляем уведомление о приватном сообщении 
                    // если пользователь сейчас не находится в комнате с этим пользователем
                    this.$toasted.show("Private message from user: " + event.user.name, { 
                        theme: "toasted-primary", 
                        position: "top-center", 
                        duration : 5000
                    });
                }
                // удаляем статус "Печатает"
                this.users.forEach((user, index) => {
                    if (user.id === event.user.id) {
                        user.typing = false;
                        this.$set(this.users, index, user);
                    }
                });
             });

        Echo.join('chat')
            // слушаем события на вход и выход пользователей
            .here(users => {
                this.users = users;
            })
            .joining(user => {
                this.users.push(user);
            })
            .leaving(user => {
                this.users = this.users.filter(u => u.id !== user.id);
            })
            // прослушиваем на событие "пользователь печатает"
            .listenForWhisper('typing', ({id, name}) => {
                this.users.forEach((user, index) => {
                    if (user.id === id) {
                        user.typing = true;
                        this.$set(this.users, index, user);

                        setTimeout( () => {
                            user.typing = false;
                            this.$set(this.users, index, user);
                        }, 5000)
                    }
                });
            })
            // слушаем событие MessageSent и при появлении отсылаем всем пользователям
            .listen('MessageSent', (event) => {
                console.log('recesive public message')
                // добавляем сообщение пользователям если они сейчас в основной комнате
                if(this.room == 0)
                {
                    this.messages.unshift({
                        message: event.message.message,
                        user: event.user,
                        createdDate: event.message.createdDate
                    });
                }
                // удаляем статус "Печатает" после отправки
                this.users.forEach((user, index) => {
                    if (user.id === event.user.id) {
                        user.typing = false;
                        this.$set(this.users, index, user);
                    }
                });
            });
    },


    methods: {
        // метод будет вызван при появлении события messagesent
        addMessage(message) {
            // отправляем сообщение на сервер
            axios.post('/messages', {message: message, room: this.room}).then(response => {
                // добавляем сообщение отправившему пользователю
                this.messages.unshift(response.data);
            });
        },

         // получаем все комнаты пользователя
        fetchRooms() {
            axios.get('/rooms').then(response => {
                this.rooms = response.data;
            });
        },

        // переход в другую комнату
        changeRoom(room) {
            this.fetchMessagesRoom(room);
        },

        // получаем все сообщения для комнаты
        fetchMessagesRoom(room) {
            this.room = room;

            axios.get('/room/' + room).then(response => {
                this.messages = [];
                this.messages = response.data;
            });
        },

        // создание новой комнаты
        addRoom(user) {
            axios.post('/room/create', {user})
            .then(response => {
                if(response.data.error){
                    this.$toasted.show(response.data.error, { 
                        theme: "toasted-primary", 
                        position: "top-center", 
                        duration : 5000
                    });
                    return;
                }
                this.rooms.push(response.data);
            })
            .catch(function (error) {
                console.log(error);
            });
        }

        
    }
});
