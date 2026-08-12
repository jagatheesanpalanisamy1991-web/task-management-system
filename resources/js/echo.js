import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',

    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: 'localhost',
    wsPort: 8081,

    forceTLS: false,
    enabledTransports: ['ws'],

    authEndpoint: 'http://localhost:8080/broadcasting/auth',

    auth: {
        headers: {
            Authorization:
                'Bearer ' + localStorage.getItem('auth_token'),

            Accept: 'application/json'
        }
    }
});

console.log('Laravel Echo initialized:', window.Echo);