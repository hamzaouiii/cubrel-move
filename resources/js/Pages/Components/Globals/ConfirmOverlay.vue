<script setup>
import { ref, watch, nextTick } from "vue";
import { useConfirm } from "@/Composables/useConfirm";

const { confirmState, accept, cancel } = useConfirm();
const overlayRef = ref(null);

watch(
  () => confirmState.isOpen,
  async (open) => {
    if (open) {
      await nextTick();
      overlayRef.value?.focus();
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
  },
  { immediate: true }
);
</script>

<template>
  <teleport to="body">
    <div
      v-if="confirmState.isOpen"
      class="confirm-overlay"
      @keydown.esc.prevent="cancel"
      tabindex="0"
      ref="overlayRef"
      @click.self="cancel"
    >
      <div class="confirm-overlay__dialog">
        <div class="confirm-overlay__title">{{ confirmState.title }}</div>
        <div class="confirm-overlay__message">{{ confirmState.message }}</div>

        <div class="confirm-overlay__actions">
          <button class="confirm-overlay__actions--cancel btn" @click="cancel">
            {{ confirmState.cancelText }}
          </button>

          <button
            :class="[
              confirmState.danger
                ? ' confirm-overlay__actions--danger'
                : 'button-primary',
              'btn',
            ]"
            @click="accept"
          >
            {{ confirmState.confirmText }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>
