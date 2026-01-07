import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { useTrans } from "@/Composables/useTrans";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import "../scss/app.scss";
import "../scss/lib.min.css";

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });

    app.use(plugin);
    app.config.globalProperties.$t = (key, fallback = "") => {
      const { t } = useTrans();
      return t(key, fallback);
    };

    app.mount(el);
  },
});
