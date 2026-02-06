import './bootstrap';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css'
import { createApp } from 'vue';
import { createVuetify } from 'vuetify';
import MainPage from './views/MainPage.vue';

const app = createApp(MainPage);
const vuetify = createVuetify();

app.use(vuetify);
app.mount('#app');
