import { createApp } from 'vue';
import LineLoginButton from './components/LineLoginButton.vue';

const app = createApp({});
app.component('line-login-button', LineLoginButton);
app.mount('#app');