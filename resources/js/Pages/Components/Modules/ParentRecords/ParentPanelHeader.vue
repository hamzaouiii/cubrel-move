<script setup>
const props = defineProps({
  icon: String,
  label: String,
  hasParent: Boolean,
});

const emit = defineEmits(["toggle", "open-overlay"]);
const openOverlay = () => {
  emit("open-overlay");
};
</script>

<template>
  <div class="parent_records__header">
    <div class="parent_records__header__details">
      <div class="parent_records__header__details__title">
        <i :class="icon"></i>
        {{ $t(label) }}
      </div>

      <div class="parent_records__header__details__count">
        {{ count }}
      </div>
    </div>
    <div class="parent_records__header__actions">
      <div class="parent_records__header__actions__edit" v-if="hasParent">
        <button class="parent_records__header__actions__btn" @click.prevent>
          <i class="fa-solid fa-pen"></i>
        </button>
      </div>
      <div class="parent_records__header__actions__add" v-else-if="!hasParent">
        <button class="parent_records__header__actions__btn" @click.prevent>
          <i class="fa-solid fa-plus"></i>
        </button>
        <button
          class="parent_records__header__actions__btn"
          @click.stop="openOverlay"
        >
          <i class="fa-solid fa-link"></i>
        </button>
      </div>
    </div>
  </div>
</template>
