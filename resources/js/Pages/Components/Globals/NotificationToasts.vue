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
  <div class="notification-toasts">
    <transition-group name="toast-slide">
      <component
        :is="t.notification.url ? Link : 'div'"
        v-for="t in toasts"
        :key="t.id"
        :href="t.notification.url"
        class="notification-toasts__item"
        @click="onClick(t)"
        @mouseenter="onMouseEnter(t)"
        @mouseleave="onMouseLeave(t)"
      >
        <div class="notification-toasts__item__icon">
          <i :class="t.notification.icon || 'fa-solid fa-bell'"></i>
        </div>
        <div class="notification-toasts__item__body">
          <div class="notification-toasts__item__title" v-html="t.notification.title"></div>
          <div class="notification-toasts__item__text" v-html="t.notification.body"></div>
        </div>
        <div
          class="notification-toasts__item__close"
          @click.stop.prevent="onClick(t)"
        >
          <i class="fa-solid fa-times"></i>
        </div>
      </component>
    </transition-group>
  </div>
</template>
