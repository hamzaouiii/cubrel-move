<script setup>
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
import {
  computed,
  ref,
  getCurrentInstance,
  onBeforeUnmount,
  onMounted,
} from "vue";
import Layout from "@/Layouts/Layout.vue";
import DropdownBreadcrumbs from "@/Pages/Components/Settings/Dropdowns/DropdownBreadcrumbs.vue";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import { useAlerts } from "@/Composables/useAlerts";

const { error, warning, success, info, clearAllAlerts } = useAlerts();
defineOptions({
  layout: Layout,
});

const props = defineProps({
  dropdown: Object,
  item: Object,
});
const appSettings = usePage().props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const newItem = useForm({
  label: "",
  value: "",
});

const form = useForm({
  key: props.dropdown?.key || "",
  values: props.dropdown?.values || {},
});
const generatedSystemvalue = (label) => {
  if (!label) return "";
  const value = label
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^-+|-+$/g, "");

  return value;
};

const rowIsDirty = computed(() => {
  return newItem.label.length >= 3;
});

const valueExistsError = ref(false);

const addItem = () => {
  if (!newItem.isDirty) return;
  console.log(form.values);
  if (
    form.values.some(
      (item) => item.value === generatedSystemvalue(newItem.label),
    )
  ) {
    error("Value Already Exists");
    valueExistsError.value = true;
    return;
  }
  form.values.push({
    label: newItem.label,
    value: generatedSystemvalue(newItem.label),
  });
  newItem.reset();
  valueExistsError.value = false;
};

const deleteItem = (value) => {
  form.values = form.values.filter((i) => i.value != value);
};

const listIsDirty = computed(() => {
  return form.isDirty;
});

const resetList = () => {
  warning("Resetting List to original values ");
  form.reset();
};

const saveList = () => {
  if (form.isDirty) {
    info(t("modules.actions.saving"));
    form.put("/settings/dropdowns/" + props.dropdown.key, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("settings.dropdown.update_success"));
      },
      onError: (e) => {
        clearAllAlerts();
        error(t("settings.dropdown.save_error"));
        console.error(e);
      },
    });
  }
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    saveList();
  }
}

onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});

useUnsavedChangesGuard({
  getIsDirty: () => form.isDirty,
});
</script>

<template>
  <Head>
    <title>
      {{ $t("settings.items.dropdowns") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <DropdownBreadcrumbs
          :setting-module="item"
          :current="t('settings.dropdown.edit')"
        ></DropdownBreadcrumbs>
      </div>
    </div>

    <div class="settings__dropdown">
      <div class="settings__dropdown__edit">
        <div class="settings__dropdown__edit__header">
          <ul class="settings__dropdown__edit__header__info">
            <li class="settings__dropdown__edit__header__info__data">
              <span class="settings__dropdown__edit__header__info__data__label"
                >{{ $t("settings.dropdown.list_name") }}:</span
              >
              <span
                class="settings__dropdown__edit__header__info__data__value"
                >{{ dropdown.key }}</span
              >
            </li>
            <li
              class="settings__dropdown__edit__header__info__data"
              v-if="dropdown.field_key"
            >
              <span class="settings__dropdown__edit__header__info__data__label"
                >{{ $t("settings.dropdown.related_field") }}:
              </span>
              <span
                class="settings__dropdown__edit__header__info__data__value"
                >{{ dropdown.field_key }}</span
              >
            </li>
            <li class="settings__dropdown__edit__header__info__indicator">
              <span>{{ $t("settings.dropdown.display_label") }}</span>
              <span>{{ $t("settings.dropdown.value") }}</span>
              <div></div>
            </li>
          </ul>
        </div>
        <ul>
          <li v-for="l in form.values" class="settings__dropdown__edit__value">
            <div class="settings__dropdown__edit__value__item">
              <span>{{ $t(l.label) }}</span>
            </div>

            <div class="settings__dropdown__edit__value__item">
              <span>{{ $t(l.value) }}</span>
            </div>
            <div class="settings__dropdown__edit__value__actions">
              <span
                class="settings__dropdown__edit__value__actions__delete"
                @click="deleteItem(l.value)"
              >
                <i class="fa-solid fa-trash-can"></i>
                <i v-if="false" class="fa-solid fa-check"></i>
              </span>
            </div>
          </li>
          <li class="settings__dropdown__edit__value">
            <div class="settings__dropdown__edit__value__item">
              <input
                type="text"
                v-model="newItem.label"
                @keyup.enter="addItem"
              />
            </div>
            <div class="settings__dropdown__edit__value__item">
              <input
                type="text"
                :value="generatedSystemvalue(newItem.label)"
                :class="{ error: valueExistsError }"
                readonly
                disabled
              />
            </div>
            <div class="settings__dropdown__edit__value__actions">
              <span
                class="settings__dropdown__edit__value__actions__add"
                @click="addItem()"
                :class="{ disabled: !rowIsDirty }"
              >
                <i class="fa-solid fa-plus"></i>
              </span>
            </div>
          </li>
        </ul>
        <div class="settings__dropdown__edit__actions">
          <button
            type="button"
            class="settings__dropdown__edit__actions__reset btn"
            :disabled="!listIsDirty"
            @click="resetList()"
          >
            {{ $t("settings.reset") }}
          </button>

          <button
            type="submit"
            class="settings__dropdown__edit__actions__save btn"
            :disabled="!listIsDirty"
            @click="saveList()"
          >
            {{ $t("settings.save") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
