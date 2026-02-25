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

const formatField = (field, value) => {
  if (value == null || value === "") return "";

  const type = field?.type?.toLowerCase();

  switch (type) {
    case "textfield":
      return value;

    case "datetime":
      return formatDateTime(value);

    case "longtext":
      return value.length > 32 ? value.slice(0, 32) + "…" : value;

    default:
      return value;
  }
};
</script>

<template>
  <div v-for="field in header" :key="field.name">
    <template v-if="field.name === 'name'">
      <Link
        :href="getRelatedRecordurl(related_slug, record.id)"
        class="parent_records__body__title"
      >
        {{ record[field.name] }}
      </Link>
    </template>
    <template v-else>
      {{ formatField(field, record[field.name]) }}
    </template>
  </div>
</template>

<!-- <Link
            :href="
              getRelatedRecordurl(relationship.related_slug, parentRecord.id)
            "
            class="parent_records__body__title"
            >{{ parentRecord.name }}</Link
          >
          <div class="parent_records__body__details">
            <span>{{ parentRecord?.email || "-" }}</span>
            <span>{{ parentRecord?.phone || "-" }}</span>
          </div> -->
