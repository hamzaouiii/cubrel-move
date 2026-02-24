<script setup>
const props = defineProps({
  icon: String,
  count: Number,
  label: String,
  single_label: String,
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
        {{ $t(label) }}
      </div>

      <div class="relatedpanels__item__header__details__count">
        {{ count }}
      </div>
    </div>
    <div class="relatedpanels__item__header__actions">
      <button class="relatedpanels__item__header__actions__btn" @click.prevent>
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
</template>
