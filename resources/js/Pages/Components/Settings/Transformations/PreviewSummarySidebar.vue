<script setup>
import { usePage } from "@inertiajs/vue3";
import { formatDateTime } from "@/utils/datetime";

const appSettings = usePage().props.appSettings;

defineProps({
  sourceModuleMeta: { type: Object, default: null },
  targetModuleMeta: { type: Object, default: null },
  summary: { type: Object, required: true },
  automationEnabled: { type: Boolean, default: false },
  isEdit: { type: Boolean, default: false },
  updatedAt: { type: String, default: null },
});
</script>

<template>
  <aside class="transformations-edit__sidebar">
    <div
      class="transformations-edit__sidebar__card transformations-edit__sidebar__card--preview"
    >
      <div class="transformations-edit__card-header">
        <h4>{{ $t("globals.transformations.labels.preview_label") }}</h4>
      </div>
      <div class="transformations-edit__preview-pipeline">
        <div class="transformations-edit__pipeline-node">
          <span
            class="transformations-edit__pipeline-icon"
            :style="{ backgroundColor: sourceModuleMeta?.color || '#64748b' }"
          >
            <i
              class="fa-solid"
              :class="sourceModuleMeta?.icon || 'fa-cube'"
            ></i>
          </span>
          <span class="transformations-edit__pipeline-label">
            {{ sourceModuleMeta ? $t(sourceModuleMeta.label) : "—" }}
          </span>
        </div>

        <div class="transformations-edit__pipeline-arrow">
          <i class="fa-solid fa-arrow-down"></i>
        </div>

        <div
          class="transformations-edit__pipeline-node transformations-edit__pipeline-node--target"
        >
          <span
            class="transformations-edit__pipeline-icon"
            :style="{ backgroundColor: targetModuleMeta?.color || '#3b82f6' }"
          >
            <i
              class="fa-solid"
              :class="targetModuleMeta?.icon || 'fa-cube'"
            ></i>
          </span>
          <span class="transformations-edit__pipeline-label">
            {{
              $t("globals.transformations.labels.new_record_badge", {
                module: targetModuleMeta.label,
              })
            }}
          </span>
        </div>
      </div>

      <ul class="transformations-edit__preview-list">
        <li>
          <span class="transformations-edit__list-icon">
            <i class="fa-solid fa-database"></i>
          </span>
          <span class="transformations-edit__list-text">
            {{
              $t("globals.transformations.messages.preview_fields", {
                count: summary.field_mappings,
              })
            }}
          </span>
        </li>
        <li v-if="summary.line_items_offered">
          <span class="transformations-edit__list-icon">
            <i class="fa-solid fa-list-check"></i>
          </span>
          <span class="transformations-edit__list-text">
            {{ $t("globals.transformations.messages.preview_line_items") }}
          </span>
        </li>
        <li>
          <span class="transformations-edit__list-icon">
            <i class="fa-solid fa-diagram-project"></i>
          </span>
          <span class="transformations-edit__list-text">
            {{
              summary.relationships === 1
                ? $t(
                    "globals.transformations.messages.preview_relationships_one",
                    { count: summary.relationships },
                  )
                : $t(
                    "globals.transformations.messages.preview_relationships_many",
                    { count: summary.relationships },
                  )
            }}
          </span>
        </li>
      </ul>
    </div>

    <div
      class="transformations-edit__sidebar__card transformations-edit__sidebar__card--summary"
    >
      <div class="transformations-edit__card-header">
        <h4>{{ $t("globals.transformations.labels.summary_label") }}</h4>
      </div>

      <ul class="transformations-edit__summary-list">
        <li>
          <span>
            <i class="fa-solid fa-bolt"></i>
            {{ $t("globals.transformations.messages.summary_automatic") }}
          </span>
          <span
            class="transformations-edit__badge"
            :class="
              automationEnabled
                ? 'transformations-edit__badge--success'
                : 'transformations-edit__badge--neutral'
            "
          >
            <i
              class="fa-solid"
              :class="automationEnabled ? 'fa-circle-check' : 'fa-circle-xmark'"
            ></i>
            {{
              automationEnabled
                ? $t("globals.transformations.messages.yes")
                : $t("globals.transformations.messages.no")
            }}
          </span>
        </li>
        <li v-if="summary.conditions?.length">
          <span>
            <i class="fa-solid fa-filter"></i>
            {{ $t("globals.transformations.labels.conditions_label") }}
          </span>
          <strong>{{ summary.conditions }}</strong>
        </li>
        <li>
          <span>
            <i class="fa-solid fa-sliders"></i>
            {{ $t("globals.transformations.labels.field_mappings_label") }}
          </span>
          <strong>{{ summary.field_mappings }}</strong>
        </li>
        <li>
          <span>
            <i class="fa-solid fa-link"></i>
            {{ $t("globals.transformations.labels.relationships_label") }}
          </span>
          <strong>{{ summary.relationships }}</strong>
        </li>
        <li v-if="isEdit && updatedAt">
          <span>
            <i class="fa-solid fa-clock"></i>
            {{ $t("globals.transformations.messages.last_updated") }}
          </span>
          <strong class="transformations-edit__time-text">{{
            formatDateTime(updatedAt, appSettings)
          }}</strong>
        </li>
      </ul>
    </div>
  </aside>
</template>
