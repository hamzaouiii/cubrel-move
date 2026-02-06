<script setup>
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
import { computed, ref, toRaw } from "vue";
import Layout from "@/Layouts/Layout.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import { useAlerts } from "@/Composables/useAlerts";

const { error, warning, success } = useAlerts();
defineOptions({
  layout: Layout,
});

const props = defineProps({
  dropdown: Object,
  item: Object,
});
const appSettings = usePage().props.appSettings;

const newItem = useForm({
  label: "",
  value: "",
});
const dbListItems = computed(() => {
  return props.dropdown.values ?? [];
});

const rowIsDirty = computed(() => {
  return newItem.value.length >= 3 && newItem.label.length >= 3;
});

let ListItems = ref([...toRaw(dbListItems.value)]);
const valueExistsError = ref(false);
const isEditing = ref([]);

const addItem = () => {
  if (!newItem.isDirty) return;
  if (ListItems.value.some((item) => item.value === newItem.value)) {
    error("Value Already Exists");
    valueExistsError.value = true;
    return;
  }
  ListItems.value.push({
    label: newItem.label,
    value: newItem.value,
  });
  newItem.reset();
};
const deleteItem = (value) => {
  ListItems.value = ListItems.value.filter((i) => i.value != value);
};

const listIsDirty = computed(() => {
  const current = JSON.stringify(dbListItems.value);
  const original = JSON.stringify(ListItems.value);
  return current !== original;
});

const resetList = () => {
  warning("Resetting List to original values ");
  ListItems.value = [...toRaw(dbListItems.value)];
};
</script>

<template>
  <Head>
    <title>
      {{ $t("settings.items.dropdowns") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="item"
        ></ModuleSettingBreadcrumbs>
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
          <li v-for="l in ListItems" class="settings__dropdown__edit__value">
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
              <input type="text" v-model="newItem.label" />
            </div>
            <div class="settings__dropdown__edit__value__item">
              <input
                type="text"
                v-model="newItem.value"
                :class="{ error: valueExistsError }"
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
          >
            {{ $t("settings.save") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
