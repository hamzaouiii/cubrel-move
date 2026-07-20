import DefaultTheme from "vitepress/theme";
import type { Theme } from "vitepress";
import TranslationNotice from "./components/TranslationNotice.vue";
import "./custom.css";

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component("TranslationNotice", TranslationNotice);
  },
} satisfies Theme;
