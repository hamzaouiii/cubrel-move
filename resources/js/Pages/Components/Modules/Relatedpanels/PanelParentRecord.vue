<script setup>
import { computed } from "vue";

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
  fields: Object,
  color: String,
});

const parentRecord = computed(() => props.record ?? null);
const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;

const fieldResolver = (name) => {
  return props.fields?.find((field) => field.name === name);
};
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
      <div class="parent-card__fields" v-if="parentRecord">
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
            :field="fieldResolver(field.name)"
            v-model="parentRecord[field.name]"
            mode="related-panel"
            :module-color="color"
          ></FieldRenderer>
        </div>
      </div>
    </div>
  </div>
</template>
