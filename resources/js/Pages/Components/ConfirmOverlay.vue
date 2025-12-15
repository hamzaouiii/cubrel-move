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
      <div class="confirm-dialog card-shadow" role="dialog" aria-modal="true">
        <div class="confirm-title">{{ confirmState.title }}</div>
        <div class="confirm-message">{{ confirmState.message }}</div>

        <div class="confirm-actions">
          <button type="button" class="btn btn-secondary" @click="cancel">
            {{ confirmState.cancelText }}
          </button>

          <button
            type="button"
            class="btn"
            :class="confirmState.danger ? 'btn-danger' : 'btn-primary'"
            @click="accept"
          >
            {{ confirmState.confirmText }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

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
