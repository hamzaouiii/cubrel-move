<script setup>
import {
  computed,
  getCurrentInstance,
  ref,
  onMounted,
  onBeforeUnmount,
} from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";
import Checkbox from "../../FiledTypes/Checkbox.vue";
import Switcher from "../../FiledTypes/Switcher.vue";

const props = defineProps({
  filterableFields: { type: Array, default: () => [] },
  availableFilters: { type: Array, default: () => [] },
  activeFilter: { type: Object, default: null },
  moduleSlug: { type: String, required: true },
});
const emit = defineEmits(["applyFilter", "clearFilter", "cancelClicked"]);

const { success, error } = useAlerts();
const { confirm } = useConfirm();
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();

const currentUser = computed(() => page.props.auth?.user ?? null);

const canManageFilter = (filter) => {
  if (!filter || !currentUser.value) return false;
  if (currentUser.value.is_admin) return true;
  return !filter.is_system && filter.user_id === currentUser.value.id;
};

const getField = (name) => props.filterableFields.find((f) => f.name === name);

// Source of truth lives in config/filter_operators.php, shared here as the
// `filterOperators` page prop (see ListController).
const operatorsForType = (type) => {
  const operators = page.props.filterOperators ?? {};
  return operators.by_type?.[type] ?? operators.default ?? [];
};

const emptyOperators = ["is_empty", "is_not_empty"];
const arrayOperators = ["between", "in"];

const VISIBLE_LIMIT = 4;
const overflowOpen = ref(false);
const overflowSearch = ref("");
const overflowRef = ref(null);

const byLastUsed = (a, b) => {
  const aTime = a?.last_used ? new Date(a.last_used).getTime() : 0;
  const bTime = b?.last_used ? new Date(b.last_used).getTime() : 0;
  return bTime - aTime; // descending: most recently used first
};

const sortedFilters = computed(() => {
  const DBprivateFilters = props.availableFilters?.private || [];
  const sharedFilters =
    props.availableFilters?.shared.filter((f) => f.slug !== "my_records") || [];
  const Myrecords =
    props.availableFilters?.shared.find((f) => f.slug === "my_records") || [];

  const allFilters = [...DBprivateFilters, ...sharedFilters].filter(Boolean);
  return [Myrecords, ...allFilters.sort(byLastUsed)];
});
const visibleFilters = computed(() => {
  const base = sortedFilters.value.slice(0, VISIBLE_LIMIT);

  return base;
});

const overflowFilters = computed(() => {
  return sortedFilters.value;
});

const filteredOverflow = computed(() => {
  if (!overflowSearch.value.trim()) return overflowFilters.value;
  const q = overflowSearch.value.toLowerCase();
  return overflowFilters.value.filter((f) => f.name.toLowerCase().includes(q));
});

const overflowPrivate = computed(() =>
  filteredOverflow.value.filter((f) => !f.is_shared),
);
const overflowShared = computed(() =>
  filteredOverflow.value.filter((f) => f.is_shared),
);
const newCondition = () => ({
  field: null,
  operator: null,
  value: null,
  valueLabel: null,
});

// ─── Saved filters panel ─────────────────────────────────────────────────────

const selectedFilterKey = ref(
  props.activeFilter?.slug ?? props.activeFilter?.id ?? null,
);

const clearSelected = () => {
  selectedFilterKey.value = null;
  emit("clearFilter");
};

const filterDisplayName = (filter) =>
  filter.is_system ? `modules.filters.${filter.slug}` : filter.name;

// ─── Condition builder ───────────────────────────────────────────────────────

const builderForm = useForm({
  name: "",
  is_shared: false,
  match_type: "all",
  conditions: [newCondition()],
});

const addCondition = () => builderForm.conditions.push(newCondition());
const removeCondition = (index) => builderForm.conditions.splice(index, 1);

const onFieldChange = (condition) => {
  condition.operator = null;
  condition.value = null;
};

const onOperatorChange = (condition) => {
  condition.value = arrayOperators.includes(condition.operator)
    ? [null, null]
    : null;
};

const fieldPickerField = computed(() => ({
  id: "filter-field-picker",
  type: "select",
  name: "field_picker",
  label: "modules.filters.field",
  nullable: true,
  dropdown_list: {
    values: props.filterableFields.map((f) => ({
      value: f.name,
      label: f.label,
    })),
  },
}));

const operatorPickerField = (fieldName) => {
  const field = getField(fieldName);
  return {
    id: "filter-operator-picker",
    type: "select",
    name: "operator_picker",
    label: "modules.filters.operator",
    nullable: true,
    dropdown_list: {
      values: operatorsForType(field?.type).map((op) => ({
        value: op,
        label: `modules.filters.operators.${op}`,
      })),
    },
  };
};

const overlayOpen = ref(false);
const activeConditionIndex = ref(null);
const showFilterBuilder = ref(false);
const editingFilter = ref(null);
const openValueOverlay = (index) => {
  activeConditionIndex.value = index;
  overlayOpen.value = true;
};

const onValueRecordSelect = (record) => {
  if (activeConditionIndex.value === null) return;
  const condition = builderForm.conditions[activeConditionIndex.value];
  condition.value = record.id;
  condition.valueLabel = record.name;
  overlayOpen.value = false;
  activeConditionIndex.value = null;
};

const activeOverlayField = computed(() => {
  if (activeConditionIndex.value === null) return null;
  return getField(builderForm.conditions[activeConditionIndex.value]?.field);
});

const selectFromOverflow = (filter) => {
  selectFilter(filter);
  overflowOpen.value = false;
  overflowSearch.value = "";
};
// ─── Submit ───────────────────────────────────────────────────────────────────

const canSave = computed(() => {
  return (
    builderForm.name.trim().length > 0 &&
    builderForm.conditions.length > 0 &&
    builderForm.conditions.every((c) => {
      if (!c.field || !c.operator) return false;
      if (emptyOperators.includes(c.operator)) return true;
      if (c.operator === "in") return true;
      if (c.operator === "between")
        return c.value?.[0] != null && c.value?.[1] != null;
      return c.value !== null && c.value !== "";
    })
  );
});

const saveFilter = () => {
  if (!canSave.value) return;

  const conditions = builderForm.conditions.map((c) => ({
    field: c.field,
    operator: c.operator,
    value:
      c.operator === "in" && typeof c.value === "string"
        ? c.value
            .split(",")
            .map((v) => v.trim())
            .filter(Boolean)
        : c.value,
    valueLabel: c.valueLabel,
  }));

  const isEditing = !!editingFilter.value;
  const url = isEditing
    ? `/${props.moduleSlug}/filters/${editingFilter.value.id}`
    : `/${props.moduleSlug}/filters`;

  builderForm
    .transform((data) => ({ ...data, conditions }))
    [isEditing ? "put" : "post"](url, {
      preserveScroll: true,
      onSuccess: () => {
        success(
          t(
            isEditing
              ? "modules.filters.update_success"
              : "modules.filters.save_success",
          ),
        );
        closeFilterBuilder();
      },
      onError: () => {
        error(
          t(
            isEditing
              ? "modules.filters.update_error"
              : "modules.filters.save_error",
          ),
        );
      },
    });
};

const currentModule = () => {
  return page.props.modules.find((e) => {
    return e.slug === props.moduleSlug;
  });
};

const matchTypeOptions = [
  { label: t("modules.filters.match_all"), value: "all" },
  { label: t("modules.filters.match_any"), value: "any" },
];
const resetBuilderForm = () => {
  builderForm.reset();
  builderForm.conditions = [newCondition()];
};

const openNewFilterBuilder = () => {
  editingFilter.value = null;
  resetBuilderForm();
  overflowOpen.value = false;
  showFilterBuilder.value = true;
};

const startEditFilter = (filter) => {
  if (!canManageFilter(filter)) return;
  editingFilter.value = filter;
  builderForm.name = filter.name;
  builderForm.is_shared = !!filter.is_shared;
  builderForm.match_type = filter.match_type || "all";
  builderForm.conditions = (filter.conditions || []).map((c) => ({
    field: c.field,
    operator: c.operator,
    value: c.value,
    valueLabel: c.valueLabel || null,
  }));
  overflowOpen.value = false;
  showFilterBuilder.value = true;
};

const closeFilterBuilder = () => {
  showFilterBuilder.value = false;
  editingFilter.value = null;
  resetBuilderForm();
};

const deleteFilter = async (filter) => {
  if (!canManageFilter(filter)) return;

  const ok = await confirm({
    title: t("modules.filters.delete_title"),
    message: t("modules.filters.delete_confirm", {
      name: filter.label ? t(filter.label) : filter.name,
    }),
    confirmText: t("modules.filters.delete_yes"),
    cancelText: t("modules.filters.delete_no"),
    danger: true,
  });
  if (!ok) return;

  router.delete(`/${props.moduleSlug}/filters/${filter.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedFilterKey.value === (filter.slug ?? filter.id)) {
        clearSelected();
        closeFilterBuilder();
      }
      success(t("modules.filters.delete_success"));
    },
    onError: () => {
      error(t("modules.filters.delete_error"));
    },
  });
};

const selectFilter = (filter) => {
  selectedFilterKey.value = filter?.slug ?? filter?.id ?? null;
  emit("applyFilter", selectedFilterKey.value);
};

const isSelected = (filter) => {
  if (selectedFilterKey.value)
    return (
      selectedFilterKey.value === filter.slug ||
      selectedFilterKey.value === filter.id
    );
  return false;
};

const handleClickOutsideActionDropDown = (event) => {
  if (overflowRef.value && !overflowRef.value.contains(event.target)) {
    overflowOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
});
</script>

<template>
  <div class="filter-zone">
    <div class="filter-zone__toolbar">
      <div class="filter-zone__saved">
        <div
          class="filter-zone__saved__filter"
          v-for="filter in visibleFilters"
          :key="filter.id ?? filter.slug"
          @click="selectFilter(filter)"
          :class="{
            'filter-zone__saved__filter--selected': isSelected(filter),
          }"
        >
          <span>{{ $t(filter?.label) || filter.name }}</span>
        </div>

        <div
          @click="overflowOpen = !overflowOpen"
          class="filter-zone__saved__overflow-trigger"
          ref="overflowRef"
        >
          <span
            class="filter-zone__saved__filter filter-zone__saved__filter--add"
          >
            <i class="fa-solid fa-ellipsis"></i>
          </span>

          <div
            v-if="overflowOpen"
            class="filter-zone__saved__overflow-panel"
            @click.stop
          >
            <div
              class="filter-zone__saved__overflow-panel__item filter-zone__saved__overflow-panel__item--action"
              @click="openNewFilterBuilder"
            >
              <span>
                <i class="fa-solid fa-plus"></i>
                {{ $t("modules.filters.new_filter") }}
              </span>
            </div>
            <input
              v-if="overflowFilters.length"
              v-model="overflowSearch"
              type="text"
              :placeholder="$t('modules.filters.search_filters')"
              class="filter-zone__saved__overflow-search"
            />
            <div class="filter-zone__saved__overflow-panel__list">
              <template v-if="overflowPrivate.length">
                <div class="filter-zone__saved__overflow-panel__group-label">
                  {{ $t("modules.filters.my_filters") }}
                </div>
                <div
                  v-for="filter in overflowPrivate"
                  :key="filter.id ?? filter.slug"
                  class="filter-zone__saved__overflow-panel__row"
                >
                  <div
                    class="filter-zone__saved__overflow-panel__item"
                    :class="{
                      'filter-zone__saved__overflow-panel__item--selected':
                        isSelected(filter),
                    }"
                    @click="selectFromOverflow(filter)"
                  >
                    <span>{{ filter.name }}</span>
                  </div>

                  <span
                    v-if="canManageFilter(filter)"
                    class="filter-zone__saved__overflow-panel__item__actions"
                    @click.stop="startEditFilter(filter)"
                  >
                    <i
                      class="fa-solid fa-pen"
                      :title="$t('modules.filters.edit_filter')"
                    ></i>
                  </span>
                </div>
              </template>

              <template v-if="overflowShared.length">
                <div class="filter-zone__saved__overflow-panel__group-label">
                  {{ $t("modules.filters.shared_filters") }}
                </div>
                <div
                  v-for="filter in overflowShared"
                  :key="filter.id ?? filter.slug"
                  class="filter-zone__saved__overflow-panel__row"
                >
                  <div
                    class="filter-zone__saved__overflow-panel__item"
                    :class="{
                      'filter-zone__saved__overflow-panel__item--selected':
                        isSelected(filter),
                    }"
                  >
                    <span @click="selectFromOverflow(filter)">{{
                      filter.name
                    }}</span>
                  </div>
                  <span
                    v-if="canManageFilter(filter)"
                    class="filter-zone__saved__overflow-panel__item__actions"
                    @click.stop="startEditFilter(filter)"
                  >
                    <i
                      class="fa-solid fa-pen"
                      :title="$t('modules.filters.edit_filter')"
                    ></i>
                  </span>
                </div>
              </template>
            </div>
          </div>
        </div>

        <span
          class="filter-zone__saved__filter filter-zone__saved__filter--clear"
          @click="clearSelected()"
          v-if="selectedFilterKey"
        >
          <i class="fa-solid fa-xmark"></i>
          <span>{{ $t("modules.filters.clear_filter") }}</span>
        </span>
      </div>
    </div>

    <Transition name="fade">
      <div class="filter-zone__builder" v-show="showFilterBuilder">
        <div class="filter-zone__builder__header">
          <div class="filter-zone__builder__title">
            <i class="fa-solid fa-sliders"></i>
            <h3>
              {{
                editingFilter
                  ? $t("modules.filters.edit_builder_title")
                  : $t("modules.filters.builder_title")
              }}
            </h3>
          </div>
          <button
            class="filter-zone__builder__close"
            @click="closeFilterBuilder"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div
          v-for="(condition, index) in builderForm.conditions"
          :key="index"
          class="filter-zone__condition"
        >
          <div class="filter-zone__condition__field">
            <label>{{ $t("modules.filters.field") }}</label>
            <FieldRenderer
              :field="fieldPickerField"
              v-model="condition.field"
              mode="edit"
              @update:modelValue="onFieldChange(condition)"
            />
          </div>

          <div class="filter-zone__condition__field" v-if="condition.field">
            <label>{{ $t("modules.filters.condition") }}</label>
            <FieldRenderer
              :field="operatorPickerField(condition.field)"
              v-model="condition.operator"
              mode="edit"
              :related_label="condition.valueLabel ?? null"
              @update:modelValue="onOperatorChange(condition)"
            />
          </div>

          <template
            v-if="
              condition.field &&
              condition.operator &&
              !emptyOperators.includes(condition.operator)
            "
          >
            <div class="filter-zone__condition__field">
              <label>{{ $t("modules.filters.value") }}</label>

              <div
                v-if="condition.operator === 'between'"
                class="filter-zone__condition__between"
              >
                <FieldRenderer
                  :field="getField(condition.field)"
                  v-model="condition.value[0]"
                  mode="edit"
                  :related_label="condition.valueLabel ?? null"
                  @open-link-overlay="openValueOverlay(index)"
                />
                <FieldRenderer
                  :field="getField(condition.field)"
                  v-model="condition.value[1]"
                  mode="edit"
                  :related_label="condition.valueLabel ?? null"
                  @open-link-overlay="openValueOverlay(index)"
                />
              </div>

              <FieldRenderer
                v-else
                :field="getField(condition.field)"
                v-model="condition.value"
                mode="edit"
                :related_label="condition.valueLabel ?? null"
                @open-link-overlay="openValueOverlay(index)"
              />
            </div>
          </template>

          <button
            class="filter-zone__condition__remove"
            v-if="builderForm.conditions.length > 1"
            @click="removeCondition(index)"
            title="Remove condition"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="filter-zone__builder__row">
          <div class="filter-zone__builder__field">
            <label>{{ $t("modules.filters.name") }}</label>
            <input
              v-model="builderForm.name"
              mode="edit"
              class="filter-zone__builder__name"
            />
          </div>
        </div>

        <div class="filter-zone__builder__actions">
          <div class="filter-zone__builder__match">
            <button class="filter-zone__builder__add-btn" @click="addCondition">
              <i class="fa-solid fa-plus"></i>
              {{ $t("modules.filters.add_condition") }}
            </button>
            <div v-if="builderForm.conditions.length > 1">
              <label>{{ $t("modules.filters.match_conditions") }}</label>
              <Switcher
                v-model="builderForm.match_type"
                :options="matchTypeOptions"
                :color="currentModule().color"
              />
            </div>
          </div>
          <div class="filter-zone__builder__match">
            <label class="filter-zone__share">
              <Checkbox
                v-model="builderForm.is_shared"
                :module-color="currentModule().color"
              />
              {{ $t("modules.filters.share_with_team") }}
            </label>

            <button
              class="filter-zone__builder__save-btn"
              :disabled="!canSave"
              @click="saveFilter"
            >
              <i class="fa-solid fa-floppy-disk"></i>
              {{
                editingFilter
                  ? $t("modules.filters.update")
                  : $t("modules.filters.save")
              }}
            </button>
            <button
              v-if="editingFilter"
              class="filter-zone__builder__delete-btn"
              @click="deleteFilter(editingFilter)"
            >
              <i class="fa-solid fa-trash-can"></i>
              {{ $t("modules.filters.delete_filter") }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <RecordSelectorDrawer
      :open="overlayOpen"
      :search-endpoint="
        activeOverlayField
          ? `/relatedfield/search/${activeOverlayField.related_module}`
          : ''
      "
      :related-module="activeOverlayField?.related_module"
      @select="onValueRecordSelect"
      @close="
        overlayOpen = false;
        activeConditionIndex = null;
      "
    />
  </div>
</template>
