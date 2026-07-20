# Shared design tokens

Source of truth for colors, typography, spacing, and radius shared between
`site/` (marketing, Nuxt) and `docs/guides/` (docs, VitePress). Both projects
`@import` `tokens.css` and `fonts.css` directly via relative paths — no build
step, no package.

- `--color-primary` (#3498db) matches the app's real default
  (`App\Support\Settings::get('primary_color', '#3498db')`). Everything else
  here is a new proposal for the marketing/docs surface — the app itself has
  no centralized token file to extract them from.
- `--font-sans` (Fira Sans + Heebo) matches the app's real fonts
  (`resources/scss/fonts.scss`), not the old `landing/*.dc.html` mockup's
  invented Plus Jakarta Sans / IBM Plex Mono.

## Font files

`fonts.css` expects `/fonts/*.woff2` to exist at each site's own public root.
The canonical source is `resources/fonts/*.woff2` (12 files, Fira Sans +
Heebo, webfont-helper v18/v28). Fonts change essentially never — if they
ever do, update `resources/fonts/` first, then re-copy into
`site/public/fonts/` and `docs/guides/.vitepress/public/fonts/`. This is a
manual copy, not wired through any build tool, since it's a one-time-ever
sync across three otherwise-independent projects.
