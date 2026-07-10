<script setup>
import axios from "axios";
import { usePage, useForm } from "@inertiajs/vue3";
import {
  getCurrentInstance,
  computed,
  watch,
  ref,
  onMounted,
  onBeforeUnmount,
  toRef,
} from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import { useFieldRules } from "@/Composables/useFieldRules";

import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";
import DropdownSelector from "@/Pages/Components/Settings/Dropdowns/DropdownSelector.vue";
import CreateNewDropdownListModal from "@/Pages/Components/Settings/Dropdowns/CreateNewDropdownListModal.vue";
import EditDropdownListModal from "@/Pages/Components/Settings/Dropdowns/EditDropdownListModal.vue";

const { error, info, clearAllAlerts } = useAlerts();

const props = defineProps({
  module: Object,
  field_types: Array,
  metadata: Object,
  field: Object,
  color: String,
});

const page = usePage();
const appSettings = page.props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const selected_dropdown_list = ref(props.field.dropdown_list_id);
const DropDownListOptions = ref([]);

const form = useForm({ ...props.field });

/**
 * COMPOSABLE INTEGRATION
 * Using the centralized rules and UI helpers
 */
const {
  visibleMetadata,
  applyRules,
  isCheckbox,
  isDropDown,
  isReadonly,
  isDisplayLabel,
  isRegex,
  hasDropdownOptions,
} = useFieldRules(form, toRef(props, "metadata"));

const generatedName = computed(() => {
  if (!form.label) return "";

  const name = form.label
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_+|_+$/g, "");

  return name;
});

const generatedKey = computed(() => {
  return props.module.slug + "_" + generatedName.value;
});

// Watchers
watch(
  () => form.type,
  (newType) => {
    applyRules(newType);
  },
);

watch(
  () => form.label,
  (newName) => {
    if (newName) {
      form.name = generatedName.value;
    }
  },
  { immediate: true },
);

watch(
  () => form.name,
  (newName) => {
    if (newName) {
      form.key = generatedKey.value;
    }
  },
  { immediate: true },
);

// UI Helpers
const typesList = () => {
  return props.field_types.map((type) => ({
    value: type,
    label: t(`fields.types.${type}`),
  }));
};

// Form Actions
const saveField = () => {
  info(t("settings.saving"));
  const url = "/settings/modulebuilder/" + props.module.id + "/field";

  if (!form.key && form.name) {
    form.key = generatedKey.value;
    form.name = generatedName.value;
  }
  if (hasDropdownOptions(form.type)) {
    form.dropdown_list = selected_dropdown_list.value;
  } else {
    form.dropdown_list = null;
  }

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      initialDropdown.value = selected_dropdown_list.value;
      form.defaults();
      emit("saved");
    },
    onError: (Error) => {
      clearAllAlerts();
      if (Error.table_missing) {
        error(Error.table_missing);
      } else {
        for (const [key, value] of Object.entries(Error)) {
          error(value);
        }
      }
    },
  });
};
const initialDropdown = ref(props.field.dropdown_list_id);

const isDirty = computed(() => {
  return form.isDirty || selected_dropdown_list.value !== initialDropdown.value;
});

const displayKeyError = () => {
  return form.errors.key || form.errors.name;
};

// Dropdown Modal Logic
const openCreateDialog = () => (showCreateDialog.value = true);
const closeCreateDialog = () => (showCreateDialog.value = false);
const openEditDialog = () => {
  if (!selected_dropdown_list.value) return;
  showEditDialog.value = true;
};
const closeEditDialog = () => (showEditDialog.value = false);

const fetchDrodownList = async () => {
  try {
    const { data } = await axios.get("/api/dropdown-lists", {});
    DropDownListOptions.value = data.list;
  } catch (error) {
    console.error(t("settings.dropdown_list_fetch_failed"), error);
  }
};

const assignList = (value) => {
  DropDownListOptions.value.push(value);
  selected_dropdown_list.value = value.id;
};

const getDropdonwItem = (id) => {
  return DropDownListOptions.value.find((e) => e.id === id);
};

onMounted(() => {
  fetchDrodownList();
  window.addEventListener("keydown", handleKeydown);
});

useUnsavedChangesGuard({
  getIsDirty: () => isDirty.value,
});

const emit = defineEmits(["onCloseModal", "saved"]);

const closeModalClicked = () => {
  emit("onCloseModal");
};
onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});
const handleKeydown = (e) => {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();

    if (isDirty.value) {
      saveField();
    }
  }
};
</script>

<template>
  <div class="dropdown-list-modal">
    <div class="dropdown-list-modal__close" @click="closeModalClicked">
      <i class="fa-solid fa-xmark"></i>
    </div>
    <div class="dropdown-list-modal__container">
      <div
        class="settings"
        :style="
          ({ '--primary-color': appSettings.primary_color },
          { '--module-color': color })
        "
      >
        <div class="settings__module">
          <div class="settings__module__edit">
            <form
              class="settings__module__edit__form"
              @submit.prevent="saveField"
            >
              <div
                class="settings__module__edit__element"
                v-for="fieldName in visibleMetadata"
                :key="fieldName"
              >
                <label class="settings__module__edit__element__label">
                  {{ $t("fields.metadata." + fieldName) }}
                </label>
                <div class="settings__module__edit__element__content">
                  <template v-if="isReadonly(fieldName)">
                    <input
                      type="text"
                      :name="fieldName"
                      v-model="form[fieldName]"
                      disabled
                      :class="{
                        'settings__module__edit__element--error-field':
                          displayKeyError(),
                      }"
                    />
                    <span
                      v-if="displayKeyError()"
                      class="settings__module__edit__element__error"
                    >
                      {{ $t("fields.key_is_taken_error") }}
                    </span>
                  </template>

                  <template v-else-if="isCheckbox(fieldName)">
                    <Checkbox v-model="form[fieldName]"></Checkbox>
                    <span
                      v-if="form.errors[fieldName]"
                      class="settings__module__edit__element__error"
                    >
                      {{ form.errors[fieldName] }}
                    </span>
                  </template>

                  <template v-else-if="isDropDown(fieldName)">
                    <DropdownField
                      v-model="form[fieldName]"
                      :options="typesList()"
                    ></DropdownField>

                    <transition name="dropdown-fade">
                      <div
                        class="dropdown-selector"
                        v-if="hasDropdownOptions(form[fieldName])"
                      >
                        <DropdownSelector
                          v-model="selected_dropdown_list"
                          :options="DropDownListOptions"
                          @onOpenCreateDialog="openCreateDialog"
                          @onOpenEditDialog="openEditDialog"
                          :is-draft="form.type !== 'status'"
                        />
                      </div>
                    </transition>

                    <span
                      v-if="form.errors[fieldName]"
                      class="settings__module__edit__element__error"
                    >
                      {{ form.errors[fieldName] }}
                    </span>
                  </template>

                  <template v-else-if="isDisplayLabel(fieldName)">
                    <span>
                      <input
                        type="text"
                        v-model="form[fieldName]"
                        :name="fieldName"
                        :class="{
                          'settings__module__edit__element--error-field':
                            form.errors[fieldName],
                        }"
                      />
                      <span class="settings__module__edit__element__hint">
                        <i class="fa-solid fa-lightbulb"></i>
                        {{ $t("fields.label_hint") }}
                      </span>
                    </span>
                    <span
                      v-if="form.errors[fieldName]"
                      class="settings__module__edit__element__error"
                    >
                      {{ form.errors[fieldName] }}
                    </span>
                  </template>

                  <template v-else-if="isRegex(fieldName)">
                    <span>
                      <input
                        type="text"
                        v-model="form[fieldName]"
                        :name="fieldName"
                        :class="{
                          'settings__module__edit__element--error-field':
                            form.errors[fieldName],
                        }"
                      />
                      <span class="settings__module__edit__element__hint">
                        <i class="fa-solid fa-lightbulb"></i>
                        {{ $t("fields.regex_hint") }}
                      </span>
                    </span>
                    <span
                      v-if="form.errors[fieldName]"
                      class="settings__module__edit__element__error"
                    >
                      {{ form.errors[fieldName] }}
                    </span>
                  </template>
                  <template v-else>
                    <input
                      type="text"
                      v-model="form[fieldName]"
                      :name="fieldName"
                      :class="{
                        'settings__module__edit__element--error-field':
                          form.errors[fieldName],
                      }"
                    />
                    <span
                      v-if="form.errors[fieldName]"
                      class="settings__module__edit__element__error"
                    >
                      {{ form.errors[fieldName] }}
                    </span>
                  </template>
                </div>
              </div>

              <div class="settings__actions">
                <button
                  type="submit"
                  class="settings__actions__save"
                  :disabled="!isDirty"
                >
                  {{ $t("settings.save") }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <CreateNewDropdownListModal
        @onCloseModal="closeCreateDialog"
        @listCreated="assignList"
        :is-draft="true"
        :is-status="form.type === 'status'"
        :module-slug="module.slug"
        :field-label="t(form.label)"
        v-if="showCreateDialog"
      />

      <EditDropdownListModal
        v-if="showEditDialog"
        :dropdown="getDropdonwItem(selected_dropdown_list)"
        :is-status="form.type === 'status'"
        @onCloseModal="closeEditDialog"
      />
    </div>
  </div>
</template>
