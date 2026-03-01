<template>
  <div class="color-picker" ref="root">
    <div
      class="color-picker__control"
      @click="toggle"
      :class="{ 'is-open': isOpen }"
    >
      <div class="color-picker__preview-wrapper">
        <div
          class="color-picker__preview-circle"
          :style="{ backgroundColor: modelValue || '#4f46e5' }"
        ></div>
        <span class="color-picker__value">{{
          modelValue || "Select Color"
        }}</span>
      </div>
      <i class="fa-solid fa-palette color-picker__icon"></i>
    </div>

    <transition name="picker-fade">
      <div v-if="isOpen" class="color-picker__panel">
        <div class="color-picker__grid">
          <button
            v-for="color in brandColors"
            :key="color"
            type="button"
            class="color-picker__swatch"
            :class="{ 'is-active': modelValue === color }"
            :style="{ backgroundColor: color }"
            @click="selectColor(color)"
            :title="color"
          ></button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
  modelValue: String,
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const root = ref(null);

// 64 Curated Professional Brand Colors (Diverse Palette)
const brandColors = [
  // Blues & Indigos (32)
  "#1E3A8A",
  "#1E40AF",
  "#1D4ED8",
  "#2563EB",
  "#3B82F6",
  "#60A5FA",
  "#93C5FD",
  "#BFDBFE",
  "#0F172A",
  "#1E293B",
  "#334155",
  "#475569",
  "#0EA5E9",
  "#0284C7",
  "#0369A1",
  "#075985",
  "#312E81",
  "#3730A3",
  "#4338CA",
  "#4F46E5",
  "#6366F1",
  "#818CF8",
  "#A5B4FC",
  "#C7D2FE",
  "#1F2937",
  "#111827",
  "#1F3A93",
  "#1A56DB",
  "#1C64F2",
  "#3F83F8",
  "#76A9FA",
  "#A4C8FF",

  // Teals & Cyans (32)
  "#0F766E",
  "#115E59",
  "#134E4A",
  "#0D9488",
  "#14B8A6",
  "#2DD4BF",
  "#5EEAD4",
  "#99F6E4",
  "#06B6D4",
  "#0891B2",
  "#0E7490",
  "#155E75",
  "#164E63",
  "#22D3EE",
  "#67E8F9",
  "#A5F3FC",
  "#083344",
  "#0E7490",
  "#14B8A6",
  "#2CB1BC",
  "#319795",
  "#2C7A7B",
  "#285E61",
  "#234E52",
  "#81E6D9",
  "#4FD1C5",
  "#38B2AC",
  "#319795",
  "#2C7A7B",
  "#285E61",
  "#134E4A",
  "#042F2E",

  // Greens (32)
  "#14532D",
  "#166534",
  "#15803D",
  "#16A34A",
  "#22C55E",
  "#4ADE80",
  "#86EFAC",
  "#BBF7D0",
  "#052E16",
  "#064E3B",
  "#065F46",
  "#047857",
  "#059669",
  "#10B981",
  "#34D399",
  "#6EE7B7",
  "#84CC16",
  "#65A30D",
  "#4D7C0F",
  "#3F6212",
  "#365314",
  "#22C55E",
  "#16A34A",
  "#15803D",
  "#4CAF50",
  "#43A047",
  "#388E3C",
  "#2E7D32",
  "#66BB6A",
  "#81C784",
  "#A5D6A7",
  "#C8E6C9",

  // Yellows & Oranges (32)
  "#78350F",
  "#92400E",
  "#B45309",
  "#D97706",
  "#F59E0B",
  "#FBBF24",
  "#FCD34D",
  "#FDE68A",
  "#EAB308",
  "#CA8A04",
  "#A16207",
  "#854D0E",
  "#9A3412",
  "#C2410C",
  "#EA580C",
  "#F97316",
  "#FB923C",
  "#FDBA74",
  "#FED7AA",
  "#FFEDD5",
  "#FFF7ED",
  "#FFB020",
  "#FFA000",
  "#FF8F00",
  "#FF6F00",
  "#FFB74D",
  "#FFCC80",
  "#FFE0B2",
  "#FFF3E0",
  "#FFE082",
  "#FFD54F",
  "#FFCA28",

  // Reds & Pinks (32)
  "#7F1D1D",
  "#991B1B",
  "#B91C1C",
  "#DC2626",
  "#EF4444",
  "#F87171",
  "#FCA5A5",
  "#FECACA",
  "#450A0A",
  "#7F1D1D",
  "#9F1239",
  "#BE123C",
  "#E11D48",
  "#F43F5E",
  "#FB7185",
  "#FDA4AF",
  "#EC4899",
  "#DB2777",
  "#C026D3",
  "#A21CAF",
  "#86198F",
  "#701A75",
  "#9D174D",
  "#BE185D",
  "#F06292",
  "#E91E63",
  "#D81B60",
  "#C2185B",
  "#AD1457",
  "#880E4F",
  "#FFCDD2",
  "#F8BBD0",

  // Purples & Violets (32)
  "#4C1D95",
  "#5B21B6",
  "#6D28D9",
  "#7C3AED",
  "#8B5CF6",
  "#A78BFA",
  "#C4B5FD",
  "#DDD6FE",
  "#581C87",
  "#6B21A8",
  "#7E22CE",
  "#9333EA",
  "#A855F7",
  "#C084FC",
  "#D8B4FE",
  "#E9D5FF",
  "#673AB7",
  "#5E35B1",
  "#512DA8",
  "#4527A0",
  "#7E57C2",
  "#9575CD",
  "#B39DDB",
  "#D1C4E9",
  "#311B92",
  "#6200EA",
  "#651FFF",
  "#7C4DFF",
  "#B388FF",
  "#EDE7F6",
  "#F3E5F5",
  "#E1BEE7",

  // Slates & Grays (32)
  "#111827",
  "#1F2937",
  "#374151",
  "#4B5563",
  "#6B7280",
  "#9CA3AF",
  "#D1D5DB",
  "#E5E7EB",
  "#F3F4F6",
  "#F9FAFB",
  "#27272A",
  "#3F3F46",
  "#52525B",
  "#71717A",
  "#A1A1AA",
  "#D4D4D8",
  "#E4E4E7",
  "#FAFAFA",
  "#18181B",
  "#27272A",
  "#3F3F46",
  "#52525B",
  "#71717A",
  "#A1A1AA",
  "#CFD8DC",
  "#B0BEC5",
  "#90A4AE",
  "#78909C",
  "#607D8B",
  "#546E7A",
  "#455A64",
  "#37474F",

  // Earth & Warm (32)
  "#3E2723",
  "#4E342E",
  "#5D4037",
  "#6D4C41",
  "#795548",
  "#8D6E63",
  "#A1887F",
  "#BCAAA4",
  "#D7CCC8",
  "#EFEBE9",
  "#5F4339",
  "#704214",
  "#8B4513",
  "#A0522D",
  "#CD853F",
  "#D2691E",
  "#DEB887",
  "#F4A460",
  "#C19A6B",
  "#AD8A64",
  "#8C7853",
  "#C2B280",
  "#E6BE8A",
  "#F5DEB3",
  "#A67B5B",
  "#7B3F00",
  "#654321",
  "#3D2B1F",
  "#8B5A2B",
  "#B5651D",
  "#A0522D",
  "#6F4E37",
];

const toggle = () => (isOpen.value = !isOpen.value);

const selectColor = (color) => {
  emit("update:modelValue", color);
  isOpen.value = false;
};

const handleClickOutside = (event) => {
  if (root.value && !root.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onUnmounted(() => document.removeEventListener("click", handleClickOutside));
</script>
