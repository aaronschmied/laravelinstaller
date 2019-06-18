
import Vue from 'vue'
import Axios from 'axios'
import UIkit from 'uikit'
import UIkitIcons from 'uikit/dist/js/uikit-icons'
import Turbolinks from 'turbolinks'
import TurbolinksAdapter from 'vue-turbolinks'
import SocketIOClient from 'socket.io-client'
import Echo from 'laravel-echo'
import moment from 'moment'
import VueMoment from 'vue-moment'
import('moment/locale/de-ch')


Vue.use(TurbolinksAdapter)
Vue.use(VueMoment, {moment})
UIkit.use(UIkitIcons)

/**
 * Register axios globally and set the csrf token.
 *
 * @type {AxiosStatic}
 */
window.axios = Axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Start the turbolinks package.
 */
Turbolinks.start()

/**
 * Register the socketio client.
 *
 * @type {lookup}
 */
window.io = SocketIOClient

/**
 * Instantiate the echo client.
 *
 * @type {Echo}
 */
window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname
});

/**
 * Add the event listener to register the vue app on a turbolinks pageload.
 */
document.addEventListener('turbolinks:load', () => {
    let vueapp = new Vue({
        components: {
            'example-component': () => {
                return import('@/ExampleComponent');
            },
        },
        el: '#app'
    });
});
