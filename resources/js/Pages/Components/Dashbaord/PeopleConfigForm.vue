<script setup>
import { ref, reactive, computed, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import DashboardFilterBuilder from "./DashboardFilterBuilder.vue";
import WidthPicker from "./WidthPicker.vue";
import { buildOptions } from "./dashboardUi.js";

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const dashboardConfig = usePage().props.dashboardConfig;

const props = defineProps({
  modules: { type: Array, required: true },
  editingInstance: { type: Object, default: null },
  defaultCols: { type: Number, default: 2 },
});

const emit = defineEmits(["submit", "cancel"]);

const cfg = props.editingInstance?.config ?? {};

// "relation" combines two different ways of getting to a person: a plain
// record-type FK field (e.g. owner_id -> users), or a named Relationship
// (relationships + relationship_links tables) for modules that only link to
// people-like modules (contacts, leads) through that system. Encoded as
// "field:<name>" or "relationship:<name>" so a single dropdown can offer both.
const initialRelation = cfg.relationField
  ? `field:${cfg.relationField}`
  : cfg.relationshipName
    ? `relationship:${cfg.relationshipName}`
    : "";

const form = reactive({
  module: cfg.module ?? "",
  relation: initialRelation,
  aggregate: cfg.aggregate ?? "count",
  field: cfg.field ?? "",
  limit: cfg.limit ?? 10,
  label: cfg.label ?? "",
  filters: cfg.filters ?? [],
  filtersMatchType: cfg.filtersMatchType ?? "all",
  showAllRecords: cfg.showAllRecords ?? false,
  cols: props.editingInstance?.cols ?? props.defaultCols,
});

const fields = ref([]);
const relationships = ref([]);
const loadingFields = ref(false);

const relationFields = computed(() =>
  fields.value.filter((f) => f.type === "record" && f.related_module),
);
const numericFields = computed(() =>
  fields.value.filter((f) => dashboardConfig.numeric_field_types.includes(f.type)),
);
const needsField = computed(() => ["sum", "avg"].includes(form.aggregate));

const canSubmit = computed(() => {
  if (!form.module || !form.relation) return false;
  if (needsField.value && !form.field) return false;
  return true;
});

const moduleOptions = computed(() =>
  props.modules.map((m) => ({ value: m.slug, label: m.label })),
);
const relationOptions = computed(() => [
  ...relationFields.value.map((f) => ({ value: `field:${f.name}`, label: f.label })),
  ...relationships.value.map((r) => ({
    value: `relationship:${r.name}`,
    label: t(r.label),
  })),
]);
const numericOptions = computed(() =>
  numericFields.value.map((f) => ({ value: f.name, label: f.label })),
);

const aggregateOptions = buildOptions(t, dashboardConfig.allowed_metrics);

watch(
  () => form.module,
  async (slug) => {
    fields.value = [];
    relationships.value = [];
    if (!slug) {
      form.relation = "";
      form.field = "";
      return;
    }
    loadingFields.value = true;
    try {
      const [fieldsRes, relationshipsRes] = await Promise.all([
        axios.get(`/dashboard/module-fields/${slug}`),
        axios.get(`/dashboard/module-relationships/${slug}`),
      ]);
      fields.value = fieldsRes.data;
      relationships.value = relationshipsRes.data;
      if (!form.relation && initialRelation) form.relation = initialRelation;
      if (!form.field && cfg.field) form.field = cfg.field;
    } finally {
      loadingFields.value = false;
    }
  },
  { immediate: true },
);

watch(
  () => form.aggregate,
  () => {
    form.field = "";
  },
);

function submit() {
  if (!canSubmit.value) return;
  const [kind, name] = form.relation.split(":");
  emit("submit", {
    instanceId: props.editingInstance?.instanceId ?? crypto.randomUUID(),
    type: "people",
    cols: form.cols,
    config: {
      module: form.module,
      ...(kind === "relationship" ? { relationshipName: name } : { relationField: name }),
      aggregate: form.aggregate,
      ...(needsField.value ? { field: form.field } : {}),
      limit: form.limit || 10,
      label: form.label.trim() || null,
      filters: form.filters,
      filtersMatchType: form.filtersMatchType,
      showAllRecords: form.showAllRecords,
    },
  });
}
</script>

<template>
  <div class="tsf">
    <!-- Module -->
    <div class="tsf__field">
      <label class="tsf__label">{{ $t("globals.dashboard.field_module") }}</label>
      <FieldRenderer
        :field="{ type: 'select' }"
        :model-value="form.module || null"
        :related_field="{ dropdown_list: { values: moduleOptions } }"
        mode="dashboard"
        @update:modelValue="form.module = $event ?? ''"
      />
    </div>

    <template v-if="form.module">
      <!-- Relation (who links these records to a person — an owner-style field, or a named relationship) -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t("globals.dashboard.field_relation_field") }}</label>
        <div v-if="loadingFields" class="tsf__hint">
          {{ $t("globals.dashboard.loading_fields") }}
        </div>
        <FieldRenderer
          v-else
          :field="{ type: 'select' }"
          :model-value="form.relation || null"
          :related_field="{ dropdown_list: { values: relationOptions } }"
          mode="dashboard"
          @update:modelValue="form.relation = $event ?? ''"
        />
      </div>

      <!-- Aggregate -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t("globals.dashboard.field_aggregate") }}</label>
        <div class="tsf__row">
          <div class="tsf__col--narrow">
            <FieldRenderer
              :field="{ type: 'select', nullable: true }"
              :model-value="form.aggregate"
              :related_field="{ dropdown_list: { values: aggregateOptions } }"
              mode="dashboard"
              @update:modelValue="form.aggregate = $event"
            />
          </div>
          <div v-if="needsField" class="tsf__col--fill">
            <FieldRenderer
              :field="{ type: 'select' }"
              :model-value="form.field || null"
              :related_field="{ dropdown_list: { values: numericOptions } }"
              mode="dashboard"
              @update:modelValue="form.field = $event ?? ''"
            />
          </div>
        </div>
      </div>

      <!-- Max rows -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t("globals.dashboard.field_max_rows") }}</label>
        <FieldRenderer
          :field="{ type: 'integer', min: 1, max: 50 }"
          :model-value="form.limit"
          mode="dashboard"
          @update:modelValue="form.limit = $event"
        />
      </div>

      <!-- Label -->
      <div class="tsf__field">
        <label class="tsf__label"
          >{{ $t("globals.dashboard.field_label") }}
          <span class="tsf__opt">({{ $t("globals.dashboard.optional") }})</span></label
        >
        <FieldRenderer
          :field="{ type: 'text' }"
          :model-value="form.label"
          mode="dashboard"
          @update:modelValue="form.label = $event"
        />
      </div>

      <!-- Width -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t("globals.dashboard.field_width") }}</label>
        <WidthPicker v-model="form.cols" />
      </div>
    </template>

    <!-- Filters -->
    <DashboardFilterBuilder
      :module="form.module"
      :model-value="form.filters"
      :match-type="form.filtersMatchType"
      :show-all-records="form.showAllRecords"
      @update:model-value="form.filters = $event"
      @update:match-type="form.filtersMatchType = $event"
      @update:show-all-records="form.showAllRecords = $event"
    />

    <!-- Actions -->
    <div class="tsf__actions">
      <button class="tsf__btn tsf__btn--secondary" type="button" @click="emit('cancel')">
        {{ $t("globals.confirm.cancel_text") }}
      </button>
      <button
        class="tsf__btn tsf__btn--primary"
        type="button"
        :disabled="!canSubmit"
        @click="submit"
      >
        {{
          editingInstance
            ? $t("globals.dashboard.save_changes")
            : $t("globals.dashboard.add_widget")
        }}
      </button>
    </div>
  </div>
</template>
