<script setup>
import { computed } from "vue";

import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
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
});

const parentRecord = props.record;
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

          <div class="parent-card__value">
            {{ formatField(field, parentRecord?.[field.name]) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.parent-card {
  background-color: rgba(240, 240, 240, 0.276);
  border-top: 1px solid rgba(211, 211, 211, 0.324);
  padding: 20px 22px;
  max-width: 720px;
}

.parent-card__header {
  margin-bottom: 18px;
}

.parent-card__title {
  font-size: 20px;
  font-weight: 600;
  color: #2563eb;
  text-decoration: none;
}

.parent-card__title:hover {
  text-decoration: underline;
}

.parent-card__fields {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.parent-card__field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.parent-card__label {
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
}

.parent-card__value {
  font-size: 14px;
  color: #1f2937;
  word-break: break-word;
}
</style>
