<script setup>
import Layout from "@/Layouts/Layout.vue";
defineOptions({
  layout: Layout,
});
const props = defineProps({
  layout: Object,
  user: Object,
});
</script>
<template>
  <div>Hi {{ user?.username }}!</div>
</template>
