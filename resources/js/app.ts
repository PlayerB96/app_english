import "../css/app.css";

import { createApp, h, type DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

const appName = import.meta.env.VITE_APP_NAME || "Dev English";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: "#2563eb",
    },
});
