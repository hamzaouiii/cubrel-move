// vite.config.js
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  server: {
    port: 9873,
  },
  plugins: [
    laravel({
      input: "resources/js/app.js",
      refresh: [
        "resources/views/**",
        "resources/js/**",
        "routes/**",
        "app/Http/**",
        "!app/Handlers/Modules/Custom/**",
        "!app/Models/Modules/Custom/**",
        "!lang/en/custom/**",
        "!lang/de/custom/**",
      ],
    }),

    vue({
      template: {
        transformAssetUrls: { base: null, includeAbsolute: false },
      },
    }),
  ],

  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
      "@img": fileURLToPath(new URL("./resources/img", import.meta.url)),
    },
  },
});
