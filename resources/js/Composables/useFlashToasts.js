import { watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";

export function useFlashToasts() {
  const { success, error, warning, clearAllAlerts } = useAlerts();
  const page = usePage();

  watch(
    () => page.props.flash,
    (flash) => {
      const message = flash?.success || flash?.error || flash?.warning;
      if (!message) return;
      clearAllAlerts();
      if (flash.success) success(flash.success);
      if (flash.error) error(flash.error);
      if (flash.warning) warning(flash.warning);
    },
    { immediate: true },
  );
}
