import { defineConfig } from "vitepress";

export default defineConfig({
  title: "Cubrel Docs",
  cleanUrls: true,
  sitemap: {
    hostname: "https://docs.cubrel.com",
  },
  head: [
    ["link", { rel: "icon", href: "/favicon.ico", sizes: "any" }],
    [
      "link",
      {
        rel: "icon",
        type: "image/png",
        sizes: "32x32",
        href: "/favicon-32x32.png",
      },
    ],
    [
      "link",
      {
        rel: "icon",
        type: "image/png",
        sizes: "16x16",
        href: "/favicon-16x16.png",
      },
    ],
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
          { text: "Cubrel.com", link: "https://www.cubrel.com" },
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
              { text: "Felder", link: "/fields-guide" },
              { text: "Layouts", link: "/layouts-guide" },
              { text: "Beziehungen", link: "/relationships-guide" },
              { text: "Datenimport", link: "/import-guide" },
              { text: "Umwandlungsregeln", link: "/conversion-guide" },
            ],
          },
          {
            text: "Alltägliche Nutzung",
            items: [
              { text: "Suche", link: "/search-guide" },
              { text: "Listenfilter", link: "/list-filters-guide" },
              { text: "Dashboard", link: "/dashboard-guide" },
              { text: "Aktivitäten", link: "/activities-guide" },
              { text: "Sammelaktionen & Export", link: "/bulk-export-guide" },
              { text: "E-Mails", link: "/emails-guide" },
              { text: "Ihre Präferenzen", link: "/preferences-guide" },
            ],
          },
          {
            text: "Verwaltung & Sicherheit",
            items: [
              { text: "Benutzer, Einladungen & Passwörter", link: "/users-guide" },
              { text: "Ersteinrichtung", link: "/onboarding-guide" },
              { text: "PDF-Vorlagen", link: "/pdf-templates-guide" },
              { text: "Dropdown-Listen", link: "/dropdown-manager-guide" },
              { text: "Impersonation", link: "/impersonation-guide" },
              { text: "Audit-Trail", link: "/audit-trail-guide" },
              { text: "Benachrichtigungen", link: "/notification-guide" },
              { text: "Sitzungen", link: "/session-timeout-guide" },
              { text: "Datenaufbewahrung", link: "/data-retention-guide" },
            ],
          },
          {
            text: "Integrationen & API",
            items: [{ text: "REST-API", link: "/rest-api-guide" }],
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
          { text: "Cubrel.com", link: "https://www.cubrel.com/en" },
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
              { text: "Fields", link: "/en/fields-guide" },
              { text: "Layouts", link: "/en/layouts-guide" },
              { text: "Relationships", link: "/en/relationships-guide" },
              { text: "Importing Data", link: "/en/import-guide" },
              { text: "Converting Records", link: "/en/conversion-guide" },
            ],
          },
          {
            text: "Everyday Use",
            items: [
              { text: "Searching", link: "/en/search-guide" },
              { text: "List Filters", link: "/en/list-filters-guide" },
              { text: "Dashboard", link: "/en/dashboard-guide" },
              { text: "Activities", link: "/en/activities-guide" },
              { text: "Bulk Actions & Export", link: "/en/bulk-export-guide" },
              { text: "Emails", link: "/en/emails-guide" },
              { text: "Your Preferences", link: "/en/preferences-guide" },
            ],
          },
          {
            text: "Administration & Security",
            items: [
              { text: "Users, Invites & Passwords", link: "/en/users-guide" },
              { text: "First-Time Setup", link: "/en/onboarding-guide" },
              { text: "PDF Templates", link: "/en/pdf-templates-guide" },
              { text: "Dropdown Lists", link: "/en/dropdown-manager-guide" },
              { text: "Impersonation", link: "/en/impersonation-guide" },
              { text: "Audit Trail", link: "/en/audit-trail-guide" },
              { text: "Notifications", link: "/en/notification-guide" },
              { text: "Sessions", link: "/en/session-timeout-guide" },
              { text: "Data Retention", link: "/en/data-retention-guide" },
            ],
          },
          {
            text: "Integrations & API",
            items: [{ text: "REST API", link: "/en/rest-api-guide" }],
          },
        ],
      },
    },
  },
});
