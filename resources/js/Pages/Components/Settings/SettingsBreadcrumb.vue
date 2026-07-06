<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  crumbs: { type: Array, required: true }, // [{ label, href? }]
});
</script>

<template>
  <div class="settings__breadcrumbs">
    <template v-for="(crumb, index) in crumbs" :key="index">
      <Link v-if="crumb.href" :href="crumb.href" class="breadcrumb-link">{{
        crumb.label
      }}</Link>
      <span v-else class="breadcrumb-item--current">{{ crumb.label }}</span>
      <span v-if="index < crumbs.length - 1" class="breadcrumb-separator">
        <i class="fa-solid fa-chevron-right"></i>
      </span>
    </template>
  </div>
</template>
