<script setup>
import {
  ref,
  reactive,
  computed,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
} from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import dayjs from "dayjs";
import "@/utils/datetime";
import { useNotifications } from "@/Composables/useNotifications";
import { useDropdownFlip } from "@/Composables/useDropdownFlip";
import AppTooltip from "./AppTooltip.vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const appSettings = usePage().props.appSettings;
const tz = () => appSettings?.timezone || "UTC";

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

  if (diffMin < 1) return t("globals.notifications.just_now");
  if (diffMin < 60)
    return t("globals.notifications.minutes_ago", { count: diffMin });

  const diffHours = Math.floor(diffMin / 60);
  if (diffHours < 24)
    return t("globals.notifications.hours_ago", { count: diffHours });

  const diffDays = Math.floor(diffHours / 24);
  return t("globals.notifications.days_ago", { count: diffDays });
};

const groupedNotifications = computed(() => {
  const buckets = { unread: [], today: [], yesterday: [], earlier: [] };
  const now = dayjs().tz(tz());

  notifications.value.forEach((n) => {
    if (!n.read_at) {
      buckets.unread.push(n);
      return;
    }

    const date = dayjs(n.created_at).tz(tz());
    if (date.isSame(now, "day")) buckets.today.push(n);
    else if (date.isSame(now.subtract(1, "day"), "day"))
      buckets.yesterday.push(n);
    else buckets.earlier.push(n);
  });

  return [
    {
      key: "unread",
      label: t("globals.notifications.group_unread"),
      items: buckets.unread,
    },
    {
      key: "today",
      label: t("globals.notifications.group_today"),
      items: buckets.today,
    },
    {
      key: "yesterday",
      label: t("globals.notifications.group_yesterday"),
      items: buckets.yesterday,
    },
    {
      key: "earlier",
      label: t("globals.notifications.group_earlier"),
      items: buckets.earlier,
    },
  ].filter((group) => group.items.length > 0);
});

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});

const openPreferences = () => {
  router.visit("/preferences?tab=notifications");
  toggle();
  hideTooltip();
};

const tooltip = reactive({
  show: false,
  text: "",
  top: 0,
  left: 0,
});

const onSettingsMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();
  tooltip.text = t("globals.notifications.open_preferences");
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.left - 10;
  tooltip.show = true;
};

const hideTooltip = () => {
  tooltip.show = false;
};
</script>
<template>
  <div
    class="topbar__actions__icons__item notification-bell"
    :class="{ 'notification-bell--open': open }"
    ref="bellRef"
    @click="toggle"
  >
    <i class="fa-solid fa-bell"></i>
    <span
      v-if="unreadCount > 0"
      class="notification-bell__badge"
      :class="{ bump: bumpBadge }"
    >
      {{ unreadCount > 99 ? "99+" : unreadCount }}
    </span>

    <transition name="fade">
      <div
        v-if="open"
        class="notification-dropdown card-shadow"
        :class="{ 'notification-dropdown--flip-up': flipUp }"
        role="dialog"
        aria-label="Notifications"
        aria-expanded="open"
      >
        <div class="notification-dropdown__header">
          <div class="header-left">
            <span class="header-title">
              {{ $t("globals.topbar.notifications") }}
            </span>
          </div>
          <div class="header-actions">
            <button
              v-if="unreadCount > 0"
              type="button"
              class="btn-mark-read"
              @click.stop="markAllRead"
            >
              {{ $t("globals.notifications.mark_all_read") }}
            </button>
            <button
              type="button"
              class="btn-settings"
              @click.stop="openPreferences"
              @mouseenter="onSettingsMouseEnter"
              @mouseleave="hideTooltip"
            >
              <i class="fa-solid fa-sliders"></i>
            </button>
          </div>
        </div>

        <div class="notification-dropdown__list">
          <div v-if="loading" class="notification-dropdown__skeleton">
            <div v-for="i in 3" :key="i" class="skeleton-item">
              <div class="skeleton-icon"></div>
              <div class="skeleton-body">
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
              </div>
            </div>
          </div>

          <div
            v-else-if="notifications.length === 0"
            class="notification-dropdown__empty"
          >
            <i class="fa-solid fa-bell-slash"></i>
            <span>{{ $t("globals.notifications.empty") }}</span>
          </div>

          <template v-else>
            <div
              v-for="group in groupedNotifications"
              :key="group.key"
              class="notification-dropdown__group"
            >
              <div class="notification-dropdown__group-label">
                {{ group.label }}
              </div>

              <component
                :is="n.data.url ? Link : 'div'"
                v-for="n in group.items"
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
                  <div
                    class="notification-item__title"
                    v-html="n.data.title"
                  ></div>
                  <div
                    class="notification-item__text"
                    v-html="n.data.body"
                  ></div>
                  <div class="notification-item__time">
                    {{ relativeTime(n.created_at) }}
                  </div>
                </div>
              </component>
            </div>
          </template>
        </div>
      </div>
    </transition>

    <AppTooltip
      :show="tooltip.show"
      :text="tooltip.text"
      :top="tooltip.top"
      :left="tooltip.left"
      placement="left"
    />
  </div>
</template>
