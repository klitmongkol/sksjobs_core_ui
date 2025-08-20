import { createApp } from 'vue';
import '../sass/app.scss';
import LineLoginButton from './components/LineLoginButton.vue';

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp({});
    app.component('line-login-button', LineLoginButton);
    app.mount('#app');
});