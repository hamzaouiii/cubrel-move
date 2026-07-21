<script setup>
//TODO make this universal

import { computed } from "vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  text: {
    type: String,
    default: "",
  },
  top: {
    type: Number,
    default: 0,
  },
  left: {
    type: Number,
    default: 0,
  },
  color: {
    type: String,
    default: "",
  },
  // 'right' anchors the tooltip's left edge at `left` (grows rightward, the
  // original/default behavior); 'left' anchors its right edge at `left`
  // instead (grows leftward) — for triggers near the right edge of the
  // viewport where a rightward tooltip would run off-screen.
  placement: {
    type: String,
    default: "right",
  },
});

const styleObject = computed(() => {
  const style = {
    top: props.top + "px",
    left: props.left + "px",
    transform:
      props.placement === "left"
        ? "translateY(-50%) translateX(-100%)"
        : "translateY(-50%)",
  };

  if (props.color) {
    style["--module-color"] = props.color;
  }

  return style;
});
</script>

<template>
  <div v-if="show" class="sidebar-tooltip" :style="styleObject">
    {{ text }}
  </div>
</template>
