<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
const props = defineProps({
  record: Object,
  header: Object,
  related_slug: {
    type: String,
    required: false,
  },
});
const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;
</script>

<template>
  <tr>
    <td v-for="field in header" :key="field.name">
      <template v-if="field.name === 'name'">
        <Link :href="getRelatedRecordurl(related_slug, record.id)">
          {{ record[field.name] }}
        </Link>
      </template>
      <template v-else-if="field.type === 'datetime'">
        {{ formatDateTime(record[field.name]) }}
      </template>

      <template v-else>
        {{ record[field.name] }}
      </template>
    </td>
  </tr>
</template>
