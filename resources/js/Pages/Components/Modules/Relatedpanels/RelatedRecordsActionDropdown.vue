<script setup>
import { ref, watch, nextTick } from "vue";

const props = defineProps({
  isMenuOpen: Boolean,
  triggerEl: Object,
  related_slug: String,
  record: Object,
  getRelatedRecordurl: Function,
});

const emit = defineEmits(["quick-edit", "unlink"]);

const menuStyle = ref({});

const MENU_WIDTH = 180;

const updatePosition = () => {
  if (!props.triggerEl) return;

  const rect = props.triggerEl.getBoundingClientRect();

  menuStyle.value = {
    position: "fixed",
    top: `${rect.bottom}px`,
    left: `${rect.right - 180}px`,
    zIndex: 9999,
  };
};

watch(
  () => props.isMenuOpen,
  async (open) => {
    if (open && props.triggerEl) {
      await nextTick();
      updatePosition();
    }
  },
);
console.log("triggerRef:", props.triggerRef?.value);
</script>
<template>
  <Teleport to="body">
    <ul v-if="isMenuOpen" class="actiondropdown-menu" :style="menuStyle">
      <a
        :href="getRelatedRecordurl(related_slug, record.id)"
        target="_blank"
        rel="noopener noreferrer"
      >
        <li>
          <i class="fa-solid fa-up-right-from-square"></i>
          <span>Open in a new Tab</span>
        </li>
      </a>

      <li @click="$emit('quick-edit', record)" class="disabled">
        <i class="fa-solid fa-brush"></i>
        <span>Quick edit</span>
      </li>

      <li class="actiondropdown-menu__divider"></li>

      <li class="actiondropdown-menu__unlink" @click="$emit('unlink', record)">
        <i class="fa-solid fa-link-slash"></i>
        <span>Unlink</span>
      </li>
    </ul>
  </Teleport>
</template>
