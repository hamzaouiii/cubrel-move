<script setup>
import { computed } from "vue";
const props = defineProps({
  icon: String,
  count: Number,
  label: String,
  single_label: String,
  type: String,
});

const hasRecords = computed(() => {
  return props.count ?? false;
});
const emit = defineEmits(["toggle", "open-overlay"]);
const openOverlay = () => {
  emit("open-overlay");
};
const emittogglePanel = () => {
  emit("toggle");
};
</script>

<template>
  <div class="relatedpanels__item__header" @click="emittogglePanel()">
    <div class="relatedpanels__item__header__details">
      <div class="relatedpanels__item__header__details__title">
        <i :class="icon"></i>
        {{ $t(type != "parent" ? single_label : label) }}
      </div>

      <div class="relatedpanels__item__header__details__count">
        {{ type != "parent" ? "" : count }}
      </div>
    </div>
    <div class="relatedpanels__item__header__actions">
      <div v-if="hasRecords && type != 'parent'">
        <button
          class="relatedpanels__item__header__actions__btn"
          @click.prevent
        >
          <i class="fa-solid fa-pen"></i>
        </button>
      </div>
      <div v-else>
        <button
          class="relatedpanels__item__header__actions__btn"
          @click.prevent
        >
          <i class="fa-solid fa-plus"></i>
          <!-- <span>add {{ $t(single_label) }}</span> -->
        </button>
        <button
          class="relatedpanels__item__header__actions__btn"
          @click.stop="openOverlay"
        >
          <i class="fa-solid fa-link"></i>
        </button>
      </div>
    </div>
  </div>
</template>
