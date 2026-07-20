export default defineNuxtConfig({
  compatibilityDate: "2026-01-01",
  ssr: true,
  css: ["~/assets/css/main.css"],
  modules: ["@nuxtjs/i18n"],

  app: {
    head: {
      link: [
        { rel: "icon", href: "/favicon.ico", sizes: "any" },
        { rel: "icon", type: "image/png", sizes: "32x32", href: "/favicon-32x32.png" },
        { rel: "icon", type: "image/png", sizes: "16x16", href: "/favicon-16x16.png" },
      ],
    },
  },

  nitro: {
    prerender: {
      crawlLinks: true,
      routes: ["/", "/en"],
    },
  },

  i18n: {
    bundle: {
      optimizeTranslationDirective: false,
    },
    locales: [
      { code: "de", iso: "de-DE", name: "Deutsch", file: "de.json" },
      { code: "en", iso: "en-GB", name: "English", file: "en.json" },
    ],
    defaultLocale: "de",
    strategy: "prefix_except_default",
    langDir: "locales/",
    lazy: true,
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: "i18n_redirected",
      redirectOn: "root",
      alwaysRedirect: false,
    },
  },
});
