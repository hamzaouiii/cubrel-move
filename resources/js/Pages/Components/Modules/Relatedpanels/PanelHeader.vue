<script setup>
import { computed, getCurrentInstance, ref } from "vue";

const props = defineProps({
  icon: String,
  dislplayCount: Number,
  totalCount: Number,
  label: String,
  single_label: String,
  type: String,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const isOpen = ref(false);
const hasRecords = computed(() => {
  return props.count ?? false;
});
const emit = defineEmits(["toggle", "open-overlay", "unlink-parent"]);
const openOverlay = () => {
  emit("open-overlay");
};
const emittogglePanel = () => {
  isOpen.value = !isOpen.value;
  emit("toggle");
};

const emitUnlinkParent = () => {
  emit("unlink-parent");
};

const countPhrase = computed(() => {
  if (props.type === "parent" && props.dislplayCount > 0) {
    return `${props.dislplayCount} ${t("modules.of")} ${props.totalCount}`;
  } else return false;
});
</script>

<template>
  <div class="relatedpanels__item__header" @click="emittogglePanel()">
    <div class="relatedpanels__item__header__details">
      <div class="relatedpanels__item__header__details__title">
        <i :class="icon"></i>
        {{ $t(type != "parent" ? single_label : label) }}
      </div>
      <div v-if="isOpen" class="relatedpanels__item__header__details__count">
        {{ totalCount }}
      </div>
      <div
        v-else-if="countPhrase"
        class="relatedpanels__item__header__details__count"
      >
        {{ countPhrase }}
      </div>
    </div>
    <div class="relatedpanels__item__header__actions">
      <div v-if="hasRecords && type != 'parent'">
        <button
          class="relatedpanels__item__header__actions__btn"
          @click.stop="emitUnlinkParent"
        >
          <i class="fa-solid fa-link-slash"></i>
        </button>
        <button
          class="relatedpanels__item__header__actions__btn"
          @click.stop="openOverlay"
        >
          <i class="fa-solid fa-pen"></i>
        </button>
      </div>
      <div v-else>
        <!-- <button
          class="relatedpanels__item__header__actions__btn"
          @click.prevent
        >
          <i class="fa-solid fa-plus"></i>
        </button> -->
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
