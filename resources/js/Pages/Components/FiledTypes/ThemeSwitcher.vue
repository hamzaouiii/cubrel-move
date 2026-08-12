<script setup>
import { computed, ref, watch, onMounted } from "vue";

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

const isDark = computed(
    () =>
        (props.modelValue ?? lightOption.value.value) ===
        darkOption.value.value,
);

const rotation = ref(0);
const isHovering = ref(false);

watch(isDark, (newVal) => {
    rotation.value = newVal ? 180 : 0;
});

onMounted(() => {
    rotation.value = isDark.value ? 180 : 0;
});

function toggle() {
    const newValue = isDark.value
        ? lightOption.value.value
        : darkOption.value.value;
    emit("update:modelValue", newValue);
}
</script>

<template>
    <button
        type="button"
        class="theme-switcher"
        :class="{ 'theme-switcher--dark': isDark }"
        role="switch"
        :aria-checked="isDark"
        :aria-label="`Switch to ${isDark ? lightOption.label : darkOption.label} theme`"
        @click="toggle"
        @mouseenter="isHovering = true"
        @mouseleave="isHovering = false"
    >

        <div class="theme-switcher__container">

            <div class="theme-switcher__ring">
                <div class="theme-switcher__ring-glow"></div>
            </div>

            <div class="theme-switcher__scene">

                <div class="theme-switcher__sky"></div>

                <div
                    class="theme-switcher__cloud theme-switcher__cloud--1"
                ></div>
                <div
                    class="theme-switcher__cloud theme-switcher__cloud--2"
                ></div>
                <div
                    class="theme-switcher__cloud theme-switcher__cloud--3"
                ></div>

                <div class="theme-switcher__stars">
                    <span
                        v-for="i in 12"
                        :key="i"
                        class="theme-switcher__star"
                        :style="{ '--i': i }"
                    ></span>
                </div>

                <div
                    class="theme-switcher__dial"
                    :style="{ transform: `rotate(${rotation}deg)` }"
                >

                    <div class="theme-switcher__sun">
                        <div class="theme-switcher__sun-core"></div>
                        <div
                            v-for="i in 8"
                            :key="`sun-ray-${i}`"
                            class="theme-switcher__sun-ray"
                            :style="{ '--angle': `${i * 45}deg` }"
                        ></div>
                    </div>

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
                </div>

                <div class="theme-switcher__horizon"></div>

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

            <span class="theme-switcher__label">
                {{ isDark ? "🌙" : "☀️" }}
            </span>
        </div>
    </button>
</template>

<style lang="scss" scoped>
.theme-switcher {
    --ts-size: 120px;
    --ts-transition: 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);

    position: relative;
    width: var(--ts-size);
    height: var(--ts-size);
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    outline: none;

    &:focus-visible {
        .theme-switcher__ring {
            box-shadow:
                0 0 0 3px var(--primary-color, #3b8bff),
                0 0 0 6px rgba(59, 139, 255, 0.2);
        }
    }

    &__container {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        transition: transform var(--ts-transition);
    }

    &__ring {
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.15);
        transition: all var(--ts-transition);

        &-glow {
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                transparent 0%,
                rgba(255, 200, 50, 0.1) 25%,
                transparent 50%,
                rgba(100, 150, 255, 0.1) 75%,
                transparent 100%
            );
            animation: ring-rotate 10s linear infinite;
        }
    }

    &--dark &__ring {
        border-color: rgba(255, 255, 255, 0.05);
        box-shadow: 0 0 30px rgba(100, 150, 255, 0.1);
    }

    &__scene {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.15),
            inset 0 0 60px rgba(255, 255, 255, 0.05);
    }

    &__sky {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            180deg,
            #4facfe 0%,
            #a8d8ff 40%,
            #f0e6d0 70%,
            #f5d6b8 85%,
            #e8c9a0 100%
        );
        transition: background var(--ts-transition);
    }

    &--dark &__sky {
        background: linear-gradient(
            180deg,
            #0a0e27 0%,
            #1a1f3a 30%,
            #2a2f4a 60%,
            #1a1f3a 80%,
            #0a0e27 100%
        );
    }

    &__cloud {
        position: absolute;
        background: rgba(255, 255, 255, 0.6);
        border-radius: 50px;
        filter: blur(1px);
        transition: all var(--ts-transition);

        &--1 {
            width: 35%;
            height: 12%;
            top: 15%;
            left: 10%;
            animation: cloud-float 20s ease-in-out infinite alternate;
        }
        &--2 {
            width: 25%;
            height: 8%;
            top: 25%;
            right: 15%;
            animation: cloud-float 25s ease-in-out infinite alternate-reverse;
        }
        &--3 {
            width: 20%;
            height: 6%;
            top: 35%;
            left: 40%;
            animation: cloud-float 18s ease-in-out infinite alternate;
        }
    }

    &--dark &__cloud {
        opacity: 0.05;
        transform: scale(0.5);
    }

    &__stars {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.8s ease;
    }

    &--dark &__stars {
        opacity: 1;
    }

    &__star {
        position: absolute;
        width: 2px;
        height: 2px;
        background: white;
        border-radius: 50%;
        animation: twinkle 2s ease-in-out infinite;
        animation-delay: calc(var(--i) * 0.2s);

        &:nth-child(1) {
            top: 8%;
            left: 15%;
        }
        &:nth-child(2) {
            top: 12%;
            left: 70%;
        }
        &:nth-child(3) {
            top: 5%;
            left: 45%;
        }
        &:nth-child(4) {
            top: 20%;
            left: 85%;
        }
        &:nth-child(5) {
            top: 25%;
            left: 5%;
        }
        &:nth-child(6) {
            top: 35%;
            left: 75%;
        }
        &:nth-child(7) {
            top: 15%;
            left: 30%;
        }
        &:nth-child(8) {
            top: 30%;
            left: 55%;
        }
        &:nth-child(9) {
            top: 45%;
            left: 15%;
        }
        &:nth-child(10) {
            top: 40%;
            left: 90%;
        }
        &:nth-child(11) {
            top: 50%;
            left: 35%;
        }
        &:nth-child(12) {
            top: 55%;
            left: 70%;
        }

        &::after {
            content: "";
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: radial-gradient(
                circle,
                rgba(255, 255, 255, 0.3),
                transparent
            );
        }
    }

    &__dial {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    &__sun {
        position: absolute;
        top: 15%;
        left: 50%;
        transform: translateX(-50%);
        width: 25%;
        height: 25%;
        transition: all var(--ts-transition);

        &-core {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(circle, #fdb813, #f59e0b);
            box-shadow: 0 0 40px rgba(253, 184, 19, 0.6);
            animation: pulse 3s ease-in-out infinite;
        }

        &-ray {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 3px;
            height: 25px;
            background: linear-gradient(to top, #fdb813, transparent);
            transform-origin: bottom center;
            transform: translate(-50%, -100%) rotate(var(--angle));
            opacity: 0.5;
            animation: ray-glow 2s ease-in-out infinite;
            animation-delay: calc(var(--angle) * 0.05s);
        }
    }

    &--dark &__sun {
        opacity: 0;
        transform: scale(0.3) rotate(180deg);
    }

    &__moon {
        position: absolute;
        top: 15%;
        left: 50%;
        transform: translateX(-50%) scale(0.3);
        width: 25%;
        height: 25%;
        opacity: 0;
        transition: all var(--ts-transition);

        &-body {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #e8e8f0, #b0b8c8);
            box-shadow: 0 0 30px rgba(200, 210, 255, 0.2);
        }

        &-crater {
            position: absolute;
            border-radius: 50%;
            background: rgba(160, 170, 190, 0.3);

            &--1 {
                width: 30%;
                height: 30%;
                top: 20%;
                left: 20%;
            }
            &--2 {
                width: 20%;
                height: 20%;
                top: 50%;
                right: 15%;
            }
            &--3 {
                width: 15%;
                height: 15%;
                bottom: 20%;
                left: 30%;
            }
        }

        &-glow {
            position: absolute;
            inset: -20px;
            border-radius: 50%;
            background: radial-gradient(
                circle,
                rgba(200, 210, 255, 0.1),
                transparent
            );
            animation: moon-glow 4s ease-in-out infinite;
        }
    }

    &--dark &__moon {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }

    &__horizon {
        position: absolute;
        bottom: 25%;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(
            to right,
            transparent,
            rgba(255, 255, 255, 0.3),
            transparent
        );
        transition: all var(--ts-transition);
    }

    &--dark &__horizon {
        opacity: 0.1;
    }

    &__ground {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 25%;
        background: linear-gradient(180deg, #8cb87a, #5a8f4c);
        border-radius: 0 0 50% 50% / 0 0 100% 100%;
        transition: all var(--ts-transition);
    }

    &--dark &__ground {
        background: linear-gradient(180deg, #1a2a1a, #0d1a0d);
    }

    &__grass {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100%;
        overflow: hidden;
    }

    &__grass-blade {
        position: absolute;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to top, #4caf50, #8bc34a);
        transform-origin: bottom center;
        animation: grass-sway 3s ease-in-out infinite;
        animation-delay: calc(var(--blade) * 0.2s);

        &:nth-child(1) {
            left: 5%;
            height: 40%;
        }
        &:nth-child(2) {
            left: 12%;
            height: 60%;
        }
        &:nth-child(3) {
            left: 18%;
            height: 35%;
        }
        &:nth-child(4) {
            left: 25%;
            height: 55%;
        }
        &:nth-child(5) {
            left: 32%;
            height: 45%;
        }
        &:nth-child(6) {
            left: 38%;
            height: 65%;
        }
        &:nth-child(7) {
            left: 45%;
            height: 30%;
        }
        &:nth-child(8) {
            left: 52%;
            height: 50%;
        }
        &:nth-child(9) {
            left: 58%;
            height: 40%;
        }
        &:nth-child(10) {
            left: 65%;
            height: 60%;
        }
        &:nth-child(11) {
            left: 72%;
            height: 35%;
        }
        &:nth-child(12) {
            left: 78%;
            height: 55%;
        }
        &:nth-child(13) {
            left: 85%;
            height: 45%;
        }
        &:nth-child(14) {
            left: 92%;
            height: 50%;
        }
        &:nth-child(15) {
            left: 98%;
            height: 30%;
        }
    }

    &--dark &__grass-blade {
        background: linear-gradient(to top, #1a3a1a, #2a5a2a);
        opacity: 0.5;
    }

    &__trees {
        position: absolute;
        bottom: 25%;
        left: 0;
        right: 0;
        height: 30%;
    }

    &__tree {
        position: absolute;
        bottom: 0;
        transition: all var(--ts-transition);

        &:nth-child(1) {
            left: 2%;
            height: 20%;
        }
        &:nth-child(2) {
            left: 12%;
            height: 35%;
        }
        &:nth-child(3) {
            left: 22%;
            height: 15%;
        }
        &:nth-child(4) {
            left: 75%;
            height: 30%;
        }
        &:nth-child(5) {
            left: 85%;
            height: 40%;
        }
        &:nth-child(6) {
            left: 92%;
            height: 25%;
        }
        &:nth-child(7) {
            left: 45%;
            height: 20%;
        }

        &-trunk {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 40%;
            background: #5d4037;
            border-radius: 2px;
        }

        &-canopy {
            position: absolute;
            bottom: 30%;
            left: 50%;
            transform: translateX(-50%);
            width: 200%;
            aspect-ratio: 1;
            background: radial-gradient(circle at 30% 30%, #66bb6a, #388e3c);
            border-radius: 50%;
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.2);
        }
    }

    &--dark &__tree-canopy {
        background: radial-gradient(circle at 30% 30%, #1a3a1a, #0d1a0d);
        box-shadow: none;
    }

    &__label {
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 20px;
        opacity: 0.6;
        transition: all var(--ts-transition);
    }

    &--dark &__label {
        opacity: 0.8;
    }

    &:hover {
        .theme-switcher__container {
            transform: scale(1.05);
        }
    }

    &:active {
        .theme-switcher__container {
            transform: scale(0.95);
        }
    }
}

@keyframes ring-rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes cloud-float {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(30px);
    }
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 1;
    }
}

@keyframes pulse {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes ray-glow {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.8;
    }
}

@keyframes grass-sway {
    0%,
    100% {
        transform: rotate(-5deg);
    }
    50% {
        transform: rotate(5deg);
    }
}

@keyframes moon-glow {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.2);
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .theme-switcher * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
