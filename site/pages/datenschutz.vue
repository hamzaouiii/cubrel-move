<script setup>
const { t } = useI18n();
useHead({ title: () => `${t("legal.datenschutzTitle")} — ${t("brand.name")}` });
</script>

<template>
  <div class="container page">
    <h1 class="page__title">{{ t("legal.datenschutzTitle") }}</h1>
    <p class="page__body">{{ t("legal.datenschutzBody") }}</p>
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-16);
  padding-bottom: var(--space-16);
  max-width: 720px;
}

.page__title {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: var(--space-6);
}

.page__body {
  color: var(--color-body);
  line-height: 1.6;
}
</style>
