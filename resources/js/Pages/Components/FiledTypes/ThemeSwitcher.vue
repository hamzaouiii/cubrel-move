<script setup>
import { computed, ref, watch, onMounted, getCurrentInstance } from "vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    options: {
        type: Array,
        default: () => [
            { label: "Light", value: "light" },
            { label: "Dark", value: "dark" },
            { label: "Auto", value: "auto" },
        ],
    },
});

const emit = defineEmits(["update:modelValue"]);

const lightOption = computed(
    () => props.options[0] ?? { label: "Light", value: "light" },
);
const darkOption = computed(
    () => props.options[1] ?? { label: "Dark", value: "dark" },
);
const autoOption = computed(
    () => props.options[2] ?? { label: "Auto", value: "auto" },
);

// Detect system preference
const systemPrefersDark = ref(false);

onMounted(() => {
    systemPrefersDark.value = window.matchMedia(
        "(prefers-color-scheme: dark)",
    ).matches;

    // Listen for system preference changes
    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    const handler = (e) => {
        systemPrefersDark.value = e.matches;
    };
    mediaQuery.addEventListener("change", handler);
});

// Determine if dark mode should be active
const isDark = computed(() => {
    const currentValue = props.modelValue ?? lightOption.value.value;

    if (currentValue === autoOption.value.value) {
        return systemPrefersDark.value;
    }
    return currentValue === darkOption.value.value;
});

// Determine dial rotation (0° = sun, 90° = auto, 180° = moon)
const rotation = computed(() => {
    const currentValue = props.modelValue ?? lightOption.value.value;

    if (currentValue === lightOption.value.value) return 0;
    if (currentValue === autoOption.value.value) return 90;
    return 180;
});

// Determine active state for labels
const activeState = computed(() => {
    const currentValue = props.modelValue ?? lightOption.value.value;

    if (currentValue === lightOption.value.value) return "light";
    if (currentValue === darkOption.value.value) return "dark";
    return "auto";
});

const activeStateLabel = computed(() =>
    t(`globals.theme_switcher.${activeState.value}`),
);

const ariaLabel = computed(
    () =>
        `${t("globals.theme_switcher.current_theme", { state: activeStateLabel.value })} ${t("globals.theme_switcher.cycle_hint")}`,
);

const isHovering = ref(false);
const isTransitioning = ref(false);

function toggle() {
    if (isTransitioning.value) return;

    const currentValue = props.modelValue ?? lightOption.value.value;
    const values = [
        lightOption.value.value,
        autoOption.value.value,
        darkOption.value.value,
    ];
    let currentIndex = values.indexOf(currentValue);
    if (currentIndex === -1) currentIndex = 0;

    const nextIndex = (currentIndex + 1) % values.length;
    const newValue = values[nextIndex];

    isTransitioning.value = true;
    emit("update:modelValue", newValue);

    setTimeout(() => {
        isTransitioning.value = false;
    }, 600);
}

// Keyboard support
function handleKeydown(event) {
    if (event.key === " " || event.key === "Enter") {
        event.preventDefault();
        toggle();
    }
}
</script>

<template>
    <button
        type="button"
        class="theme-switcher"
        :class="[
            {
                'theme-switcher--dark': isDark,
                'theme-switcher--auto': activeState === 'auto',
                'theme-switcher--transitioning': isTransitioning,
            },
        ]"
        role="switch"
        :aria-checked="isDark"
        :aria-label="ariaLabel"
        @click="toggle"
        @keydown="handleKeydown"
        @mouseenter="isHovering = true"
        @mouseleave="isHovering = false"
    >
        <!-- Main container -->
        <div class="theme-switcher__container">
            <!-- Outer ring with rotating glow -->
            <div class="theme-switcher__ring">
                <div class="theme-switcher__ring-glow"></div>
            </div>

            <!-- Background scene -->
            <div class="theme-switcher__scene">
                <!-- Sky gradient -->
                <div class="theme-switcher__sky"></div>

                <!-- Clouds -->
                <div
                    class="theme-switcher__cloud theme-switcher__cloud--1"
                ></div>
                <div
                    class="theme-switcher__cloud theme-switcher__cloud--2"
                ></div>
                <div
                    class="theme-switcher__cloud theme-switcher__cloud--3"
                ></div>

                <!-- Stars -->
                <div class="theme-switcher__stars">
                    <span
                        v-for="i in 12"
                        :key="i"
                        class="theme-switcher__star"
                        :style="{ '--i': i }"
                    ></span>
                </div>

                <!-- Sun/Moon dial -->
                <div
                    class="theme-switcher__dial"
                    :style="{ transform: `rotate(${rotation}deg)` }"
                >
                    <!-- Sun -->
                    <div class="theme-switcher__sun">
                        <div class="theme-switcher__sun-core"></div>
                        <div
                            v-for="i in 8"
                            :key="`sun-ray-${i}`"
                            class="theme-switcher__sun-ray"
                            :style="{ '--angle': `${i * 45}deg` }"
                        ></div>
                    </div>

                    <!-- Moon -->
                    <div class="theme-switcher__moon">
                        <div class="theme-switcher__moon-body"></div>
                        <div
                            class="theme-switcher__moon-crater theme-switcher__moon-crater--1"
                        ></div>
                        <div
                            class="theme-switcher__moon-crater theme-switcher__moon-crater--2"
                        ></div>
                        <div
                            class="theme-switcher__moon-crater theme-switcher__moon-crater--3"
                        ></div>
                        <div class="theme-switcher__moon-glow"></div>
                    </div>

                    <!-- Auto indicator (half sun/half moon) -->
                    <div class="theme-switcher__auto-indicator">
                        <div class="theme-switcher__auto-sun"></div>
                        <div class="theme-switcher__auto-moon"></div>
                        <div class="theme-switcher__auto-divider"></div>
                        <span class="theme-switcher__auto-icon">⚡</span>
                    </div>
                </div>

                <!-- Horizon line -->
                <div class="theme-switcher__horizon"></div>

                <!-- Ground -->
                <div class="theme-switcher__ground">
                    <div class="theme-switcher__grass">
                        <span
                            v-for="i in 15"
                            :key="`blade-${i}`"
                            class="theme-switcher__grass-blade"
                            :style="{ '--blade': i }"
                        ></span>
                    </div>
                </div>

                <!-- Tiny trees on horizon -->
                <div class="theme-switcher__trees">
                    <div
                        v-for="i in 7"
                        :key="`tree-${i}`"
                        class="theme-switcher__tree"
                        :style="{ '--tree': i }"
                    >
                        <div class="theme-switcher__tree-trunk"></div>
                        <div class="theme-switcher__tree-canopy"></div>
                    </div>
                </div>
            </div>

            <!-- Status indicators -->
            <div class="theme-switcher__indicators">
                <span
                    class="theme-switcher__indicator theme-switcher__indicator--light"
                    :class="{
                        'theme-switcher__indicator--active':
                            activeState === 'light',
                    }"
                >
                    ☀️
                </span>
                <span
                    class="theme-switcher__indicator theme-switcher__indicator--auto"
                    :class="{
                        'theme-switcher__indicator--active':
                            activeState === 'auto',
                    }"
                >
                    ⚡
                </span>
                <span
                    class="theme-switcher__indicator theme-switcher__indicator--dark"
                    :class="{
                        'theme-switcher__indicator--active':
                            activeState === 'dark',
                    }"
                >
                    🌙
                </span>
            </div>

            <!-- Current state label -->
            <span class="theme-switcher__label">
                {{
                    activeState === "light"
                        ? "☀️"
                        : activeState === "dark"
                          ? "🌙"
                          : "⚡"
                }}
                {{ activeStateLabel }}
            </span>

            <!-- Auto mode hint -->
            <span v-if="activeState === 'auto'" class="theme-switcher__hint">
                {{ systemPrefersDark ? "🌙" : "☀️" }}
                {{
                    systemPrefersDark
                        ? t("globals.theme_switcher.system_prefers_dark")
                        : t("globals.theme_switcher.system_prefers_light")
                }}
            </span>
        </div>
    </button>
</template>

<style scoped></style>
