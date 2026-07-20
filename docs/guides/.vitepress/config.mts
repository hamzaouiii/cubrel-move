import { defineConfig } from "vitepress";

export default defineConfig({
  title: "Cubrel Docs",
  cleanUrls: true,
  sitemap: {
    hostname: "https://docs.cubrel.com",
  },
  head: [
    ["link", { rel: "icon", href: "/favicon.ico", sizes: "any" }],
    ["link", { rel: "icon", type: "image/png", sizes: "32x32", href: "/favicon-32x32.png" }],
    ["link", { rel: "icon", type: "image/png", sizes: "16x16", href: "/favicon-16x16.png" }],
  ],

  // German content lives in de/, served unprefixed at '/'
  rewrites: {
    "de/:slug*": ":slug*",
  },

  themeConfig: {
    logo: "/logo.svg",
    siteTitle: false,
    outline: { level: [2, 3] },
  },

  locales: {
    root: {
      label: "Deutsch",
      lang: "de-DE",
      link: "/",
      themeConfig: {
        nav: [
          { text: "Leitfaden", link: "/overview" },
          { text: "App öffnen", link: "https://app.cubrel.com" },
        ],
        sidebar: [
          {
            text: "Erste Schritte",
            items: [
              { text: "Überblick", link: "/overview" },
              { text: "Begriffe", link: "/terminology" },
            ],
          },
          {
            text: "Module & Daten",
            items: [
              { text: "Module", link: "/modules" },
              { text: "Beziehungen", link: "/relationships-guide" },
              { text: "Datenimport", link: "/import-guide" },
              { text: "Module vs. BaseModule", link: "/module-basemodule-guide" },
            ],
          },
          {
            text: "Verwaltung & Sicherheit",
            items: [
              { text: "Audit-Trail", link: "/audit-trail-guide" },
              { text: "Sitzungen", link: "/session-timeout-guide" },
            ],
          },
        ],
        outlineTitle: "Auf dieser Seite",
        docFooter: { prev: "Vorherige Seite", next: "Nächste Seite" },
        returnToTopLabel: "Nach oben",
        darkModeSwitchLabel: "Design",
        lastUpdatedText: "Zuletzt aktualisiert",
      },
    },
    en: {
      label: "English",
      lang: "en-US",
      link: "/en/",
      themeConfig: {
        nav: [
          { text: "Guide", link: "/en/overview" },
          { text: "Open App", link: "https://app.cubrel.com" },
        ],
        sidebar: [
          {
            text: "Getting Started",
            items: [
              { text: "Overview", link: "/en/overview" },
              { text: "Terminology", link: "/en/terminology" },
            ],
          },
          {
            text: "Modules & Data",
            items: [
              { text: "Modules", link: "/en/modules" },
              { text: "Relationships", link: "/en/relationships-guide" },
              { text: "Importing Data", link: "/en/import-guide" },
              { text: "Module vs. BaseModule", link: "/en/module-basemodule-guide" },
            ],
          },
          {
            text: "Administration & Security",
            items: [
              { text: "Audit Trail", link: "/en/audit-trail-guide" },
              { text: "Sessions", link: "/en/session-timeout-guide" },
            ],
          },
        ],
      },
    },
  },
});
