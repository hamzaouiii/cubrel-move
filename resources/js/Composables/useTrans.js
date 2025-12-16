import { usePage } from "@inertiajs/vue3";

export function useTrans() {
  const page = usePage();
  const translations = page?.props?.translations || {};

  const t = (key, params = {}, fallback = "") => {
    if (!key) return fallback;

    const parts = key.split(".");
    let value = translations;

    for (const part of parts) {
      if (value && Object.prototype.hasOwnProperty.call(value, part)) {
        value = value[part];
      } else {
        return fallback || key;
      }
    }

    if (params && typeof params === "object") {
      for (const [k, v] of Object.entries(params)) {
        const replacement = typeof v === "object" && "value" in v ? v.value : v;
        value = value.replaceAll(`:${k}`, String(replacement));
      }
    }

    return value;
  };

  return { t };
}
