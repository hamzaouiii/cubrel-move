import { ref, nextTick } from "vue";

/**
 * Decides whether an absolutely-positioned dropdown menu should open above
 * its trigger instead of below. Needed because these menus are positioned
 * with `top: 100%` and get visually clipped when the trigger sits near the
 * bottom of the viewport (or a scrollable modal) on small screens.
 */
export function useDropdownFlip(triggerRef, { menuHeight = 280, margin = 8 } = {}) {
  const flipUp = ref(false);

  function measure() {
    const el = triggerRef.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    flipUp.value = spaceBelow < menuHeight + margin && spaceAbove > spaceBelow;
  }

  async function recalc() {
    await nextTick();
    measure();
  }

  return { flipUp, recalc };
}
