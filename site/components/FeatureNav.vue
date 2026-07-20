<script setup>
const { tm, rt } = useI18n();
const localePath = useLocalePath();
const route = useRoute();

const slugs = ["modules", "lists", "records", "relationships", "dashboards"];

const items = computed(() => {
  const areas = tm("features.areas");
  return slugs.map((slug, i) => ({ slug, label: rt(areas[i]?.navLabel) }));
});

const isActive = (slug) => route.path.endsWith(`/features/${slug}`);
</script>

<template>
  <nav class="feature-nav">
    <NuxtLink
      v-for="item in items"
      :key="item.slug"
      :to="localePath(`/features/${item.slug}`)"
      class="feature-nav__pill"
      :class="{ 'feature-nav__pill--active': isActive(item.slug) }"
    >
      {{ item.label }}
    </NuxtLink>
  </nav>
</template>

<style scoped>
.feature-nav {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  margin-bottom: var(--space-12);
}

.feature-nav__pill {
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-body);
  border: 1px solid var(--color-border);
  border-radius: 999px;
  padding: 0.5rem 1rem;
}

.feature-nav__pill:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.feature-nav__pill--active {
  background: var(--color-ink);
  border-color: var(--color-ink);
  color: var(--color-on-dark);
}
</style>
