<template>
    <ul class="chat">
        <li class="left clearfix" v-for="message in messages">
            <div class="chat-body clearfix">
                <div class="header">
                    <strong class="primary-font">
                        <a href="#" v-on:click.prevent="addName(message.user.name)">
                            {{ message.user.name }}
                        </a>
                    </strong>
                        | {{ message.createdDate }}
                </div>
                <p>
                    {{ message.message }}
                </p>
            </div>
        </li>
    </ul>
</template>

<script>
// подключаем Event для трансляции событий между модулями
import Event from '../event.js';

export default {
    props: ['messages'],  

    methods: {
        // обращаемся к пользователю по нику
        addName(name) {
           Event.$emit('addName', name) 
        },
    }
}
</script>