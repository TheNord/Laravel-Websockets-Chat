<template>
    <div class="input-group">
        <input
                id="btn-input"
                type="text"
                name="message"
                class="form-control input-sm"
                placeholder="Type your message here..."
                ref="mess"
                v-model="newMessage"
                @keyup.enter="sendMessage"
                @keyup="sendTypingEvent">

        <span class="input-group-btn">
            <button class="btn btn-primary btn-sm" id="btn-chat" @click="sendMessage">
                Send
            </button>
        </span>
    </div>
</template>

<script>
// подключаем Event для трансляции событий между модулями
import Event from '../event.js';

export default {
    // получаем данные о пользователе из chat.blade
    props: ['user'],

    data() {
        return {
            newMessage: ''
        }
    },

    mounted() {
        // вписываем в форму ввода сообщения ник пользователя, к кому обращаемся
        Event.$on('addName', (name) => {
            this.newMessage = '@' + name + ': ';
            this.$refs.mess.focus();
        });
    },   

    methods: {
        // отправляем событие о том что пользователь набирает сообщение
        sendTypingEvent() {
            Event.$emit('typing', {
                user: this.user
            });
        },  

        // отправляем сообщение
        sendMessage() {
            this.$emit('messagesent', {
                user: this.user,
                message: this.newMessage
            });

            this.newMessage = ''
        }
    }
}
</script>