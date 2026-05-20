import { onUnmounted } from "vue";

export function useDebounceFn(fn, delay = 300) {
  let timer = null;

  const debouncedFn = (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };

  onUnmounted(() => clearTimeout(timer));

  return debouncedFn;
}
