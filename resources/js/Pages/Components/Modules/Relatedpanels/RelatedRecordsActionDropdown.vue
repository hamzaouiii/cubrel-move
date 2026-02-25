<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from "vue";

const props = defineProps({
  isMenuOpen: Boolean,
  triggerEl: Object,
  related_slug: String,
  record: Object,
  getRelatedRecordurl: Function,
});

const emit = defineEmits(["quick-edit", "unlink", "close"]);

const menuRef = ref(null);
const menuStyle = ref({});

const MENU_WIDTH = 180;
const OFFSET = 6; // small spacing from button

const updatePosition = () => {
  if (!props.triggerEl || !menuRef.value) return;

  const rect = props.triggerEl.getBoundingClientRect();
  const menuRect = menuRef.value.getBoundingClientRect();

  let top = rect.bottom + OFFSET;
  let left = rect.right - MENU_WIDTH;

  // 🔥 Flip vertically if near bottom
  if (rect.bottom + menuRect.height > window.innerHeight) {
    top = rect.top - menuRect.height - OFFSET;
  }

  // Prevent right overflow
  if (left + MENU_WIDTH > window.innerWidth) {
    left = window.innerWidth - MENU_WIDTH - 8;
  }

  // Prevent left overflow
  if (left < 8) {
    left = 8;
  }

  menuStyle.value = {
    position: "fixed",
    top: `${top}px`,
    left: `${left}px`,
    zIndex: 99,
  };
};

const handleClickOutside = (e) => {
  if (!props.isMenuOpen) return;

  const clickedInsideMenu = menuRef.value?.contains(e.target);
  const clickedTrigger = props.triggerEl?.contains(e.target);

  if (!clickedInsideMenu && !clickedTrigger) {
    emit("close");
  }
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

onMounted(() => {
  window.addEventListener("resize", updatePosition);
  window.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener("resize", updatePosition);
  window.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <Teleport to="body">
    <ul
      v-if="isMenuOpen"
      ref="menuRef"
      class="actiondropdown-menu"
      :style="menuStyle"
    >
      <a
        :href="getRelatedRecordurl(related_slug, record.id)"
        target="_blank"
        rel="noopener noreferrer"
      >
        <li>
          <i class="fa-solid fa-up-right-from-square"></i>
          <span>{{ $t("modules.actions.open_new_tab") }}</span>
        </li>
      </a>

      <li @click="$emit('quick-edit', record)" class="disabled">
        <i class="fa-solid fa-brush"></i>
        <span>{{ $t("modules.actions.quick_edit") }}</span>
      </li>

      <li class="actiondropdown-menu__divider"></li>

      <li class="actiondropdown-menu__unlink" @click="$emit('unlink', record)">
        <i class="fa-solid fa-link-slash"></i>
        <span>{{ $t("modules.actions.unlink") }}</span>
      </li>
    </ul>
  </Teleport>
</template>
