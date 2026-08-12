<script setup>
import { onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";
import { useLiveToasts } from "@/Composables/useLiveToasts";
import { useNotifications } from "@/Composables/useNotifications";

const { toasts, removeToast } = useLiveToasts();
const { markRead } = useNotifications();

const HOVER_READ_MS = 1800;
const hoverTimers = {};

function onClick(t) {
    markRead(t.notification.id);
    removeToast(t.id);
}

function onMouseEnter(t) {
    if (hoverTimers[t.id]) return;
    hoverTimers[t.id] = setTimeout(() => {
        markRead(t.notification.id);
        delete hoverTimers[t.id];
    }, HOVER_READ_MS);
}

function onMouseLeave(t) {
    if (hoverTimers[t.id]) {
        clearTimeout(hoverTimers[t.id]);
        delete hoverTimers[t.id];
    }
}

onBeforeUnmount(() => {
    Object.values(hoverTimers).forEach(clearTimeout);
});
</script>

<template>
    <div class="notifications-toasts">
        <TransitionGroup name="toast">
            <component
                :is="t.notification.url ? Link : 'div'"
                v-for="t in toasts"
                :key="t.id"
                :href="t.notification.url"
                class="toast"
                @click="onClick(t)"
                @mouseenter="onMouseEnter(t)"
                @mouseleave="onMouseLeave(t)"
            >
                <div class="toast__icon">
                    <i :class="t.notification.icon || 'fa-regular fa-bell'"></i>
                </div>

                <div class="toast__content">
                    <div
                        class="toast__title"
                        v-html="t.notification.title"
                    ></div>
                    <div class="toast__body" v-html="t.notification.body"></div>
                </div>

                <button
                    class="toast__dismiss"
                    @click.stop.prevent="onClick(t)"
                    aria-label="Dismiss notification"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <!-- Progress indicator for hover-read -->
                <div class="toast__progress">
                    <div
                        class="toast__progress__bar"
                        :style="{ animationDuration: HOVER_READ_MS + 'ms' }"
                    ></div>
                </div>
            </component>
        </TransitionGroup>
    </div>
</template>

<style lang="scss" scoped>
.notifications-toasts {
    position: fixed;
    bottom: 24px;
    left: 24px;
    max-width: 380px;
    width: 100%;
    display: flex;
    flex-direction: column-reverse;
    gap: 12px;
    z-index: 100;
    pointer-events: none;

    @media (max-width: 640px) {
        left: 16px;
        right: 16px;
        max-width: none;
        bottom: 16px;
    }
}

.toast {
    pointer-events: auto;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px 14px 14px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow:
        0 4px 16px rgba(0, 0, 0, 0.08),
        0 1px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
    cursor: default;

    &:hover {
        box-shadow:
            0 8px 24px rgba(0, 0, 0, 0.12),
            0 2px 8px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
    }

    // Left accent - subtle gradient based on notification type
    &::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 3px;
        border-radius: 0 2px 2px 0;
        background: linear-gradient(180deg, #0d6efd, #6ea8fe);
        opacity: 0.6;
    }

    // Link styling when wrapped in Link component
    &[href] {
        cursor: pointer;

        &:hover {
            .toast__title {
                color: #0d6efd;
            }
        }
    }

    &__icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(
            135deg,
            rgba(13, 110, 253, 0.08),
            rgba(13, 110, 253, 0.04)
        );
        color: #0d6efd;
        font-size: 0.875rem;
        margin-top: 1px;

        i {
            font-size: 0.875rem;
        }
    }

    &__content {
        flex: 1;
        min-width: 0;
    }

    &__title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1a1a1a;
        line-height: 1.4;
        margin-bottom: 2px;
        transition: color 0.2s ease;

        :deep(a) {
            color: inherit;
            text-decoration: none;
        }
    }

    &__body {
        font-size: 0.8125rem;
        color: #6b7280;
        line-height: 1.5;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;

        :deep(a) {
            color: #0d6efd;
            text-decoration: none;

            &:hover {
                text-decoration: underline;
            }
        }
    }

    &__dismiss {
        flex-shrink: 0;
        background: none;
        border: none;
        padding: 4px;
        margin: -4px -4px -4px 0;
        cursor: pointer;
        color: #9ca3af;
        border-radius: 6px;
        transition: all 0.15s ease;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;

        i {
            font-size: 1rem;
            line-height: 1;
        }

        &:hover {
            color: #4b5563;
            background: rgba(0, 0, 0, 0.04);
            transform: scale(1.05);
        }

        &:active {
            transform: scale(0.95);
        }
    }

    // Progress bar for hover-read indicator
    &__progress {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(0, 0, 0, 0.04);
        overflow: hidden;

        &__bar {
            height: 100%;
            background: linear-gradient(90deg, #0d6efd, #6ea8fe);
            transform-origin: left;
            animation: toast-progress linear forwards;
            animation-play-state: paused;
        }
    }

    &:hover .toast__progress__bar {
        animation-play-state: running;
    }

    // Different icon colors based on notification type (can be extended)
    &--info &__icon {
        background: linear-gradient(
            135deg,
            rgba(13, 110, 253, 0.08),
            rgba(13, 110, 253, 0.04)
        );
        color: #0d6efd;
    }

    &--success &__icon {
        background: linear-gradient(
            135deg,
            rgba(25, 135, 84, 0.08),
            rgba(25, 135, 84, 0.04)
        );
        color: #198754;
    }

    &--warning &__icon {
        background: linear-gradient(
            135deg,
            rgba(255, 193, 7, 0.08),
            rgba(255, 193, 7, 0.04)
        );
        color: #d39e00;
    }

    &--error &__icon {
        background: linear-gradient(
            135deg,
            rgba(220, 53, 69, 0.08),
            rgba(220, 53, 69, 0.04)
        );
        color: #dc3545;
    }
}

// Animations
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.96);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(-20px) scale(0.96);
}

@keyframes toast-progress {
    from {
        transform: scaleX(1);
    }
    to {
        transform: scaleX(0);
    }
}

// Responsive
@media (max-width: 640px) {
    .notifications-toasts {
        left: 16px;
        right: 16px;
        bottom: 16px;
        max-width: none;
    }

    .toast {
        padding: 12px 14px;
        border-radius: 10px;

        &__icon {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        &__title {
            font-size: 0.8125rem;
        }

        &__body {
            font-size: 0.75rem;
            -webkit-line-clamp: 3;
        }
    }
}
</style>
