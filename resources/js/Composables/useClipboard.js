import { ref, getCurrentInstance } from "vue";
import { useAlerts } from "@/Composables/useAlerts";

export function useClipboard() {
  const copied = ref(false);
  let timeout = null;
  const { proxy } = getCurrentInstance();
  const t = proxy.$t;

  const { info, error } = useAlerts();

  const copy = async (value) => {
    try {
      await navigator.clipboard.writeText(String(value));

      copied.value = true;
      info(t("globals.copied"), { timeout: 1000 });

      clearTimeout(timeout);
      timeout = setTimeout(() => {
        copied.value = false;
      }, 1500);
    } catch (err) {
      console.error(err);
      error(t("globals.copy_failed"), { timeout: 1000 });
    }
  };

  return {
    copied,
    copy,
  };
}
