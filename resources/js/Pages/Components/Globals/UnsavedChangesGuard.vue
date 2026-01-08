<script setup>
import { watch } from "vue";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";

const props = defineProps({
  isDirty: {
    type: Boolean,
    default: false,
  },
  confirmDialog: {
    type: Function,
    default: null,
  },
  translationPrefix: {
    type: String,
    default: "layouts",
  },
  excludeUrls: {
    type: Array,
    default: () => [],
  },
});

const { enableGuard, disableGuard } = useUnsavedChangesGuard({
  getIsDirty: () => props.isDirty,
  excludeUrls: props.excludeUrls,
});

// Watch isDirty to enable/disable guard
watch(
  () => props.isDirty,
  (newVal) => {
    if (newVal) {
      enableGuard();
    } else {
      disableGuard();
    }
  },
  { immediate: true }
);
</script>

<template>
  <!-- This component doesn't render anything -->
</template>
