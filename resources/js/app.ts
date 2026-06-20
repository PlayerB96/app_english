import "../css/app.css";
import "sweetalert2/dist/sweetalert2.min.css";

import { createApp, h, type DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

import { useThemeStore } from "@/Stores/useThemeStore";

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

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia);

        useThemeStore().init();
        app.mount(el);
    },
    progress: {
        color: "#2563eb",
    },
});
