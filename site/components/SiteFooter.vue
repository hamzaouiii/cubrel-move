<script setup>
const localePath = useLocalePath();
const { t } = useI18n();
</script>

<template>
  <footer class="site-footer">
    <div class="container site-footer__inner">
      <div class="site-footer__brand">
        <img src="/logo.svg" :alt="t('brand.name')" height="24" />
        <p>{{ t('brand.tagline') }}</p>
      </div>

      <nav class="site-footer__links">
        <NuxtLink :to="localePath('/features')">{{ t('nav.features') }}</NuxtLink>
        <NuxtLink :to="localePath('/pricing')">{{ t('nav.pricing') }}</NuxtLink>
        <a href="https://docs.cubrel.com">{{ t('nav.docs') }}<ExternalLinkIcon /></a>
        <a href="https://app.cubrel.com">{{ t('nav.app') }}<ExternalLinkIcon /></a>
        <NuxtLink :to="localePath('/impressum')">{{ t('nav.impressum') }}</NuxtLink>
        <NuxtLink :to="localePath('/datenschutz')">{{ t('nav.datenschutz') }}</NuxtLink>
      </nav>
    </div>
    <div class="container site-footer__legal">
      <p>{{ t('footer.copyright') }}</p>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  border-top: 1px solid var(--color-border);
  margin-top: var(--space-16);
}

.site-footer__inner {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-8);
  justify-content: space-between;
  padding-top: var(--space-12);
  padding-bottom: var(--space-8);
}

.site-footer__brand p {
  margin-top: var(--space-2);
  color: var(--color-muted);
  font-size: 0.9rem;
  max-width: 240px;
}

.site-footer__links {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-6);
  align-items: flex-start;
}

.site-footer__links a {
  text-decoration: none;
  color: var(--color-body);
  font-size: 0.9rem;
}

.site-footer__links a:hover {
  color: var(--color-primary);
}

.site-footer__legal {
  border-top: 1px solid var(--color-border);
  padding-top: var(--space-4);
  padding-bottom: var(--space-8);
}

.site-footer__legal p {
  color: var(--color-muted);
  font-size: 0.85rem;
}
</style>
