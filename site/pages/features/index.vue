<script setup>
const { t, tm, rt } = useI18n();
const localePath = useLocalePath();

const slugs = ["modules", "lists", "records", "relationships", "dashboards"];

const areas = computed(() =>
  tm("features.areas").map((area, i) => ({ ...area, slug: slugs[i] })),
);

useHead({ title: () => `${t("features.title")} — ${t("brand.name")}` });
</script>

<template>
  <div class="container page">
    <p class="eyebrow">{{ t("features.eyebrow") }}</p>
    <h1 class="page__title">{{ t("features.title") }}</h1>
    <p class="page__subtitle">{{ t("features.subtitle") }}</p>

    <div class="grid grid--3">
      <NuxtLink
        v-for="(area, i) in areas"
        :key="i"
        :to="localePath(`/features/${area.slug ?? ''}`)"
        class="card"
      >
        <p class="card__eyebrow">{{ rt(area.eyebrow) }}</p>
        <h3 class="card__title">{{ rt(area.navLabel) }}</h3>
        <p class="card__body">{{ rt(area.body) }}</p>
        <span class="card__link">{{ t("features.viewMore") }} →</span>
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-12);
  padding-bottom: var(--space-16);
}

.eyebrow {
  font-size: 0.8rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-primary);
  margin-bottom: var(--space-3);
}

.page__title {
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  max-width: 640px;
  margin-bottom: var(--space-4);
}

.page__subtitle {
  font-size: 1.1rem;
  color: var(--color-body);
  max-width: 640px;
  margin-bottom: var(--space-12);
  line-height: 1.6;
}

.grid {
  display: grid;
  gap: var(--space-6);
}

.grid--3 {
  grid-template-columns: repeat(3, 1fr);
}

.card {
  display: block;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
  text-decoration: none;
  color: inherit;
}

.card:hover {
  border-color: var(--color-primary);
}

.card__eyebrow {
  font-size: 0.7rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-muted);
  margin-bottom: var(--space-2);
}

.card__title {
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: var(--space-2);
}

.card__body {
  font-size: 0.9rem;
  color: var(--color-muted);
  line-height: 1.6;
  margin-bottom: var(--space-4);
}

.card__link {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-primary);
}
</style>
