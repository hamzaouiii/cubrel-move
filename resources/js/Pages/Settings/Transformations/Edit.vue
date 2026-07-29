<script setup>
import { computed, getCurrentInstance, reactive, ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import MappingRow from "@/Pages/Components/Settings/Transformations/MappingRow.vue";
import PreviewSummarySidebar from "@/Pages/Components/Settings/Transformations/PreviewSummarySidebar.vue";
import SettingDropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import Switcher from "@/Pages/Components/FiledTypes/Switcher.vue";
import ExplainTip from "@/Pages/Components/Globals/ExplainTip.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error } = useAlerts();
const { confirm } = useConfirm();
const page = usePage();
const appSettings = page.props.appSettings;

const props = defineProps({
  transformation: { type: Object, default: null },
  transform_modules: { type: Array, default: () => [] },
  hasLinkedRecords: { type: Boolean, default: false },
});

const isEdit = computed(() => !!props.transformation);

const stepConfig = (type) =>
  props.transformation?.steps?.find((s) => s.type === type)?.configuration ??
  {};

const form = reactive({
  name: props.transformation?.name ?? "",
  source_module: props.transformation?.source_module ?? "",
  target_module: props.transformation?.target_module ?? "",
  enabled: props.transformation?.enabled ?? true,
  automation_enabled: props.transformation?.automation_enabled ?? false,
  conditions: props.transformation?.conditions ?? [],
  conditions_match: props.transformation?.conditions_match ?? "all",
  // Field mappings and "default values" both answer the same question
  // ("what value should this target field get?"), there is only one
  // list, in field/static/expression mode per row. Pre-existing
  // transformations saved before this merge may still have a separate
  // set_values step; fold it into field_mappings on load so nothing is
  // lost.
  field_mappings: [
    ...(stepConfig("copy_fields").mappings ?? []),
    ...(stepConfig("set_values").values ?? []),
  ],
  relationships: stepConfig("copy_relationships").relationships ?? [],
  link_records_enabled: props.transformation?.link_records_enabled ?? true,
});

const editingName = ref(false);

const sourceModuleMeta = computed(() =>
  props.transform_modules.find((m) => m.slug === form.source_module),
);
const targetModuleMeta = computed(() =>
  props.transform_modules.find((m) => m.slug === form.target_module),
);

const sourceFieldOptions = computed(() => sourceModuleMeta.value?.fields ?? []);
const targetFieldOptions = computed(() => targetModuleMeta.value?.fields ?? []);

const moduleDropdownOptions = computed(() =>
  props.transform_modules.map((m) => ({
    value: m.slug,
    label: m.label,
    icon: m.icon,
  })),
);
const showPipeline = computed(() => {
  return form.source_module && form.target_module;
});
// With match "all" (AND), the same field can only be used once, e.g.
// "status is accepted AND status is draft" can never be true. With
// match "any" (OR) repeats are fine, e.g. "status is draft OR status
// is accepted" is a normal either-or check.
const conditionFieldOptionsFor = (index) => {
  const usedElsewhere =
    form.conditions_match === "all"
      ? new Set(
          form.conditions
            .filter((_, i) => i !== index)
            .map((c) => c.field)
            .filter(Boolean),
        )
      : new Set();

  return sourceFieldOptions.value
    .filter((f) => !usedElsewhere.has(f.name))
    .map((f) => ({ value: f.name, label: f.label }));
};

// The condition VALUE input must match the selected field's real type
// (status dropdown, date picker, record id, number, ...) rather than
// always being a plain text box, resolved via the same FieldRenderer
// dispatch every other field-driven input in the app uses.
const conditionFieldMeta = (fieldName) =>
  sourceFieldOptions.value.find((f) => f.name === fieldName) ?? null;

// Same operator vocabulary as the record list's filter builder (see
// FilterZone.vue), config/filter_operators.php is the single source of
// truth for which operators make sense per field type, shared here as
// the `filterOperators` page prop.
const operatorsForType = (type) => {
  const operators = page.props.filterOperators ?? {};
  return operators.by_type?.[type] ?? operators.default ?? [];
};
const emptyOperators = ["is_empty", "is_not_empty"];

const operatorOptionsFor = (fieldName) => {
  const field = conditionFieldMeta(fieldName);
  return operatorsForType(field?.type).map((op) => ({
    value: op,
    label: t(`modules.filters.operators.${op}`),
  }));
};

const onConditionFieldChange = (condition) => {
  condition.operator = "";
  condition.value = null;
};

const onConditionOperatorChange = (condition) => {
  condition.value = condition.operator === "between" ? [null, null] : null;
};

const availableRelationships = computed(() => {
  const options = [];
  if (sourceModuleMeta.value?.has_line_items) {
    options.push({
      name: "line_items",
      label: "globals.transformations.labels.line_items",
    });
  }
  for (const rel of sourceModuleMeta.value?.relationships ?? []) {
    options.push(rel);
  }
  return options;
});

const allRelationshipsSelected = computed(
  () =>
    availableRelationships.value.length > 0 &&
    availableRelationships.value.every((rel) =>
      form.relationships.includes(rel.name),
    ),
);

const toggleAllRelationships = () => {
  form.relationships = allRelationshipsSelected.value
    ? []
    : availableRelationships.value.map((rel) => rel.name);
};

const addCondition = () =>
  form.conditions.push({ field: "", operator: "", value: null });
const removeCondition = (i) => {
  form.conditions.splice(i, 1);
  // Automatic can't be saved with zero conditions
  if (form.automation_enabled && form.conditions.length === 0) {
    addCondition();
  }
};

watch(
  () => form.automation_enabled,
  (enabled) => {
    if (enabled && form.conditions.length === 0) {
      addCondition();
    }
  },
  { immediate: true },
);

const conditionOverlayOpen = ref(false);
const activeConditionIndex = ref(null);

const openConditionValueOverlay = (index) => {
  activeConditionIndex.value = index;
  conditionOverlayOpen.value = true;
};

const onConditionRecordSelect = (record) => {
  if (activeConditionIndex.value === null) return;
  form.conditions[activeConditionIndex.value].value = record.id;
  form.conditions[activeConditionIndex.value].valueLabel = record.name;
  conditionOverlayOpen.value = false;
  activeConditionIndex.value = null;
};

const activeConditionOverlayField = computed(() => {
  if (activeConditionIndex.value === null) return null;
  return conditionFieldMeta(form.conditions[activeConditionIndex.value]?.field);
});

const matchTypeOptions = [
  { value: "all", label: t("globals.transformations.options.match_all") },
  { value: "any", label: t("globals.transformations.options.match_any") },
];

const addMapping = () =>
  form.field_mappings.push({
    mode: "field",
    target_field: "",
    source_field: "",
  });
const removeMapping = (i) => form.field_mappings.splice(i, 1);

const targetFieldOptionsFor = (index) => {
  const usedElsewhere = new Set(
    form.field_mappings
      .filter((_, i) => i !== index)
      .map((m) => m.target_field)
      .filter(Boolean),
  );
  return targetFieldOptions.value.filter((f) => !usedElsewhere.has(f.name));
};

const steps = [
  {
    key: "trigger",
    label: "globals.transformations.steps.step_trigger",
    subtitle: "globals.transformations.steps.step_trigger_subtitle",
  },
  {
    key: "mapping",
    label: "globals.transformations.steps.step_mapping",
    subtitle: "globals.transformations.steps.step_mapping_subtitle",
  },
];
const activeStep = ref("trigger");
const activeStepIndex = computed(() =>
  steps.findIndex((s) => s.key === activeStep.value),
);

const hasFilledCondition = computed(() =>
  form.conditions.some((c) => !!c.field),
);
const canLeaveTrigger = computed(
  () => !form.automation_enabled || hasFilledCondition.value,
);

const goToStep = (key) => {
  if (key !== "trigger" && !canLeaveTrigger.value) {
    error(t("globals.transformations.messages.automation_requires_condition"));
    return;
  }
  activeStep.value = key;
};

const summary = computed(() => ({
  conditions: form.conditions.length,
  field_mappings: form.field_mappings.length,
  relationships: form.relationships.length,
  line_items_offered: form.relationships.includes("line_items"),
}));

const saving = ref(false);

const submit = () => {
  saving.value = true;
  const url = isEdit.value
    ? `/settings/transformations/${props.transformation.id}`
    : "/settings/transformations";
  const method = isEdit.value ? "put" : "post";

  router[method](url, form, {
    onError: (errors) =>
      error(
        Object.values(errors)[0] ??
          t("globals.transformations.messages.save_error"),
      ),
    onFinish: () => (saving.value = false),
  });
};

const handleDelete = async () => {
  const confirmed = await confirm({
    title: t("globals.transformations.messages.delete_confirm_title"),
    message: props.hasLinkedRecords
      ? t("globals.transformations.messages.delete_confirm_with_records", {
          name: form.name,
        })
      : t("globals.transformations.messages.delete_confirm", {
          name: form.name,
        }),
    highlight: form.name,
    danger: true,
  });
  if (!confirmed) return;

  router.delete(`/settings/transformations/${props.transformation.id}`, {
    onError: () => error(t("globals.transformations.messages.delete_error")),
  });
};

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  {
    label: t("settings.items.transformations"),
    href: "/settings/transformations",
  },
  {
    label: isEdit.value
      ? t("globals.transformations.labels.edit_title")
      : t("globals.transformations.labels.new_title"),
  },
];
</script>

<template>
  <Head>
    <title>
      {{
        isEdit
          ? $t("globals.transformations.labels.edit_title")
          : $t("globals.transformations.labels.new_title")
      }}
    </title>
  </Head>

  <div
    class="settings transformations-edit"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--module-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>

    <div class="transformations-edit__toolbar">
      <div>
        <button
          v-if="isEdit"
          type="button"
          class="transformations-edit__toolbar__btn transformations-edit__toolbar__btn--delete"
          @click="handleDelete"
        >
          <i class="fa-solid fa-trash"></i>
          {{ $t("globals.transformations.buttons.delete_btn") }}
        </button>

        <button
          v-if="isEdit"
          type="button"
          class="transformations-edit__toolbar__btn transformations-edit__toolbar__btn--toggle-enabled"
          :class="{
            'transformations-edit__toolbar__btn--toggle-enabled--off':
              !form.enabled,
          }"
          @click="form.enabled = !form.enabled"
        >
          <i class="fa-solid fa-power-off"></i>
          {{
            form.enabled
              ? $t("globals.transformations.buttons.disable_btn")
              : $t("globals.transformations.buttons.enable_btn")
          }}
        </button>
      </div>
      <div>
        <button
          type="button"
          class="transformations-edit__toolbar__btn transformations-edit__toolbar__btn--cancel"
          @click="router.visit('/settings/transformations')"
        >
          {{ $t("globals.transformations.buttons.cancel") }}
        </button>

        <button
          type="button"
          class="transformations-edit__toolbar__btn transformations-edit__toolbar__btn--save"
          :disabled="
            saving || !form.name || !form.source_module || !form.target_module
          "
          @click="submit"
        >
          {{
            saving
              ? $t("globals.transformations.buttons.saving")
              : $t("globals.transformations.buttons.save_btn")
          }}
        </button>
      </div>
    </div>

    <div class="transformations-edit__layout">
      <div class="transformations-edit__main">
        <div class="transformations-edit__pipeline-card">
          <div class="transformations-edit__identity">
            <div class="transformations-edit__identity__title">
              <label>{{
                $t("globals.transformations.labels.name_label")
              }}</label>
              <input
                v-model="form.name"
                type="text"
                :placeholder="
                  $t('globals.transformations.placeholders.name_placeholder')
                "
                @blur="editingName = false"
                @keyup.enter="editingName = false"
                autofocus
              />
            </div>
          </div>
          <div class="transformations-edit__module-pickers">
            <label>
              {{ $t("globals.transformations.labels.source_module_label") }}
              <SettingDropdownField
                v-model="form.source_module"
                :options="moduleDropdownOptions"
                :disabled="isEdit"
                :placeholder="
                  $t('globals.transformations.placeholders.module_placeholder')
                "
              />
            </label>
            <label>
              {{ $t("globals.transformations.labels.target_module_label") }}
              <SettingDropdownField
                v-model="form.target_module"
                :options="moduleDropdownOptions"
                :disabled="isEdit"
                :placeholder="
                  $t('globals.transformations.placeholders.module_placeholder')
                "
              />
            </label>
          </div>
          <div class="transformations-edit__pipeline" v-if="showPipeline">
            <div
              class="transformations-edit__pipeline__module transformations-edit__pipeline__module--left"
            >
              <span
                class="transformations-edit__pipeline__module__icon"
                :style="{
                  backgroundColor: sourceModuleMeta?.color || '#94a3b8',
                }"
              >
                <i
                  class="fa-solid"
                  :class="sourceModuleMeta?.icon || 'fa-cube'"
                ></i>
              </span>
              <div class="transformations-edit__pipeline__module__label">
                {{
                  sourceModuleMeta
                    ? $t(sourceModuleMeta.label)
                    : $t(
                        "globals.transformations.placeholders.module_placeholder",
                      )
                }}
                <span>{{
                  $t("globals.transformations.labels.source_module_label")
                }}</span>
              </div>
            </div>

            <div class="transformations-edit__pipeline__arrow">
              <span>{{
                $t("globals.transformations.labels.creates_badge")
              }}</span>
              <i class="fa-solid fa-arrow-right-long"></i>
            </div>

            <div
              class="transformations-edit__pipeline__module transformations-edit__pipeline__module--right"
            >
              <span
                class="transformations-edit__pipeline__module__icon"
                :style="{
                  backgroundColor: targetModuleMeta?.color || '#94a3b8',
                }"
              >
                <i
                  class="fa-solid"
                  :class="targetModuleMeta?.icon || 'fa-cube'"
                ></i>
              </span>
              <div class="transformations-edit__pipeline__module__label">
                {{
                  targetModuleMeta
                    ? $t(targetModuleMeta.label)
                    : $t(
                        "globals.transformations.placeholders.module_placeholder",
                      )
                }}
                <span>{{
                  $t("globals.transformations.labels.target_module_label")
                }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-if="showPipeline" class="transfomations-edit__lab">
          <!-- Step tabs -->
          <div class="transformations-edit__tabs">
            <button
              v-for="(step, i) in steps"
              :key="step.key"
              type="button"
              class="transformations-edit__tabs__item"
              :class="{
                'transformations-edit__tabs__item--active':
                  activeStep === step.key,
                'transformations-edit__tabs__item--done': i < activeStepIndex,
              }"
              @click="goToStep(step.key)"
            >
              <span class="transformations-edit__tabs__item__number">{{
                i + 1
              }}</span>
              <span class="transformations-edit__tabs__item__text">
                <strong>{{ $t(step.label) }}</strong>
                <small>{{ $t(step.subtitle) }}</small>
              </span>
            </button>
          </div>

          <section
            v-if="activeStep === 'trigger'"
            class="transformations-edit__section"
          >
            <div class="transformations-edit__badges">
              <label class="transformations-edit__checkbox">
                <Checkbox v-model="form.automation_enabled" />
                {{
                  $t("globals.transformations.labels.automation_enabled_short")
                }}
                <i class="fa-solid fa-bolt"></i>
              </label>
              <ExplainTip
                :text="
                  $t('globals.transformations.hints.automation_enabled_explain')
                "
              />
            </div>

            <div class="transformations-edit__badge-group">
              <label class="transformations-edit__checkbox">
                <Checkbox v-model="form.link_records_enabled" />
                {{ $t("globals.transformations.labels.link_records_label") }}
              </label>
              <ExplainTip
                :text="$t('globals.transformations.hints.link_records_explain')"
              />
            </div>

            <p
              v-if="form.automation_enabled && form.link_records_enabled"
              class="transformations-edit__link-warning"
            >
              <i class="fa-solid fa-triangle-exclamation"></i>
              {{
                $t(
                  "globals.transformations.messages.link_records_override_warning",
                )
              }}
            </p>

            <Transition name="expand">
              <div
                v-if="form.automation_enabled"
                class="transformations-edit__conditions-panel"
              >
                <div class="transformations-edit__conditions-header">
                  <div>
                    <h3>
                      {{
                        $t("globals.transformations.labels.conditions_label")
                      }}
                    </h3>
                  </div>

                  <div
                    v-if="form.conditions.length > 1"
                    class="transformations-edit__match"
                  >
                    <label>{{
                      $t("globals.transformations.labels.match_label")
                    }}</label>
                    <Switcher
                      v-model="form.conditions_match"
                      :options="matchTypeOptions"
                    />
                  </div>
                </div>
                <div class="transformations-edit__condition-group">
                  <div
                    v-for="(condition, i) in form.conditions"
                    :key="i"
                    class="transformations-edit__condition-row"
                  >
                    <span
                      v-if="i > 0"
                      class="transformations-edit__condition-row__boolean"
                      :class="{
                        'transformations-edit__condition-row__boolean--or':
                          form.conditions_match === 'any',
                      }"
                    >
                      {{
                        form.conditions_match === "any"
                          ? $t("globals.transformations.options.or_label")
                          : $t("globals.transformations.options.and_label")
                      }}
                    </span>

                    <div class="transformations-edit__condition-row__field">
                      <label>{{
                        $t("globals.transformations.labels.field_label")
                      }}</label>
                      <SettingDropdownField
                        v-model="condition.field"
                        :options="conditionFieldOptionsFor(i)"
                        :placeholder="
                          $t(
                            'globals.transformations.placeholders.source_field_placeholder',
                          )
                        "
                        @update:model-value="onConditionFieldChange(condition)"
                      />
                    </div>
                    <SettingDropdownField
                      v-if="condition.field"
                      class="transformations-edit__condition-row__operator"
                      v-model="condition.operator"
                      :options="operatorOptionsFor(condition.field)"
                      :searchable="false"
                      @update:model-value="onConditionOperatorChange(condition)"
                    />
                    <div
                      v-if="
                        condition.field &&
                        condition.operator &&
                        !emptyOperators.includes(condition.operator)
                      "
                      class="transformations-edit__condition-row__value"
                    >
                      <label>{{
                        $t("globals.transformations.labels.value_label")
                      }}</label>
                      <input
                        v-if="
                          conditionFieldMeta(condition.field)?.type ===
                          'address'
                        "
                        type="text"
                        class="transformations-edit__condition-row__plain-input"
                        v-model="condition.value"
                        :placeholder="
                          $t(
                            'globals.transformations.placeholders.value_placeholder',
                          )
                        "
                      />
                      <template v-else-if="condition.operator === 'between'">
                        <FieldRenderer
                          :field="conditionFieldMeta(condition.field)"
                          v-model="condition.value[0]"
                          mode="edit"
                        />
                        <FieldRenderer
                          :field="conditionFieldMeta(condition.field)"
                          v-model="condition.value[1]"
                          mode="edit"
                        />
                      </template>
                      <FieldRenderer
                        v-else
                        :field="conditionFieldMeta(condition.field)"
                        v-model="condition.value"
                        mode="edit"
                        :related_label="condition.valueLabel ?? null"
                        @open-link-overlay="openConditionValueOverlay(i)"
                      />
                    </div>
                    <button
                      type="button"
                      class="transformations-edit__condition-row__remove"
                      @click="removeCondition(i)"
                    >
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>

                <button type="button" @click="addCondition">
                  <i class="fa-solid fa-plus"></i>
                  {{ $t("globals.transformations.buttons.add_condition_btn") }}
                </button>
              </div>
            </Transition>
          </section>

          <!-- Step 2: Mapping -->
          <template v-if="activeStep === 'mapping'">
            <section class="transformations-edit__section">
              <h3>
                {{ $t("globals.transformations.labels.field_mappings_label") }}
              </h3>
              <p class="transformations-edit__hint">
                {{ $t("globals.transformations.hints.field_mappings_hint") }}
              </p>

              <MappingRow
                v-for="(mapping, i) in form.field_mappings"
                :key="i"
                :model-value="mapping"
                :target-fields="targetFieldOptionsFor(i)"
                :source-fields="sourceFieldOptions"
                :source-module="form.source_module"
                @update:model-value="(v) => (form.field_mappings[i] = v)"
                @remove="removeMapping(i)"
              />

              <button type="button" @click="addMapping">
                <i class="fa-solid fa-plus"></i>
                {{ $t("globals.transformations.buttons.add_mapping_btn") }}
              </button>
            </section>

            <section class="transformations-edit__section">
              <div class="transformations-edit__conditions-header">
                <div>
                  <h3>
                    {{
                      $t("globals.transformations.labels.relationships_label")
                    }}
                  </h3>
                  <p class="transformations-edit__hint">
                    {{ $t("globals.transformations.hints.relationships_hint") }}
                  </p>
                </div>

                <button
                  v-if="availableRelationships.length > 0"
                  type="button"
                  class="transformations-edit__select-all"
                  @click="toggleAllRelationships"
                >
                  {{
                    allRelationshipsSelected
                      ? $t("globals.transformations.buttons.unselect_all")
                      : $t("globals.transformations.buttons.select_all")
                  }}
                </button>
              </div>

              <div class="transformations-edit__relationship-grid">
                <label
                  v-for="rel in availableRelationships"
                  :key="rel.name"
                  class="transformations-edit__checkbox"
                >
                  <Selectbox v-model="form.relationships" :value="rel.name" />
                  {{ $t(rel.label) }}
                </label>
              </div>
            </section>
          </template>
        </div>
      </div>

      <PreviewSummarySidebar
        v-if="showPipeline"
        :source-module-meta="sourceModuleMeta"
        :target-module-meta="targetModuleMeta"
        :summary="summary"
        :automation-enabled="form.automation_enabled"
        :is-edit="isEdit"
        :updated-at="transformation?.updated_at"
      />
    </div>

    <RecordSelectorDrawer
      :open="conditionOverlayOpen"
      :search-endpoint="
        activeConditionOverlayField
          ? `/relatedfield/search/${activeConditionOverlayField.related_module}`
          : ''
      "
      :related-module="activeConditionOverlayField?.related_module"
      @select="onConditionRecordSelect"
      @close="
        conditionOverlayOpen = false;
        activeConditionIndex = null;
      "
    />
  </div>
</template>
