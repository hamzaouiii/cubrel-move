<script setup>
const { t, tm, rt } = useI18n();
const tiers = computed(() => tm("pricing.tiers"));
const compare = computed(() => tm("pricing.compare"));
const faqs = computed(() => tm("pricing.faqs"));

useHead({ title: () => `${t("pricing.title")} — ${t("brand.name")}` });
</script>

<template>
  <div>
    <section class="container page-header">
      <p class="eyebrow eyebrow--center">{{ t("pricing.eyebrow") }}</p>
      <h1 class="page-header__title">{{ t("pricing.title") }}</h1>
      <p class="page-header__subtitle">{{ t("pricing.subtitle") }}</p>
    </section>

    <section class="container tiers">
      <div v-for="(tier, i) in tiers" :key="i" class="tier" :class="{ 'tier--highlight': tier.badge }">
        <div class="tier__name-row">
          <h3 class="tier__name">{{ rt(tier.name) }}</h3>
          <span v-if="tier.badge" class="tier__badge">{{ rt(tier.badge) }}</span>
        </div>
        <p class="tier__tagline">{{ rt(tier.tagline) }}</p>
        <div class="tier__price-row">
          <span class="tier__price">{{ rt(tier.price) }}</span>
          <span class="tier__period">{{ rt(tier.period) }}</span>
        </div>
        <a href="https://app.cubrel.com" class="btn tier__cta" :class="tier.badge ? 'btn--primary' : 'btn--secondary'">
          {{ rt(tier.cta) }}<ExternalLinkIcon />
        </a>
        <ul class="tier__features">
          <li v-for="(feat, j) in tier.features" :key="j">
            <span class="tier__check">✓</span>
            <span>{{ rt(feat) }}</span>
          </li>
        </ul>
      </div>
    </section>

    <section class="container compare">
      <h2 class="section-title section-title--center">{{ t("pricing.compareTitle") }}</h2>
      <div class="compare-table">
        <div class="compare-row compare-row--head">
          <span>{{ t("features.eyebrow") }}</span>
          <span class="compare-cell">Free</span>
          <span class="compare-cell">Team</span>
          <span class="compare-cell">Business</span>
        </div>
        <div v-for="(row, i) in compare" :key="i" class="compare-row">
          <span>{{ rt(row.label) }}</span>
          <span class="compare-cell">{{ rt(row.free) }}</span>
          <span class="compare-cell">{{ rt(row.team) }}</span>
          <span class="compare-cell">{{ rt(row.business) }}</span>
        </div>
      </div>
    </section>

    <section class="container faq">
      <h2 class="section-title section-title--center">{{ t("pricing.faqTitle") }}</h2>
      <div class="faq-list">
        <div v-for="(f, i) in faqs" :key="i" class="faq-item">
          <h3 class="faq-item__q">{{ rt(f.q) }}</h3>
          <p class="faq-item__a">{{ rt(f.a) }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.eyebrow {
  font-size: 0.8rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-primary);
  margin-bottom: var(--space-3);
}

.eyebrow--center {
  text-align: center;
}

.page-header {
  padding-top: var(--space-16);
  padding-bottom: var(--space-6);
  text-align: center;
}

.page-header__title {
  font-size: 2.75rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  max-width: 680px;
  margin: 0 auto var(--space-4);
}

.page-header__subtitle {
  font-size: 1.1rem;
  color: var(--color-body);
  max-width: 560px;
  margin: 0 auto;
}

.tiers {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-6);
  padding-top: var(--space-8);
  padding-bottom: var(--space-8);
  align-items: stretch;
}

.tier {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--space-8);
  display: flex;
  flex-direction: column;
}

.tier--highlight {
  background: var(--color-ink);
  border-color: var(--color-ink);
  color: #fff;
}

.tier__name-row {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  margin-bottom: var(--space-2);
}

.tier__name {
  font-size: 1.1rem;
  font-weight: 700;
}

.tier__badge {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  background: var(--color-primary);
  color: #fff;
}

.tier__tagline {
  font-size: 0.9rem;
  color: var(--color-muted);
  margin-bottom: var(--space-6);
}

.tier--highlight .tier__tagline {
  color: #94a3b8;
}

.tier__price-row {
  display: flex;
  align-items: baseline;
  gap: var(--space-2);
  margin-bottom: var(--space-6);
}

.tier__price {
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.tier__period {
  font-size: 0.9rem;
  color: var(--color-muted);
}

.tier--highlight .tier__period {
  color: #94a3b8;
}

.tier__cta {
  text-align: center;
  margin-bottom: var(--space-6);
  text-decoration: none;
}

.tier__features {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.tier__features li {
  display: flex;
  gap: var(--space-3);
  font-size: 0.9rem;
  align-items: flex-start;
}

.tier__check {
  color: #10b981;
  font-weight: 700;
}

.tier--highlight .tier__check {
  color: #6ee7b7;
}

.section-title {
  font-size: 1.75rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  margin-bottom: var(--space-8);
}

.section-title--center {
  text-align: center;
}

.compare {
  padding-top: var(--space-12);
  padding-bottom: var(--space-8);
}

.compare-table {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.compare-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  padding: var(--space-4) var(--space-6);
  border-top: 1px solid var(--color-border);
  align-items: center;
  font-size: 0.9rem;
}

.compare-row:first-child {
  border-top: none;
}

.compare-row--head {
  background: var(--color-surface-alt);
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--color-muted);
}

.compare-cell {
  text-align: center;
}

.faq {
  padding-top: var(--space-8);
  padding-bottom: var(--space-16);
  max-width: 820px;
  margin: 0 auto;
}

.faq-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.faq-item {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
}

.faq-item__q {
  font-size: 1.05rem;
  font-weight: 700;
  margin-bottom: var(--space-2);
}

.faq-item__a {
  font-size: 0.9rem;
  color: var(--color-muted);
  line-height: 1.6;
}
</style>
