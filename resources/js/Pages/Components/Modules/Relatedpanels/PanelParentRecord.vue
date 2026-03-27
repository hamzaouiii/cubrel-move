<script setup>
import { computed } from "vue";

import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import FieldRenderer from "../../Globals/FieldRenderer.vue";
const props = defineProps({
  record: {
    type: Object,
    default: () => ({}),
  },
  header: Object,
  related_slug: {
    type: String,
    required: false,
  },
  color: String,
});

const parentRecord = props.record;
const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;

const formatField = (field, value) => {
  if (value == null || value === "") return "";

  const type = field?.type?.toLowerCase();

  switch (type) {
    case "text":
      return value;

    case "datetime":
      return formatDateTime(value);

    case "longtext":
      return value.length > 32 ? value.slice(0, 32) + "…" : value;

    default:
      return value;
  }
};

const titleField = computed(() => props.header.find((f) => f.name === "name"));

const nonTitleFields = computed(() =>
  props.header.filter((f) => f.name !== "name"),
);

// First 2 become meta line
const metaFields = computed(() => nonTitleFields.value.slice(0, 2));

// Rest go into detail grid
const detailFields = computed(() => nonTitleFields.value.slice(2));
</script>

<template>
  <div class="parent-wrapper">
    <div class="parent-card">
      <!-- Title -->
      <div class="parent-card__header">
        <Link
          :href="getRelatedRecordurl(related_slug, parentRecord?.id)"
          class="parent-card__title"
        >
          {{ parentRecord?.name }}
        </Link>
        <a
          :href="getRelatedRecordurl(related_slug, parentRecord?.id)"
          target="_blank"
          rel="noopener noreferrer"
          class="parent-card__new-tab"
        >
          <i class="fa-solid fa-up-right-from-square"></i>
        </a>
      </div>

      <!-- Fields -->
      <div class="parent-card__fields">
        <div
          v-for="field in header"
          :key="field.name"
          v-if="field?.name !== 'name'"
          class="parent-card__field"
        >
          <div class="parent-card__label">
            {{ $t(field.label) }}
          </div>

          <FieldRenderer
            :field="field"
            v-model="parentRecord[field.name]"
            mode="related-panel"
            :module-color="color"
          ></FieldRenderer>
        </div>
      </div>
    </div>
  </div>
</template>
