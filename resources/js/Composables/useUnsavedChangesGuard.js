import { ref, onUnmounted, getCurrentInstance } from "vue";
import { router } from "@inertiajs/vue3";
import { useConfirm } from "./useConfirm";
export function useUnsavedChangesGuard(options = {}) {
  const { getIsDirty = () => false, excludeUrls = [], skipUrls = [] } = options;

  const isActive = ref(true);
  let navigationGuardCleanup = null;

  // Get confirm function from global composable
  const instance = getCurrentInstance();
  const { confirm } = useConfirm();

  let confirmFunc = confirm;

  const shouldBlockNavigation = (visit) => {
    if (!isActive.value) return false;
    if (!getIsDirty()) return false;

    const url = visit?.url || "";
    const urlString = typeof url === "string" ? url : String(url);

    // Check exclude URLs (always allowed)
    for (const pattern of excludeUrls) {
      if (typeof pattern === "string" && urlString.includes(pattern)) {
        return false;
      }
      if (pattern instanceof RegExp && pattern.test(urlString)) {
        return false;
      }
    }

    // Check skip URLs (save actions)
    for (const pattern of skipUrls) {
      if (typeof pattern === "string" && urlString.includes(pattern)) {
        return false;
      }
      if (pattern instanceof RegExp && pattern.test(urlString)) {
        return false;
      }
    }

    return true;
  };

  const handleNavigationGuard = async (event) => {
    try {
      const visit = event.detail?.visit;

      if (!shouldBlockNavigation(visit)) {
        return; // Allow navigation
      }

      // Prevent navigation
      event.preventDefault();

      // Get translation function if available
      const t = instance?.proxy?.$t || ((key) => key);

      // Show confirmation dialog
      let confirmed = false;

      if (confirmFunc) {
        // Use your custom confirm dialog
        confirmed = await confirmFunc({
          title: t("globals.unsaved_changes_title"),
          message: t("globals.unsaved_changes_message"),
          confirmText: t("globals.unsaved_changes_leave"),
          cancelText: t("globals.unsaved_changes_stay"),
          danger: true,
        });
      } else {
        // Fallback to browser confirm
        confirmed = window.confirm(t("globals.unsaved_changes_message"));
      }

      if (confirmed && visit) {
        // User confirmed - allow navigation
        isActive.value = false;

        // Perform the navigation
        router.visit(visit.url, {
          method: visit.method,
          data: visit.data,
          preserveScroll: visit.preserveScroll,
          preserveState: visit.preserveState,
          replace: visit.replace,
          only: visit.only,
          headers: visit.headers,
        });

        // Re-enable guard
        setTimeout(() => {
          isActive.value = true;
        }, 100);
      }
    } catch (error) {
      console.error("Error in navigation guard:", error);
    }
  };

  // Setup the guard
  navigationGuardCleanup = router.on("before", handleNavigationGuard);

  // Handle browser refresh/close
  const handleBeforeUnload = (event) => {
    if (getIsDirty()) {
      const currentUrl = window.location.pathname;
      let isSaveAction = false;

      for (const pattern of skipUrls) {
        if (typeof pattern === "string" && currentUrl.includes(pattern)) {
          isSaveAction = true;
          break;
        }
      }

      if (!isSaveAction) {
        event.preventDefault();
        event.returnValue = "";
      }
    }
  };

  window.addEventListener("beforeunload", handleBeforeUnload);

  // Cleanup
  onUnmounted(() => {
    if (navigationGuardCleanup) {
      navigationGuardCleanup();
    }
    window.removeEventListener("beforeunload", handleBeforeUnload);
  });

  return {
    isActive,
  };
}
