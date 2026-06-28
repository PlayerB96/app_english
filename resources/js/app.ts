import "../css/app.css";
import "sweetalert2/dist/sweetalert2.min.css";

import { createApp, h, type DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

import AppLayout from "@/Layouts/AppLayout.vue";
import { useThemeStore } from "@/Stores/useThemeStore";

const appName = import.meta.env.VITE_APP_NAME || "Dev English";

const pagesWithoutLayout = new Set([
    "Welcome",
    "Auth/Login",
    "Auth/Register",
    "Auth/VerifyEmail",
]);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        );

        if (!pagesWithoutLayout.has(name) && !page.default.layout) {
            page.default.layout = AppLayout;
        }

        return page;
    },
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
