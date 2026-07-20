<script setup>
const props = defineProps({
  eyebrow: { type: String, required: true },
  title: { type: String, required: true },
  body: { type: String, required: true },
  image: { type: String, required: true },
  imageAlt: { type: String, required: true },
  reverse: { type: Boolean, default: false },
});

const { t } = useI18n();
</script>

<template>
  <section class="showcase" :class="{ 'showcase--reverse': props.reverse }">
    <div class="showcase__text">
      <p class="eyebrow">{{ eyebrow }}</p>
      <h1 class="showcase__title">{{ title }}</h1>
      <p class="showcase__body">{{ body }}</p>
      <a href="https://app.cubrel.com" class="btn btn--primary">{{ t('home.ctaPrimary') }}<ExternalLinkIcon /></a>
    </div>
    <div class="showcase__media">
      <img :src="image" :alt="imageAlt" />
    </div>
  </section>
</template>

<style scoped>
.eyebrow {
  font-size: 0.8rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-primary);
  margin-bottom: var(--space-3);
}

.showcase {
  display: grid;
  grid-template-columns: 0.85fr 1.15fr;
  gap: var(--space-12);
  align-items: center;
  padding-bottom: var(--space-16);
}

.showcase--reverse {
  grid-template-columns: 1.15fr 0.85fr;
}

.showcase--reverse .showcase__text {
  order: 2;
}

.showcase--reverse .showcase__media {
  order: 1;
}

.showcase__title {
  font-size: 2.25rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  line-height: 1.15;
  margin-bottom: var(--space-4);
}

.showcase__body {
  font-size: 1.05rem;
  color: var(--color-body);
  line-height: 1.6;
  margin-bottom: var(--space-8);
}

.showcase__media {
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.showcase__media img {
  width: 100%;
  display: block;
}
</style>
