import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import 'windi.css';
import './main.css';
import 'material-icons/iconfont/material-icons.css';

const app = createApp(App);

app.use(router);

app.mount('#app');
