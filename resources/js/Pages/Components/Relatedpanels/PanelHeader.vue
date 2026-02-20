<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import Panel from "./Panel.vue";
const props = defineProps({
  icon: String,
  count: Number,
  label: String,
  single_label: String,
});
</script>

<template>
  <div class="relatedpanels__item__header">
    <div class="relatedpanels__item__header__details">
      <div class="relatedpanels__item__header__details__title">
        <i :class="icon"></i>
        {{ $t(label) }}
      </div>

      <div class="relatedpanels__item__header__details__count">
        {{ count }}
      </div>
    </div>
    <div class="relatedpanels__item__header__actions">
      <button class="relatedpanels__item__header__actions__btn">
        <i class="fa-solid fa-plus"></i>
        <!-- <span>add {{ $t(single_label) }}</span> -->
      </button>
      <button class="relatedpanels__item__header__actions__btn">
        <i class="fa-solid fa-link"></i>
      </button>
    </div>
  </div>
</template>
