<script setup>
const { t, tm, rt } = useI18n();
const localePath = useLocalePath();

const highlights = computed(() => tm("home.highlights"));
const steps = computed(() => tm("home.steps"));
const dashboardPoints = computed(() => tm("home.dashboardPoints"));

useHead({ title: () => `${t("brand.name")} — ${t("home.heroTitle")}` });
</script>

<template>
  <div>
    <!-- HERO -->
    <section class="container hero">
      <h1 class="hero__title">{{ t("home.heroTitle") }}</h1>
      <p class="hero__subtitle">{{ t("home.heroSubtitle") }}</p>
      <div class="hero__actions">
        <a href="https://app.cubrel.com" class="btn btn--primary"
          >{{ t("home.ctaPrimary") }}<ExternalLinkIcon
        /></a>
        <NuxtLink :to="localePath('/features')" class="btn btn--secondary">{{
          t("home.ctaSecondary")
        }}</NuxtLink>
      </div>
      <p class="hero__note">{{ t("home.note") }}</p>
    </section>

    <!-- HIGHLIGHTS -->
    <section class="container section">
      <p class="eyebrow eyebrow--center">{{ t("home.highlightsLabel") }}</p>
      <div class="grid grid--3">
        <div v-for="(h, i) in highlights" :key="i" class="card">
          <div class="card__icon">{{ rt(h.icon) }}</div>
          <h3 class="card__title">{{ rt(h.title) }}</h3>
          <p class="card__body">{{ rt(h.body) }}</p>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="section-band">
      <div class="container">
        <p class="eyebrow eyebrow--center">{{ t("home.howItWorksLabel") }}</p>
        <h2 class="section-title section-title--center">
          {{ t("home.howItWorksTitle") }}
        </h2>
        <div class="grid grid--3">
          <div v-for="(s, i) in steps" :key="i" class="card">
            <div class="card__num">{{ rt(s.num) }}</div>
            <h3 class="card__title">{{ rt(s.title) }}</h3>
            <p class="card__body">{{ rt(s.body) }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- DASHBOARD -->
    <section class="container section dashboard">
      <div>
        <p class="eyebrow">{{ t("home.dashboardLabel") }}</p>
        <h2 class="section-title">{{ t("home.dashboardTitle") }}</h2>
        <p class="dashboard__body">{{ t("home.dashboardBody") }}</p>
        <ul class="checklist">
          <li v-for="(p, i) in dashboardPoints" :key="i">
            <span class="checklist__check">✓</span>
            <span>{{ rt(p) }}</span>
          </li>
        </ul>
      </div>
      <img
        src="~/assets/dashbaord.png"
        :alt="t('home.dashboardTitle')"
        class="dashboard__preview"
      />
    </section>

    <!-- CTA -->
    <section class="container cta">
      <div class="cta__box">
        <h2 class="cta__title">{{ t("home.ctaTitle") }}</h2>
        <p class="cta__body">{{ t("home.ctaBody") }}</p>
        <div class="hero__actions">
          <a href="https://app.cubrel.com" class="btn btn--primary"
            >{{ t("home.ctaPrimary") }}<ExternalLinkIcon
          /></a>
          <NuxtLink :to="localePath('/pricing')" class="btn btn--ghost">{{
            t("nav.pricing")
          }}</NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.hero {
  padding-top: var(--space-16);
  padding-bottom: var(--space-12);
  text-align: center;
}

.hero__badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: 0.4rem 0.9rem;
  border: 1px solid var(--color-border);
  border-radius: 999px;
  font-size: 0.85rem;
  color: var(--color-body);
  margin-bottom: var(--space-6);
}

.hero__badge-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--color-success);
}

.hero__title {
  font-size: 3.5rem;
  line-height: 1.06;
  letter-spacing: -0.02em;
  font-weight: 800;
  max-width: 780px;
  margin: 0 auto var(--space-6);
}

.hero__subtitle {
  font-size: 1.25rem;
  line-height: 1.55;
  color: var(--color-body);
  max-width: 640px;
  margin: 0 auto var(--space-8);
}

.hero__actions {
  display: flex;
  gap: var(--space-4);
  justify-content: center;
  margin-bottom: var(--space-4);
}

.hero__note {
  font-size: 0.85rem;
  color: var(--color-muted);
}

.section {
  padding-top: var(--space-12);
  padding-bottom: var(--space-12);
}

.section-band {
  background: var(--color-surface-alt);
  border-top: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
  padding-top: var(--space-16);
  padding-bottom: var(--space-16);
}

.eyebrow {
  font-size: 0.8rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-primary);
  margin-bottom: var(--space-3);
}

.eyebrow--center {
  text-align: center;
  color: var(--color-muted);
}

.section-title {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  margin-bottom: var(--space-8);
}

.section-title--center {
  text-align: center;
}

.grid {
  display: grid;
  gap: var(--space-6);
}

.grid--3 {
  grid-template-columns: repeat(3, 1fr);
}

.card {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
  background: var(--color-surface);
}

.card__icon {
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  background: var(--color-primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  margin-bottom: var(--space-4);
}

.card__num {
  font-family: var(--font-sans);
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: var(--space-4);
}

.card__title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: var(--space-2);
}

.card__body {
  font-size: 0.9rem;
  color: var(--color-muted);
  line-height: 1.6;
}

.dashboard {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: var(--space-12);
  align-items: center;
}

.dashboard__body {
  font-size: 1.05rem;
  color: var(--color-body);
  margin: var(--space-4) 0 var(--space-6);
  line-height: 1.6;
}

.checklist {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.checklist li {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
  font-size: 0.95rem;
}

.checklist__check {
  color: var(--color-success);
  font-weight: 700;
}

.dashboard__preview {
  width: 100%;
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-md);
}

.cta {
  padding-bottom: var(--space-16);
}

.cta__box {
  background: var(--color-ink);
  border-radius: var(--radius-lg);
  padding: var(--space-16) var(--space-8);
  text-align: center;
}

.cta__title {
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--color-on-dark);
  margin-bottom: var(--space-4);
}

.cta__body {
  color: var(--color-on-dark-muted);
  max-width: 520px;
  margin: 0 auto var(--space-8);
}

.btn--ghost {
  background: rgba(255, 255, 255, 0.08);
  color: var(--color-on-dark);
  border-color: rgba(255, 255, 255, 0.16);
}
</style>
