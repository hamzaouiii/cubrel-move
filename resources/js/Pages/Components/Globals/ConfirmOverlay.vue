<script setup>
import { ref, watch, nextTick } from "vue";
import { useConfirm } from "@/Composables/useConfirm";
import { usePage } from "@inertiajs/vue3";
const { confirmState, accept, cancel } = useConfirm();
const overlayRef = ref(null);

const page = usePage();
const appSettings = page.props.appSettings;

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
  { immediate: true },
);

const primaryColor = appSettings.primary_color;
const secondaryColor = appSettings.secondary_color;
const dangerColor = appSettings.danger_color;
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
      :style="[
        { '--primary-color': primaryColor },
        { '--secondary-color': secondaryColor },
        { '--danger-color': dangerColor },
      ]"
    >
      <div class="confirm-overlay__dialog">
        <div class="confirm-overlay__title">
          {{ confirmState.title ?? $t("globals.confirm.title") }}
        </div>
        <div class="confirm-overlay__message">
          <template v-if="confirmState.highlight !== null">
            {{ confirmState.message.split(confirmState.highlight)[0] }}
            <span
              :class="[
                {
                  'confirm-overlay__highlight--danger': confirmState.danger,
                },
                {
                  'confirm-overlay__highlight': !confirmState.danger,
                },
              ]"
            >
              {{ confirmState.highlight }}
            </span>
            {{ confirmState.message.split(confirmState.highlight)[1] }}
          </template>

          <template v-else>
            {{ confirmState.message ?? $t("globals.confirm.message") }}
          </template>
        </div>

        <div class="confirm-overlay__actions">
          <button class="confirm-overlay__actions--cancel" @click="cancel">
            {{ confirmState.cancelText ?? $t("globals.confirm.cancel_text") }}
          </button>

          <button
            :class="[
              confirmState.danger
                ? ' confirm-overlay__actions--danger'
                : 'confirm-overlay__actions--primary',
            ]"
            @click="accept"
          >
            {{ confirmState.confirmText ?? $t("globals.confirm.confirm_text") }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>
