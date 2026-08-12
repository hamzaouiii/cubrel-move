import { watch, onMounted, onUnmounted } from "vue";

export function useThemeMode(themeEnabled, theme) {
  const media = window.matchMedia
    ? window.matchMedia("(prefers-color-scheme: dark)")
    : null;

  const resolve = () => {
    if (!themeEnabled.value) return "light";
    if (theme.value === "dark") return "dark";
    if (theme.value === "auto" && media?.matches) return "dark";
    return "light";
  };

  const apply = () => {
    document.documentElement.setAttribute("data-theme", resolve());
  };

  watch([themeEnabled, theme], apply, { immediate: true });

  onMounted(() => {
    media?.addEventListener("change", apply);
  });

  onUnmounted(() => {
    media?.removeEventListener("change", apply);
  });
}
