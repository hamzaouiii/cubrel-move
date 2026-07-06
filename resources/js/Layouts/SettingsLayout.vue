<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import SettingsRail from "@/Pages/Components/Settings/SettingsRail.vue";

const COLLAPSE_BREAKPOINT = 1024;
const STORAGE_KEY = "cubrel_settings_rail_collapsed";

const isNarrow = ref(false);
const manualOverride = ref(null); // null = follow viewport width; true/false = user-forced

// Collapsed by default under the breakpoint, unless the user has explicitly toggled it.
const collapsed = computed(() => manualOverride.value ?? isNarrow.value);

function checkWidth() {
  isNarrow.value = window.innerWidth <= COLLAPSE_BREAKPOINT;
}

function setCollapsed(value) {
  manualOverride.value = value;
  localStorage.setItem(STORAGE_KEY, value ? "1" : "0");
}

function toggleCollapsed() {
  setCollapsed(!collapsed.value);
}

function closeIfNarrow() {
  if (isNarrow.value) {
    setCollapsed(true);
  }
}

onMounted(() => {
  checkWidth();
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored !== null) {
    manualOverride.value = stored === "1";
  }
  window.addEventListener("resize", checkWidth);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkWidth);
});
</script>

<template>
  <div
    class="settings-shell"
    :class="{
      'settings-shell--collapsed': collapsed,
      'settings-shell--narrow': isNarrow,
    }"
  >
    <div
      v-if="isNarrow && !collapsed"
      class="settings-shell__backdrop"
      @click="closeIfNarrow"
    ></div>

    <SettingsRail class="settings-shell__rail" @collapse="setCollapsed(true)" />

    <div class="settings-shell__content">
      <button
        v-if="collapsed"
        type="button"
        class="settings-shell__expand-btn"
        :aria-label="$t('settings.expand_menu')"
        @click="toggleCollapsed"
      >
        <i class="fa-solid fa-bars"></i>
        <span>{{ $t("settings.expand_menu") }}</span>
      </button>
      <slot />
    </div>
  </div>
</template>
