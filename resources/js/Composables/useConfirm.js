import { reactive, readonly } from "vue";

const state = reactive({
  isOpen: false,
  title: "Please confirm",
  message: "Are you sure?",
  confirmText: "Confirm",
  cancelText: "Cancel",
  danger: false,
  highlight: null,
  _resolve: null,
});

export function useConfirm() {
  const confirm = (options = {}) => {
    state.title = options.title;
    state.message = options.message;
    state.confirmText = options.confirmText;
    state.cancelText = options.cancelText;
    state.danger = options.danger ?? false;
    state.highlight = options.highlight ?? null;
    state.isOpen = true;

    return new Promise((resolve) => {
      state._resolve = resolve;
    });
  };

  const accept = () => {
    if (typeof state._resolve === "function") state._resolve(true);
    cleanup();
  };

  const cancel = () => {
    if (typeof state._resolve === "function") state._resolve(false);
    cleanup();
  };

  const cleanup = () => {
    state.isOpen = false;
    state._resolve = null;
  };

  return {
    confirm,
    confirmState: readonly(state),
    accept,
    cancel,
  };
}
