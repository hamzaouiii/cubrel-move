<script setup>
import {
  reactive,
  ref,
  computed,
  watch,
  getCurrentInstance,
  onMounted,
  onBeforeUnmount,
} from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { useAlerts } from "@/Composables/useAlerts";
import { useFieldValidation } from "@/Composables/useFieldValidation";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import FieldRenderer from "../Globals/FieldRenderer.vue";
import RecordSelectorDrawer from "./RecordSelectorDrawer.vue";

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  moduleSlug: {
    type: String,
    required: true,
  },
  fields: {
    type: Array,
    default: () => [],
  },
  icon: {
    type: String,
    default: "fa-solid fa-user",
  },
  accentColor: {
    type: String,
    default: "var(--module-color)",
  },
});

const emit = defineEmits(["close", "created"]);

const { success, error: showError, info, clearAllAlerts } = useAlerts();
const { validateFieldTypes } = useFieldValidation({ fields: props.fields });

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const allLayouts = computed(() => usePage().props.layouts);
const allModules = computed(() => usePage().props.modules);

const moduleLabel = computed(() => t("modules." + props.moduleSlug + ".single_label"));

// Falls back to a single synthetic section built from the field metadata so the
// modal still renders something usable if a module has no "record" layout defined.
const recordLayout = computed(() => {
  const found = allLayouts.value.find((l) => l.module === props.moduleSlug)?.layouts
    ?.record;

  if (found?.sections?.length) {
    return found;
  }

  return {
    sections: [
      {
        name: "",
        has_line_items: false,
        layout: props.fields
          .filter(
            (f) => !f.readonly && f.name !== "created_at" && f.name !== "updated_at",
          )
          .map((f) => ({ name: f.name, label: f.label })),
      },
    ],
  };
});

const visibleSections = computed(() =>
  recordLayout.value.sections.filter((s) => !s.has_line_items),
);

const getField = (f) => props.fields.find((field) => field.name === f.name) ?? f;

const getIcon = (slug) => {
  if (!slug) return "fa-solid fa-user";
  return allModules.value.find((m) => m.slug === slug)?.icon || "fa-solid fa-user";
};

const getLinkingLayout = (slug) => {
  if (!slug) return null;
  return (
    allLayouts.value.find((l) => l.module === slug)?.layouts?.linkingPanel?.columns ||
    null
  );
};

const buildInitialForm = () => {
  const data = {};

  visibleSections.value.forEach((section) => {
    section.layout.forEach((field) => {
      if (!(field.name === "created_at" || field.name === "updated_at")) {
        const meta = props.fields.find((f) => f.name === field.name);
        data[field.name] = meta?.type === "checkbox" ? false : "";
      }
    });
  });

  return data;
};

const form = reactive(buildInitialForm());
const dirty = ref(false);
const saving = ref(false);
const validationErrors = ref([]);

const hasError = computed(
  () => (field) => validationErrors.value.some((item) => item.field === field.name),
);

const updateField = (name, value) => {
  form[name] = value;
  dirty.value = true;
};

const fieldOverlayOpen = ref(false);
const activeField = ref(null);

const openFieldOverlay = (field) => {
  activeField.value = field;
  fieldOverlayOpen.value = true;
};

const onFieldRecordSelect = (record) => {
  if (!activeField.value) return;

  const fieldName = activeField.value.name;
  form[fieldName] = record.id;
  form[fieldName + "__label"] = record.name;
  dirty.value = true;
  fieldOverlayOpen.value = false;
  activeField.value = null;
};

const getRequiredFields = () =>
  props.fields.filter((field) => field.required === true && field.readonly !== true);

const isEmptyValue = (value) =>
  value === "" ||
  value === "---" ||
  value === null ||
  value === undefined ||
  (Array.isArray(value) && value.length === 0);

const validateRequiredFields = () => {
  getRequiredFields().forEach((field) => {
    if (isEmptyValue(form[field.name])) {
      validationErrors.value.push({
        field: field.name,
        label: field.label,
        type: "required",
      });
    }
  });

  if (validationErrors.value.length > 1) {
    clearAllAlerts();
    showError(t("fields.validation.is_required_several"));
  } else if (validationErrors.value.length === 1) {
    clearAllAlerts();
    showError(
      t(validationErrors.value[0].label) + " " + t("fields.validation.is_required"),
    );
  }
};

const resetState = () => {
  const fresh = buildInitialForm();
  Object.keys(form).forEach((key) => {
    if (!(key in fresh)) delete form[key];
  });
  Object.assign(form, fresh);

  validationErrors.value = [];
  dirty.value = false;
  saving.value = false;
  fieldOverlayOpen.value = false;
  activeField.value = null;
};

watch(
  () => props.open,
  (val) => {
    if (val) resetState();
  },
);

const submit = async () => {
  if (saving.value) return;

  clearAllAlerts();
  validationErrors.value = [];

  validateRequiredFields();
  if (validationErrors.value.length > 0) return;

  const typeErrors = validateFieldTypes({ ...form });
  if (typeErrors.length > 0) {
    validationErrors.value = [...validationErrors.value, ...typeErrors];
    clearAllAlerts();

    if (typeErrors.length > 1) {
      showError(t("fields.validation.invalid_several"));
    } else {
      showError(t(typeErrors[0].label) + " " + t("fields.validation.invalid_format"));
    }
    return;
  }

  saving.value = true;
  try {
    info(t("modules.actions.saving"));
    const { data } = await axios.post(`/${props.moduleSlug}`, form, {
      headers: { Accept: "application/json" },
    });
    clearAllAlerts();
    success(t("modules.actions.create_success"));
    emit("created", data);
  } catch (e) {
    clearAllAlerts();
    showError(e.response?.data?.message || t("modules.actions.create_error"));
  } finally {
    saving.value = false;
  }
};

const close = () => {
  if (saving.value) return;
  emit("close");
};

const handleKeydown = (e) => {
  if (!props.open || fieldOverlayOpen.value) return;

  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    e.stopPropagation();
    submit();
  }

  if (e.key === "Escape") {
    e.preventDefault();
    e.stopPropagation();
    if (!dirty.value) close();
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});

useUnsavedChangesGuard({
  getIsDirty: () => props.open && dirty.value,
});
</script>

<template>
  <Transition name="fade" appear>
    <div
      v-if="open"
      class="quick-create-modal"
      :style="{ '--module-color': accentColor }"
      role="dialog"
      aria-modal="true"
    >
      <div class="quick-create-modal__container">
        <div class="quick-create-modal__header">
          <div class="quick-create-modal__header__title">
            <i :class="icon"></i>
            <span>{{ $t("modules.linking.create_record") }} — {{ moduleLabel }}</span>
          </div>
          <div class="quick-create-modal__header__actions">
            <button
              class="quick-create-modal__header__actions__btn"
              :disabled="saving"
              @click="close"
            >
              {{ $t("modules.actions.cancel") }}
            </button>
            <button
              class="quick-create-modal__header__actions__btn quick-create-modal__header__actions__btn--primary"
              :disabled="saving"
              @click="submit"
            >
              {{ $t("modules.actions.save") }}
            </button>
          </div>
        </div>

        <div class="quick-create-modal__body">
          <div class="record-layout__sections">
            <div
              v-for="s in visibleSections"
              :key="s.name"
              class="record-layout__sections__item"
            >
              <div v-if="s.name" class="record-layout__sections__item__title">
                {{ s.name }}
              </div>

              <div class="record-layout__sections__item__layout">
                <div
                  v-for="f in s.layout.filter((f) => !getField(f).readonly)"
                  :key="f.name"
                  class="record-layout__sections__item__layout__field"
                >
                  <span class="record-layout__sections__item__layout__field__label">
                    {{ $t(f.label) }}:
                  </span>
                  <FieldRenderer
                    :field="getField(f)"
                    :model-value="form[f.name]"
                    mode="edit"
                    :related_label="form[f.name + '__label'] ?? null"
                    :module-color="accentColor"
                    :icon="getIcon(getField(f).related_module)"
                    :has-error="hasError(f)"
                    @update:model-value="(val) => updateField(f.name, val)"
                    @open-link-overlay="openFieldOverlay"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <RecordSelectorDrawer
        :open="fieldOverlayOpen"
        :search-endpoint="
          activeField ? `/relatedfield/search/${activeField.related_module}` : ''
        "
        :related-module="activeField?.related_module"
        :icon="getIcon(activeField?.related_module || null)"
        :layout="getLinkingLayout(activeField?.related_module || null)"
        :selected-record="form[activeField?.name]"
        :active-field="activeField"
        :fields="fields"
        @select="onFieldRecordSelect"
        @close="
          fieldOverlayOpen = false;
          activeField = null;
        "
      />
    </div>
  </Transition>
</template>
