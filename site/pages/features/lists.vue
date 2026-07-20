<script setup>
import img from "~/assets/features/lists.webp";

const { t, tm, rt } = useI18n();
const area = computed(() => tm("features.areas")[1]);

useHead({ title: () => `${rt(area.value.navLabel)} — ${t("brand.name")}` });
</script>

<template>
  <div class="container page">
    <FeatureNav />
    <FeatureShowcase
      reverse
      :eyebrow="rt(area.eyebrow)"
      :title="rt(area.title)"
      :body="rt(area.body)"
      :image="img"
      :image-alt="rt(area.imageAlt)"
    />
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-12);
}
</style>
