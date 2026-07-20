<script setup>
const localePath = useLocalePath();
const { t } = useI18n();
</script>

<template>
  <header class="site-header">
    <div class="container site-header__inner">
      <NuxtLink :to="localePath('/')" class="site-header__brand">
        <img src="/logo.svg" :alt="t('brand.name')" height="28" />
      </NuxtLink>

      <nav class="site-header__nav">
        <NuxtLink :to="localePath('/features')">{{ t('nav.features') }}</NuxtLink>
        <NuxtLink :to="localePath('/pricing')">{{ t('nav.pricing') }}</NuxtLink>
        <a href="https://docs.cubrel.com">{{ t('nav.docs') }}<ExternalLinkIcon /></a>
      </nav>

      <div class="site-header__actions">
        <LanguageSwitch />
        <a href="https://app.cubrel.com" class="btn btn--dark btn--sm">{{ t('nav.start') }}<ExternalLinkIcon /></a>
      </div>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--color-border);
}

.site-header__inner {
  display: flex;
  align-items: center;
  gap: var(--space-8);
  padding-top: var(--space-4);
  padding-bottom: var(--space-4);
}

.site-header__brand {
  display: flex;
  align-items: center;
}

.site-header__nav {
  display: flex;
  gap: var(--space-6);
  margin-left: var(--space-2);
}

.site-header__nav a {
  text-decoration: none;
  color: var(--color-body);
  font-weight: 500;
  font-size: 0.95rem;
}

.site-header__nav a:hover {
  color: var(--color-primary);
}

.site-header__actions {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  margin-left: auto;
}

.btn--sm {
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}
</style>
