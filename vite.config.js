// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url' 

export default defineConfig({
  plugins: [
    laravel({ input: 'resources/js/app.js', refresh: true }),
    vue({
      template: { transformAssetUrls: { base: null, includeAbsolute: false } },
    }),
      vuetify({ autoImport: true }), 
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@img': fileURLToPath(new URL('./resources/img', import.meta.url)), 
    },
  },
})
