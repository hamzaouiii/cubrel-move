<script setup>
import {
  ref,
  computed,
  watch,
  nextTick,
  onMounted,
  onUnmounted,
  getCurrentInstance,
} from "vue";
import { useDropdownFlip } from "@/Composables/useDropdownFlip";

const props = defineProps({
  modelValue: String,
});

const emit = defineEmits(["update:modelValue"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const RECENTS_KEY = "cubrel.colorpicker.recents";
const RECENTS_MAX = 8;

const recentColors = ref(loadRecents());

function loadRecents() {
  try {
    const raw = localStorage.getItem(RECENTS_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function addRecent(color) {
  const c = color.toUpperCase();
  const next = [c, ...recentColors.value.filter((x) => x !== c)].slice(
    0,
    RECENTS_MAX,
  );
  recentColors.value = next;
  try {
    localStorage.setItem(RECENTS_KEY, JSON.stringify(next));
  } catch {

  }
}

const isOpen = ref(false);
const root = ref(null);
const { flipUp, recalc } = useDropdownFlip(root, { menuHeight: 480 });

const activeTab = ref("palette");
const searchQuery = ref("");

const hexInput = ref("");
const hexInputError = ref("");

const hue = ref(210);
const sat = ref(0.6);
const val = ref(1);
const svArea = ref(null);
const hueBar = ref(null);

function hsvToRgb(h, s, v) {
  const c = v * s;
  const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
  const m = v - c;
  let r = 0,
    g = 0,
    b = 0;
  if (h < 60) [r, g, b] = [c, x, 0];
  else if (h < 120) [r, g, b] = [x, c, 0];
  else if (h < 180) [r, g, b] = [0, c, x];
  else if (h < 240) [r, g, b] = [0, x, c];
  else if (h < 300) [r, g, b] = [x, 0, c];
  else [r, g, b] = [c, 0, x];
  return {
    r: Math.round((r + m) * 255),
    g: Math.round((g + m) * 255),
    b: Math.round((b + m) * 255),
  };
}

function rgbToHex(r, g, b) {
  const h = (n) => n.toString(16).padStart(2, "0").toUpperCase();
  return `#${h(r)}${h(g)}${h(b)}`;
}

function normalizeHex(input) {
  if (!input) return null;
  let hex = input.trim();
  if (!hex.startsWith("#")) hex = "#" + hex;
  if (!/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(hex)) return null;
  if (hex.length === 4) {
    hex = `#${hex[1]}${hex[1]}${hex[2]}${hex[2]}${hex[3]}${hex[3]}`;
  }
  return hex.toUpperCase();
}

function hexToRgb(hex) {
  const num = parseInt(hex.slice(1), 16);
  return { r: (num >> 16) & 255, g: (num >> 8) & 255, b: num & 255 };
}

function rgbToHsv(r, g, b) {
  r /= 255;
  g /= 255;
  b /= 255;
  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  const d = max - min;
  let h = 0;
  if (d !== 0) {
    if (max === r) h = ((g - b) / d) % 6;
    else if (max === g) h = (b - r) / d + 2;
    else h = (r - g) / d + 4;
    h *= 60;
    if (h < 0) h += 360;
  }
  return { h, s: max === 0 ? 0 : d / max, v: max };
}

const excludedWhiteShades = new Set([
  "#F9FAFB",
  "#FAFAFA",
  "#F3F4F6",
  "#E5E7EB",
  "#D1D5DB",
  "#E4E4E7",
  "#FFF7ED",
  "#FFF3E0",
  "#FFEDD5",
]);

const colorPalette = [
  {
    shadeKey: "fields.colorpicker.shades.blues",
    keywordKey: "fields.colorpicker.keywords.blues",
    colors: [

      "#1E3A8A",
      "#1E40AF",
      "#1D4ED8",
      "#2563EB",
      "#3B82F6",
      "#60A5FA",
      "#93C5FD",
      "#BFDBFE",

      "#0C4A6E",
      "#075985",
      "#0369A1",
      "#0284C7",
      "#0EA5E9",
      "#38BDF8",
      "#7DD3FC",
      "#BAE6FD",

      "#312E81",
      "#3730A3",
      "#4338CA",
      "#4F46E5",
      "#6366F1",
      "#818CF8",
      "#A5B4FC",
      "#C7D2FE",

      "#0F172A",
      "#1E293B",
      "#334155",
      "#475569",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.teals",
    keywordKey: "fields.colorpicker.keywords.teals",
    colors: [

      "#134E4A",
      "#115E59",
      "#0F766E",
      "#0D9488",
      "#14B8A6",
      "#2DD4BF",
      "#5EEAD4",
      "#99F6E4",

      "#164E63",
      "#155E75",
      "#0E7490",
      "#0891B2",
      "#06B6D4",
      "#22D3EE",
      "#67E8F9",
      "#A5F3FC",

      "#064E3B",
      "#065F46",
      "#047857",
      "#059669",
      "#10B981",
      "#34D399",
      "#6EE7B7",
      "#A7F3D0",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.greens",
    keywordKey: "fields.colorpicker.keywords.greens",
    colors: [

      "#14532D",
      "#166534",
      "#15803D",
      "#16A34A",
      "#22C55E",
      "#4ADE80",
      "#86EFAC",
      "#BBF7D0",

      "#365314",
      "#3F6212",
      "#4D7C0F",
      "#65A30D",
      "#84CC16",
      "#A3E635",
      "#BEF264",
      "#D9F99D",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.yellows",
    keywordKey: "fields.colorpicker.keywords.yellows",
    colors: [

      "#78350F",
      "#92400E",
      "#B45309",
      "#D97706",
      "#F59E0B",
      "#FBBF24",
      "#FCD34D",
      "#FDE68A",

      "#713F12",
      "#854D0E",
      "#A16207",
      "#CA8A04",
      "#EAB308",
      "#FACC15",
      "#FDE047",

      "#7C2D12",
      "#9A3412",
      "#C2410C",
      "#EA580C",
      "#F97316",
      "#FB923C",
      "#FDBA74",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.reds",
    keywordKey: "fields.colorpicker.keywords.reds",
    colors: [

      "#7F1D1D",
      "#991B1B",
      "#B91C1C",
      "#DC2626",
      "#EF4444",
      "#F87171",
      "#FCA5A5",
      "#FECACA",

      "#881337",
      "#9F1239",
      "#BE123C",
      "#E11D48",
      "#F43F5E",
      "#FB7185",
      "#FDA4AF",

      "#831843",
      "#9D174D",
      "#BE185D",
      "#DB2777",
      "#EC4899",
      "#F472B6",
      "#F9A8D4",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.purples",
    keywordKey: "fields.colorpicker.keywords.purples",
    colors: [

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

      "#701A75",
      "#86198F",
      "#A21CAF",
      "#C026D3",
      "#D946EF",
      "#E879F9",
      "#F0ABFC",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.slates",
    keywordKey: "fields.colorpicker.keywords.slates",
    colors: [

      "#0F172A",
      "#1E293B",
      "#334155",
      "#475569",
      "#64748B",
      "#94A3B8",
      "#CBD5E1",

      "#111827",
      "#1F2937",
      "#374151",
      "#4B5563",
      "#6B7280",
      "#9CA3AF",

      "#18181B",
      "#27272A",
      "#3F3F46",
      "#52525B",
      "#71717A",
      "#A1A1AA",

      "#171717",
      "#262626",
      "#404040",
      "#525252",
      "#737373",
      "#A3A3A3",
    ],
  },
  {
    shadeKey: "fields.colorpicker.shades.earth",
    keywordKey: "fields.colorpicker.keywords.earth",
    colors: [

      "#3E2723",
      "#4E342E",
      "#5D4037",
      "#6D4C41",
      "#795548",
      "#8D6E63",
      "#A1887F",
      "#BCAAA4",

      "#292524",
      "#44403C",
      "#57534E",
      "#78716C",
      "#A8A29E",
      "#D6D3D1",

      "#7B3F00",
      "#8B4513",
      "#A0522D",
      "#CD853F",
      "#D2691E",
      "#DEB887",
      "#F4A460",
    ],
  },
];

const translatedGroups = computed(() =>
  colorPalette
    .map((group) => {
      const shade = (t(group.shadeKey) || "").toLowerCase();
      const keywords = (t(group.keywordKey) || "").toLowerCase();
      return {
        ...group,
        label: t(group.shadeKey),
        searchBlob: `${shade} ${keywords}`,
        colors: [
          ...new Set(group.colors.filter((c) => !excludedWhiteShades.has(c))),
        ],
      };
    })
    .filter((group) => group.colors.length > 0),
);

const filteredGroups = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return translatedGroups.value;
  return translatedGroups.value.filter((g) => g.searchBlob.includes(query));
});

const hasResults = computed(() =>
  filteredGroups.value.some((g) => g.colors.length > 0),
);

const currentHex = computed(() => {
  const { r, g, b } = hsvToRgb(hue.value, sat.value, val.value);
  return rgbToHex(r, g, b);
});

const hueColor = computed(() => `hsl(${hue.value}, 100%, 50%)`);

function syncFromHex(hex) {
  const normalized = normalizeHex(hex);
  if (!normalized) return;
  const { r, g, b } = hexToRgb(normalized);
  const hsv = rgbToHsv(r, g, b);

  if (hsv.s > 0) hue.value = hsv.h;
  sat.value = hsv.s;
  val.value = hsv.v;
}

function startDrag(el, handler, evt) {
  const rect = el.getBoundingClientRect();
  const update = (e) => {
    const point = e.touches ? e.touches[0] : e;
    const fx = clamp((point.clientX - rect.left) / rect.width, 0, 1);
    const fy = clamp((point.clientY - rect.top) / rect.height, 0, 1);
    handler(fx, fy);
    emitCurrent();
  };
  update(evt);
  const move = (e) => update(e);
  const stop = () => {
    window.removeEventListener("pointermove", move);
    window.removeEventListener("pointerup", stop);
    addRecent(currentHex.value);
  };
  window.addEventListener("pointermove", move);
  window.addEventListener("pointerup", stop);
}

function clamp(n, min, max) {
  return Math.min(Math.max(n, min), max);
}

function emitCurrent() {
  emit("update:modelValue", currentHex.value);
}

function onSvPointerDown(e) {
  if (!svArea.value) return;
  startDrag(
    svArea.value,
    (fx, fy) => {
      sat.value = fx;
      val.value = 1 - fy;
    },
    e,
  );
}

function onHuePointerDown(e) {
  if (!hueBar.value) return;
  startDrag(
    hueBar.value,
    (fx) => {
      hue.value = fx * 360;
    },
    e,
  );
}

function onSvKeydown(e) {
  const step = e.shiftKey ? 0.1 : 0.02;
  let handled = true;
  if (e.key === "ArrowRight") sat.value = clamp(sat.value + step, 0, 1);
  else if (e.key === "ArrowLeft") sat.value = clamp(sat.value - step, 0, 1);
  else if (e.key === "ArrowUp") val.value = clamp(val.value + step, 0, 1);
  else if (e.key === "ArrowDown") val.value = clamp(val.value - step, 0, 1);
  else handled = false;
  if (handled) {
    e.preventDefault();
    emitCurrent();
    addRecent(currentHex.value);
  }
}

const hexInputValid = computed(() => normalizeHex(hexInput.value) !== null);

const previewHexColor = computed(
  () => normalizeHex(hexInput.value) || "transparent",
);

const applyHex = () => {
  const normalized = normalizeHex(hexInput.value);
  if (!normalized) {
    hexInputError.value = t("fields.colorpicker.hex_input_error");
    return;
  }
  hexInputError.value = "";
  commit(normalized);
};

const hasEyeDropper = typeof window !== "undefined" && "EyeDropper" in window;

async function pickFromScreen() {
  if (!hasEyeDropper) return;
  try {

    const result = await new window.EyeDropper().open();
    const normalized = normalizeHex(result.sRGBHex);
    if (normalized) commit(normalized);
  } catch {

  }
}

function commit(color, { close = true } = {}) {
  const normalized = normalizeHex(color) || color;
  emit("update:modelValue", normalized);
  addRecent(normalized);
  if (close) closePanel();
}

const selectColor = (color) => commit(color);

function resetTransient() {
  searchQuery.value = "";
  hexInput.value = "";
  hexInputError.value = "";
}

function closePanel() {
  isOpen.value = false;
  resetTransient();
}

const toggle = async () => {
  if (isOpen.value) {
    closePanel();
    return;
  }
  isOpen.value = true;
  syncFromHex(props.modelValue);
  hexInput.value = props.modelValue || "";
  await recalc();
};

watch(
  () => props.modelValue,
  (v) => {
    if (isOpen.value) syncFromHex(v);
  },
);

watch(activeTab, async (tab) => {
  if (tab === "custom") {
    hexInput.value = props.modelValue || currentHex.value;
    await nextTick();
    await recalc();
  }
});

const handleClickOutside = (event) => {
  if (root.value && !root.value.contains(event.target)) {
    closePanel();
  }
};

const handleKeydown = (event) => {
  if (event.key === "Escape" && isOpen.value) {
    closePanel();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleKeydown);
});
onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
  <div class="color-picker" ref="root">

    <button
      type="button"
      class="color-picker__control"
      :class="{ 'is-open': isOpen }"
      :aria-expanded="isOpen"
      aria-haspopup="dialog"
      @click.stop="toggle"
    >
      <div class="color-picker__preview-wrapper">
        <div
          class="color-picker__preview-circle"
          :style="{ backgroundColor: modelValue || 'transparent' }"
          :class="{ 'is-empty': !modelValue }"
        ></div>
        <span class="color-picker__value">
          {{ modelValue || $t("fields.colorpicker.select_color") }}
        </span>
      </div>
      <i class="fa-solid fa-palette color-picker__icon"></i>
    </button>

    <transition name="picker-fade">
      <div
        v-if="isOpen"
        class="color-picker__panel"
        :class="{ 'color-picker__panel--flip-up': flipUp }"
        role="dialog"
      >

        <div class="color-picker__tabs" role="tablist">
          <button
            type="button"
            class="color-picker__tab"
            :class="{ 'is-active': activeTab === 'palette' }"
            role="tab"
            :aria-selected="activeTab === 'palette'"
            @click.stop="activeTab = 'palette'"
          >
            <i class="fa-solid fa-swatchbook"></i>
            {{ $t("fields.colorpicker.tab_palette") }}
          </button>
          <button
            type="button"
            class="color-picker__tab"
            :class="{ 'is-active': activeTab === 'custom' }"
            role="tab"
            :aria-selected="activeTab === 'custom'"
            @click.stop="activeTab = 'custom'"
          >
            <i class="fa-solid fa-sliders"></i>
            {{ $t("fields.colorpicker.tab_custom") }}
          </button>
        </div>

        <div v-show="activeTab === 'palette'" class="color-picker__body">
          <div class="color-picker__search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
              type="text"
              v-model="searchQuery"
              :placeholder="$t('fields.colorpicker.search_placeholder')"
              class="color-picker__search"
              @click.stop
            />
          </div>

          <div
            v-if="recentColors.length && !searchQuery"
            class="color-picker__group"
          >
            <div class="color-picker__group-label">
              {{ $t("fields.colorpicker.recent") }}
            </div>
            <div class="color-picker__grid">
              <button
                v-for="color in recentColors"
                :key="`recent-${color}`"
                type="button"
                class="color-picker__swatch"
                :class="{ 'is-active': modelValue === color }"
                :style="{ backgroundColor: color }"
                :title="color"
                :aria-label="color"
                @click.stop="selectColor(color)"
              >
                <i
                  v-if="modelValue === color"
                  class="fa-solid fa-check color-picker__check"
                ></i>
              </button>
            </div>
          </div>

          <div class="color-picker__scroll">
            <div
              v-for="group in filteredGroups"
              :key="group.shadeKey"
              class="color-picker__group"
            >
              <div class="color-picker__group-label">{{ group.label }}</div>
              <div class="color-picker__grid">
                <button
                  v-for="(color, index) in group.colors"
                  :key="`${color}-${index}`"
                  type="button"
                  class="color-picker__swatch"
                  :class="{ 'is-active': modelValue === color }"
                  :style="{ backgroundColor: color }"
                  :title="color"
                  :aria-label="color"
                  @click.stop="selectColor(color)"
                >
                  <i
                    v-if="modelValue === color"
                    class="fa-solid fa-check color-picker__check"
                  ></i>
                </button>
              </div>
            </div>

            <div v-if="!hasResults" class="color-picker__empty">
              {{ $t("fields.colorpicker.no_shades_found") }}
            </div>
          </div>
        </div>

        <div v-show="activeTab === 'custom'" class="color-picker__body">

          <div
            ref="svArea"
            class="color-picker__sv"
            :style="{ backgroundColor: hueColor }"
            tabindex="0"
            role="slider"
            :aria-label="$t('fields.colorpicker.tab_custom')"
            @pointerdown.stop.prevent="onSvPointerDown"
            @keydown="onSvKeydown"
          >
            <div class="color-picker__sv-white"></div>
            <div class="color-picker__sv-black"></div>
            <div
              class="color-picker__sv-handle"
              :style="{
                left: sat * 100 + '%',
                top: (1 - val) * 100 + '%',
                backgroundColor: currentHex,
              }"
            ></div>
          </div>

          <div
            ref="hueBar"
            class="color-picker__hue"
            @pointerdown.stop.prevent="onHuePointerDown"
          >
            <div
              class="color-picker__hue-handle"
              :style="{ left: (hue / 360) * 100 + '%' }"
            ></div>
          </div>

          <div class="color-picker__custom-readout">
            <div
              class="color-picker__custom-swatch"
              :style="{ backgroundColor: currentHex }"
            ></div>
            <span class="color-picker__custom-hex">{{ currentHex }}</span>
            <button
              v-if="hasEyeDropper"
              type="button"
              class="color-picker__eyedropper"
              :title="$t('fields.colorpicker.pick_from_screen')"
              :aria-label="$t('fields.colorpicker.pick_from_screen')"
              @click.stop="pickFromScreen"
            >
              <i class="fa-solid fa-eye-dropper"></i>
            </button>
          </div>

          <div class="color-picker__hex-section">
            <div class="color-picker__hex-input-group">
              <input
                type="text"
                v-model="hexInput"
                placeholder="#RRGGBB"
                class="color-picker__hex-input"
                @keyup.enter="applyHex"
                @click.stop
              />
              <div
                class="color-picker__hex-preview"
                :style="{ backgroundColor: previewHexColor }"
                :title="hexInputValid ? hexInput : 'Invalid hex'"
              ></div>
              <button
                type="button"
                class="color-picker__hex-apply"
                :disabled="!hexInputValid"
                @click.stop="applyHex"
              >
                {{ $t("fields.colorpicker.apply") }}
              </button>
            </div>
            <div v-if="hexInputError" class="color-picker__hex-error">
              {{ hexInputError }}
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>

.color-picker {
  --cp-accent: var(--primary-color);
  --cp-surface: var(--color-bg-surface);
  --cp-border: var(--color-border);
  --cp-border-strong: var(--color-border-muted);
  --cp-text: var(--color-text-heading);
  --cp-text-muted: var(--color-text-muted);
  --cp-radius: 12px;
  --cp-shadow:
    0 12px 32px -8px var(--color-shadow-elevated),
    0 2px 8px -2px var(--color-shadow-md);

  position: relative;
  width: 100%;
  font-family: inherit;
}

.color-picker__control {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  width: 100%;
  padding: 9px 12px;
  background: var(--cp-surface);
  border: 1px solid var(--cp-border-strong);
  border-radius: 10px;
  cursor: pointer;
  color: var(--cp-text);
  font-size: 14px;
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}
.color-picker__control:hover {
  border-color: var(--cp-accent);
}
.color-picker__control.is-open {
  border-color: var(--cp-accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cp-accent) 18%, transparent);
}
.color-picker__preview-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}
.color-picker__preview-circle {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 1px solid var(--color-shadow-md);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
  flex-shrink: 0;
}
.color-picker__preview-circle.is-empty {
  background: repeating-conic-gradient(var(--color-border) 0% 25%, var(--color-bg-surface) 0% 50%) 50% /
    10px 10px;
}
.color-picker__value {
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.02em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.color-picker__icon {
  color: var(--cp-text-muted);
  flex-shrink: 0;
}

.color-picker__panel {
  position: absolute;
  z-index: 50;
  top: calc(100% + 8px);
  left: 0;
  width: 300px;
  max-width: min(300px, calc(100vw - 24px));
  background: var(--cp-surface);
  border: 1px solid var(--cp-border);
  border-radius: var(--cp-radius);
  box-shadow: var(--cp-shadow);
  overflow: hidden;
}
.color-picker__panel--flip-up {
  top: auto;
  bottom: calc(100% + 8px);
}

.color-picker__tabs {
  display: flex;
  padding: 6px;
  gap: 4px;
  border-bottom: 1px solid var(--cp-border);
}
.color-picker__tab {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: var(--cp-text-muted);
  cursor: pointer;
  transition:
    background 0.15s ease,
    color 0.15s ease;
}
.color-picker__tab:hover {
  background: color-mix(in srgb, var(--cp-accent) 8%, transparent);
  color: var(--cp-text);
}
.color-picker__tab.is-active {
  background: var(--cp-accent);
  color: #fff;
}

.color-picker__body {
  padding: 12px;
}

.color-picker__search-wrapper {
  position: relative;
  margin-bottom: 12px;
}
.color-picker__search-wrapper i {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--cp-text-muted);
  font-size: 12px;
  pointer-events: none;
}
.color-picker__search {
  width: 100%;
  padding: 8px 10px 8px 30px;
  border: 1px solid var(--cp-border-strong);
  border-radius: 8px;
  font-size: 13px;
  color: var(--cp-text);
  outline: none;
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}
.color-picker__search:focus {
  border-color: var(--cp-accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cp-accent) 15%, transparent);
}

.color-picker__scroll {
  max-height: 224px;
  overflow-y: auto;
  margin: 0 -4px;
  padding: 0 4px;
}
.color-picker__scroll::-webkit-scrollbar {
  width: 8px;
}
.color-picker__scroll::-webkit-scrollbar-thumb {
  background: var(--cp-border-strong);
  border-radius: 4px;
}
.color-picker__group + .color-picker__group {
  margin-top: 12px;
}
.color-picker__group-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--cp-text-muted);
  margin-bottom: 7px;
}
.color-picker__grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 6px;
}
.color-picker__swatch {
  position: relative;
  aspect-ratio: 1;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  padding: 0;
  box-shadow: inset 0 0 0 1px var(--color-shadow-md);
  transition:
    transform 0.1s ease,
    box-shadow 0.1s ease;
}
.color-picker__swatch:hover {
  transform: scale(1.12);
  box-shadow:
    inset 0 0 0 1px var(--color-shadow-md),
    0 2px 6px var(--color-shadow-elevated);
  z-index: 1;
}
.color-picker__swatch:focus-visible {
  outline: 2px solid var(--cp-accent);
  outline-offset: 2px;
}
.color-picker__swatch.is-active {
  box-shadow:
    inset 0 0 0 1px var(--color-shadow-md),
    0 0 0 2px var(--cp-surface),
    0 0 0 4px var(--cp-accent);
}

.color-picker__check {
  color: #fff;
  font-size: 10px;
  text-shadow: 0 0 2px rgba(0, 0, 0, 0.6);
}
.color-picker__empty {
  padding: 24px 8px;
  text-align: center;
  font-size: 13px;
  color: var(--cp-text-muted);
}

.color-picker__sv {
  position: relative;
  width: 100%;
  height: 150px;
  border-radius: 10px;
  cursor: crosshair;
  overflow: hidden;
  touch-action: none;
  outline: none;
}
.color-picker__sv:focus-visible {
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cp-accent) 40%, transparent);
}
.color-picker__sv-white,
.color-picker__sv-black {
  position: absolute;
  inset: 0;
  pointer-events: none;
}
.color-picker__sv-white {
  background: linear-gradient(to right, #fff, rgba(255, 255, 255, 0));
}
.color-picker__sv-black {
  background: linear-gradient(to top, #000, rgba(0, 0, 0, 0));
}
.color-picker__sv-handle {
  position: absolute;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow:
    0 0 0 1px rgba(0, 0, 0, 0.3),
    0 1px 3px rgba(0, 0, 0, 0.4);
  transform: translate(-50%, -50%);
  pointer-events: none;
}

.color-picker__hue {
  position: relative;
  height: 14px;
  margin-top: 14px;
  border-radius: 7px;
  cursor: pointer;
  touch-action: none;
  background: linear-gradient(
    to right,
    #ff0000 0%,
    #ffff00 17%,
    #00ff00 33%,
    #00ffff 50%,
    #0000ff 67%,
    #ff00ff 83%,
    #ff0000 100%
  );
}
.color-picker__hue-handle {
  position: absolute;
  top: 50%;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.2);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
  transform: translate(-50%, -50%);
  pointer-events: none;
}

.color-picker__custom-readout {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 14px;
}
.color-picker__custom-swatch {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  box-shadow: inset 0 0 0 1px var(--color-shadow-md);
  flex-shrink: 0;
}
.color-picker__custom-hex {
  font-family: "DM Mono", ui-monospace, monospace;
  font-size: 14px;
  letter-spacing: 0.04em;
  color: var(--cp-text);
  flex: 1;
}
.color-picker__eyedropper {
  width: 34px;
  height: 34px;
  border: 1px solid var(--cp-border-strong);
  border-radius: 8px;
  background: var(--cp-surface);
  color: var(--cp-text-muted);
  cursor: pointer;
  transition:
    border-color 0.15s ease,
    color 0.15s ease;
}
.color-picker__eyedropper:hover {
  border-color: var(--cp-accent);
  color: var(--cp-accent);
}

.color-picker__hex-section {
  margin-top: 14px;
}
.color-picker__hex-input-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.color-picker__hex-input {
  flex: 1;
  min-width: 0;
  padding: 8px 10px;
  border: 1px solid var(--cp-border-strong);
  border-radius: 8px;
  font-family: "DM Mono", ui-monospace, monospace;
  font-size: 13px;
  letter-spacing: 0.04em;
  color: var(--cp-text);
  outline: none;
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}
.color-picker__hex-input:focus {
  border-color: var(--cp-accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cp-accent) 15%, transparent);
}
.color-picker__hex-preview {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  box-shadow: inset 0 0 0 1px var(--color-shadow-md);
  flex-shrink: 0;
}
.color-picker__hex-apply {
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  background: var(--cp-accent);
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition:
    opacity 0.15s ease,
    filter 0.15s ease;
}
.color-picker__hex-apply:hover:not(:disabled) {
  filter: brightness(1.05);
}
.color-picker__hex-apply:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}
.color-picker__hex-error {
  margin-top: 6px;
  font-size: 12px;
  color: var(--danger-color);
}

.picker-fade-enter-active,
.picker-fade-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.picker-fade-enter-from,
.picker-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
