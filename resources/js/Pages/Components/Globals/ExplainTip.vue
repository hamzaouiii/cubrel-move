<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";

const props = defineProps({
  text: { type: String, required: true },
  color: { type: String },
});

const visible = ref(false);
const trigger = ref(null);

const currentColor = computed(() => props.color || null);

function show() {
  visible.value = true;
}
function hide() {
  visible.value = false;
}
function toggle() {
  visible.value = !visible.value;
}
function onOutsideClick(e) {
  if (trigger.value && !trigger.value.contains(e.target)) {
    visible.value = false;
  }
}

// Relative luminance → pick black or white text
function hexToRgb(hex) {
  const h = hex.replace("#", "");
  const full =
    h.length === 3
      ? h
          .split("")
          .map((c) => c + c)
          .join("")
      : h;
  return [
    parseInt(full.slice(0, 2), 16),
    parseInt(full.slice(2, 4), 16),
    parseInt(full.slice(4, 6), 16),
  ];
}
function luminance([r, g, b]) {
  return [r, g, b]
    .map((v) => {
      const s = v / 255;
      return s <= 0.04045 ? s / 12.92 : ((s + 0.055) / 1.055) ** 2.4;
    })
    .reduce((sum, v, i) => sum + v * [0.2126, 0.7152, 0.0722][i], 0);
}

const textColor = computed(() => {
  if (!currentColor.value) return "#fefefe";
  try {
    return luminance(hexToRgb(currentColor.value)) > 0.35
      ? "#1a1a1a"
      : "#fefefe";
  } catch {
    return "#fefefe";
  }
});

onMounted(() => document.addEventListener("click", onOutsideClick, true));
onBeforeUnmount(() =>
  document.removeEventListener("click", onOutsideClick, true),
);
</script>

<template>
  <span
    class="explain-tip"
    ref="trigger"
    :style="{ '--tip-color': currentColor, '--tip-text': textColor }"
  >
    <button
      type="button"
      class="explain-tip__icon"
      @mouseenter="show"
      @mouseleave="hide"
      @click.stop="toggle"
      aria-label="More information"
    >
      <i class="fa-solid fa-circle-info"></i>
    </button>
    <span v-show="visible" class="explain-tip__popover" role="tooltip" style="text-align: left;">
      {{ text }}
    </span>
  </span>
</template>
