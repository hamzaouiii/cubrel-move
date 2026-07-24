import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

// live updates arrive over the websocket this poll is
// just a fallback for dropped connections
const POLL_MS = 5 * 60 * 1000;

const unreadCount = ref(0);
const notifications = ref([]);
const loading = ref(false);
let subscribers = 0;
let timer = null;

async function fetchUnreadCount() {
  try {
    const { data } = await axios.get("/notifications/unread-count");
    unreadCount.value = data.count;
  } catch {
    // do nothing - fail silently
  }
}

async function fetchList() {
  loading.value = true;
  try {
    const { data } = await axios.get("/notifications");
    notifications.value = data.data;
  } finally {
    loading.value = false;
  }
}

async function markRead(id) {
  const item = notifications.value.find((n) => n.id === id);
  if (item && !item.read_at) {
    item.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  }
  await axios.post(`/notifications/${id}/read`);
}

async function markAllRead() {
  notifications.value.forEach((n) => {
    if (!n.read_at) n.read_at = new Date().toISOString();
  });
  unreadCount.value = 0;
  await axios.post("/notifications/read-all");
}

// called from the Echo notification listener wired in AppLayout.vue
function applyLiveNotification(payload) {
  unreadCount.value += 1;
  notifications.value = [
    {
      id: payload.id,
      data: payload,
      read_at: null,
      created_at: new Date().toISOString(),
    },
    ...notifications.value,
  ].slice(0, 20);
}

export function useNotifications() {
  onMounted(() => {
    subscribers += 1;
    if (subscribers === 1) {
      fetchUnreadCount();
      timer = setInterval(() => {
        if (document.visibilityState === "visible") {
          fetchUnreadCount();
        }
      }, POLL_MS);
    }
  });

  onUnmounted(() => {
    subscribers -= 1;
    if (subscribers === 0 && timer) {
      clearInterval(timer);
      timer = null;
    }
  });

  return {
    unreadCount,
    notifications,
    loading,
    fetchUnreadCount,
    fetchList,
    markRead,
    markAllRead,
    applyLiveNotification,
  };
}
