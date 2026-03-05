import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';

const appElement = document.getElementById('app');

if (appElement) {
    createApp(App).mount(appElement);
}
