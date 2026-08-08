<script setup>
import { ref, reactive, computed, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import ColorPicker from "@/Pages/Components/FiledTypes/ColorPicker.vue";
import IconPicker from "@/Pages/Components/Settings/Modules/IconPicker.vue";
import DashboardFilterBuilder from "./DashboardFilterBuilder.vue";
import WidthPicker from "./WidthPicker.vue";
import { buildOptions } from "./dashboardUi.js";

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const dashboardConfig = usePage().props.dashboardConfig;

const props = defineProps({
  modules: { type: Array, required: true },
  editingInstance: { type: Object, default: null },
  defaultCols: { type: Number, default: 1 },
});

const emit = defineEmits(["submit", "cancel"]);

const cfg = props.editingInstance?.config ?? {};

const form = reactive({
  module: cfg.module ?? "",
  aggregate: cfg.aggregate ?? "count",
  field: cfg.field ?? "",
  label: cfg.label ?? "",
  icon: cfg.icon ?? "fa-solid fa-chart-simple",
  iconBg: cfg.iconBg ?? "#e8f5e9",
  iconColor: cfg.iconColor ?? "#2e7d32",
  filters: cfg.filters ?? [],
  filtersMatchType: cfg.filtersMatchType ?? "all",
  showAllRecords: cfg.showAllRecords ?? false,
  cols: props.editingInstance?.cols ?? props.defaultCols,
});

const fields = ref([]);
const loadingFields = ref(false);

const numericFields = computed(() =>
  fields.value.filter((f) => dashboardConfig.numeric_field_types.includes(f.type)),
);
const needsField = computed(() => ["sum", "avg"].includes(form.aggregate));

const canSubmit = computed(() => {
  if (!form.module) return false;
  if (needsField.value && !form.field) return false;
  return true;
});

const moduleOptions = computed(() =>
  props.modules.map((m) => ({ value: m.slug, label: m.label })),
);
const numericOptions = computed(() =>
  numericFields.value.map((f) => ({ value: f.name, label: f.label })),
);

const aggregateOptions = buildOptions(t, dashboardConfig.allowed_metrics);

watch(
  () => form.module,
  async (slug) => {
    fields.value = [];
    if (!slug) {
      form.field = "";
      return;
    }
    loadingFields.value = true;
    try {
      const { data } = await axios.get(`/dashboard/module-fields/${slug}`);
      fields.value = data;
      // Restore field selection when pre-loading for edit
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
  emit("submit", {
    instanceId: props.editingInstance?.instanceId ?? crypto.randomUUID(),
    type: "metric",
    cols: form.cols,
    config: {
      module: form.module,
      aggregate: form.aggregate,
      ...(needsField.value ? { field: form.field } : {}),
      label: form.label.trim() || null,
      icon: form.icon,
      iconBg: form.iconBg,
      iconColor: form.iconColor,
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
      <label class="tsf__label">{{
        $t("globals.dashboard.field_module")
      }}</label>
      <FieldRenderer
        :field="{ type: 'select' }"
        :model-value="form.module || null"
        :related_field="{ dropdown_list: { values: moduleOptions } }"
        mode="dashboard"
        @update:modelValue="form.module = $event ?? ''"
      />
    </div>

    <template v-if="form.module">
      <div class="tsf__field">
        <label class="tsf__label">{{
          $t("globals.dashboard.field_aggregate")
        }}</label>
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
            <div v-if="loadingFields" class="tsf__hint">
              {{ $t("globals.dashboard.loading") }}
            </div>
            <FieldRenderer
              v-else
              :field="{ type: 'select' }"
              :model-value="form.field || null"
              :related_field="{ dropdown_list: { values: numericOptions } }"
              mode="dashboard"
              @update:modelValue="form.field = $event ?? ''"
            />
          </div>
        </div>
      </div>

      <!-- Label -->
      <div class="tsf__field">
        <label class="tsf__label"
          >{{ $t("globals.dashboard.field_label") }}
          <span class="tsf__opt"
            >({{ $t("globals.dashboard.optional") }})</span
          ></label
        >
        <FieldRenderer
          :field="{ type: 'text' }"
          :model-value="form.label"
          mode="dashboard"
          @update:modelValue="form.label = $event"
        />
      </div>

      <!-- Icon -->
      <div class="tsf__field">
        <label class="tsf__label">{{
          $t("globals.dashboard.field_icon")
        }}</label>
        <IconPicker v-model="form.icon" :color="form.iconColor" />
      </div>

      <!-- Icon colors -->
      <div class="tsf__row tsf__row--2col">
        <div class="tsf__field">
          <label class="tsf__label">{{
            $t("globals.dashboard.field_icon_bg")
          }}</label>
          <ColorPicker v-model="form.iconBg" />
        </div>
        <div class="tsf__field">
          <label class="tsf__label">{{
            $t("globals.dashboard.field_icon_color")
          }}</label>
          <ColorPicker v-model="form.iconColor" />
        </div>
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
      <button
        class="tsf__btn tsf__btn--secondary"
        type="button"
        @click="emit('cancel')"
      >
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
