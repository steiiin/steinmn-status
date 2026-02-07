import './bootstrap';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { createVuetify } from 'vuetify';

const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

createInertiaApp({
    resolve: (name) => pages[`./Pages/${name}.vue`],
    setup({ el, App, props, plugin }) {
        const vuetify = createVuetify();

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .mount(el);
    },
});
