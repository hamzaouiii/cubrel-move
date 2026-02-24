<script setup>
import Record from "@/Pages/Modules/Record.vue";
import { Link } from "@inertiajs/vue3";
const props = defineProps({
  record: Object,
  url: String,
  hasParent: Boolean,
});
</script>

<template>
  <div class="parent_records__body" v-if="hasParent">
    <Link :href="url" class="parent_records__body__title">{{
      record.name
    }}</Link>
    <div class="parent_records__body__details">
      <span>{{ record?.email || "-" }}</span>
      <span>{{ record?.phone || "-" }}</span>
    </div>
  </div>
</template>
