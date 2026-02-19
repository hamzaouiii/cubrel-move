<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
const props = defineProps({
  icon: String,
  count: Number,
  label: String,
});
</script>

<template>
  <div class="relatedpanels__item__header">
    <div class="relatedpanels__item__header__title">
      <i :class="icon"></i>
      {{ $t(label) }}
    </div>

    <div class="relatedpanels__item__header__count">
      {{ count }}
    </div>
  </div>
</template>
