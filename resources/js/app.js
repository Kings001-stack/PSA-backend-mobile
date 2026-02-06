import "../css/app.css";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

import { ZiggyVue } from "ziggy-js";
import { Ziggy } from "./ziggy";
import { route } from "ziggy-js";

// Make Ziggy available globally for the 'route' helper
window.Ziggy = Ziggy;

createInertiaApp({
    title: (title) => `${title} - Pharmacy Chatbot`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mixin({ methods: { route: (name, params, absolute, config = Ziggy) => route(name, params, absolute, config) } })
            .mount(el);
    },
    progress: {
        color: "#667eea",
    },
});
