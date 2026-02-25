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
        <a
          :href="getRelatedRecordurl(related_slug, record.id)"
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

          <div class="parent-card__value">
            {{ formatField(field, parentRecord?.[field.name]) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped lang="scss">
.parent-card {
  background-color: rgba(240, 240, 240, 0.276);
  border-top: 1px solid rgba(211, 211, 211, 0.324);
  padding: 20px 22px;

  &__header {
    margin-bottom: 18px;
  }
  &__new-tab {
    all: unset;
    margin-left: 10px;
    cursor: pointer;
    &:hover {
      color: #2563eb;
    }
  }
  &__title {
    font-size: 20px;
    font-weight: 600;
    color: #2563eb;
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }

  &__fields {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  &__field {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  &__label {
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
  }

  &__value {
    font-size: 14px;
    color: #1f2937;
    word-break: break-word;
  }
}
</style>
