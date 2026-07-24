import { ref } from "vue";

const toasts = ref([]);
let nextId = 1;

const TIMEOUT_MS = 6000;

function pushToast(notification, options = {}) {
  const id = nextId++;
  toasts.value.push({ id, notification });

  if (!options.persist) {
    setTimeout(() => removeToast(id), TIMEOUT_MS);
  }
}

function removeToast(id) {
  toasts.value = toasts.value.filter((t) => t.id !== id);
}

export function useLiveToasts() {
  return { toasts, pushToast, removeToast };
}
