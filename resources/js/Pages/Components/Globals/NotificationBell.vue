<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";
import { useNotifications } from "@/Composables/useNotifications";
import { useDropdownFlip } from "@/Composables/useDropdownFlip";

const {
  unreadCount,
  notifications,
  loading,
  fetchList,
  markRead,
  markAllRead,
} = useNotifications();

const open = ref(false);
const bellRef = ref(null);
const { flipUp, recalc } = useDropdownFlip(bellRef, { menuHeight: 400 });

const toggle = async () => {
  open.value = !open.value;
  if (open.value) {
    await recalc();
    fetchList();
  }
};

const handleClickOutside = (event) => {
  if (bellRef.value && !bellRef.value.contains(event.target)) {
    open.value = false;
  }
};

const onItemClick = (notification) => {
  if (!notification.read_at) {
    markRead(notification.id);
  }
  open.value = false;
};

const relativeTime = (value) => {
  const diffMs = Date.now() - new Date(value).getTime();
  const diffMin = Math.floor(diffMs / 60000);

  if (diffMin < 1) return "just now";
  if (diffMin < 60) return `${diffMin}m ago`;

  const diffHours = Math.floor(diffMin / 60);
  if (diffHours < 24) return `${diffHours}h ago`;

  const diffDays = Math.floor(diffHours / 24);
  return `${diffDays}d ago`;
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <div
    class="topbar__actions__icons__item notification-bell"
    :class="{ 'notification-bell--open': open }"
    ref="bellRef"
    @click="toggle"
  >
    <i class="fa-solid fa-bell"></i>
    <span v-if="unreadCount > 0" class="notification-bell__badge">
      {{ unreadCount > 99 ? "99+" : unreadCount }}
    </span>

    <transition name="fade">
      <div
        v-if="open"
        class="notification-dropdown card-shadow"
        :class="{ 'notification-dropdown--flip-up': flipUp }"
      >
        <div class="notification-dropdown__header">
          <span>{{ $t("globals.topbar.notifications") }}</span>
          <button
            v-if="unreadCount > 0"
            type="button"
            @click.stop="markAllRead"
          >
            {{ $t("globals.notifications.mark_all_read") }}
          </button>
        </div>

        <div class="notification-dropdown__list">
          <div v-if="loading" class="notification-dropdown__empty">
            <i class="fa-solid fa-spinner fa-spin"></i>
          </div>
          <div
            v-else-if="notifications.length === 0"
            class="notification-dropdown__empty"
          >
            {{ $t("globals.notifications.empty") }}
          </div>
          <template v-else>
            <component
              :is="n.data.url ? Link : 'div'"
              v-for="n in notifications"
              :key="n.id"
              :href="n.data.url"
              class="notification-item"
              :class="{ 'notification-item--unread': !n.read_at }"
              @click="onItemClick(n)"
            >
              <div class="notification-item__icon">
                <i :class="n.data.icon || 'fa-solid fa-bell'"></i>
              </div>
              <div class="notification-item__body">
                <div class="notification-item__title">{{ n.data.title }}</div>
                <div class="notification-item__text">{{ n.data.body }}</div>
                <div class="notification-item__time">
                  {{ relativeTime(n.created_at) }}
                </div>
              </div>
            </component>
          </template>
        </div>
      </div>
    </transition>
  </div>
</template>
