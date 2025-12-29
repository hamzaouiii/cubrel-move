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
});

const styleObject = computed(() => {
  const style = {
    top: props.top + "px",
    left: props.left + "px",
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
