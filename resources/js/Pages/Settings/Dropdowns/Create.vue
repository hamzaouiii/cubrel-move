<script setup>
import { Head, usePage, useForm } from "@inertiajs/vue3";
import { ref, computed, getCurrentInstance } from "vue";
import Layout from "@/Layouts/Layout.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import { useAlerts } from "@/Composables/useAlerts";

const { error, info, success, clearAllAlerts } = useAlerts();
defineOptions({
  layout: Layout,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  item: Object,
});
const appSettings = usePage().props.appSettings;

const form = useForm({
  name: "dsa",
  field: "das",
  module: "das",
  json: {},
});
let listItems = ref([]);
let newItem = ref({ label: "", value: "" });

const rowIsDirty = computed(() => {
  return newItem.value.value.length >= 3 && newItem.value.label.length >= 3;
});

const valueExistsError = ref(false);
// const isEditing = ref([]);

const addItem = () => {
  if (!rowIsDirty.value) {
    console.log("row is not dirty");
    return;
  }
  if (listItems.value.some((item) => item.value === newItem.value)) {
    error("Value Already Exists");
    valueExistsError.value = true;
    return;
  }
  listItems.value.push({
    label: newItem.value.label,
    value: newItem.value.value,
  });

  newItem.value.value = "";
  newItem.value.label = "";
};
const deleteItem = (value) => {
  listItems.value = listItems.value.filter((i) => i.value != value);
};

const listIsDirty = computed(() => {
  return listItems.value.length && form.name.length;
});

const saveList = () => {
  form.json = JSON.stringify(listItems.value);
  info(t("modules.actions.saving"));

  form.post("/settings/dropdowns", {
    onSuccess: () => {
      clearAllAlerts();
      success(t("modules.actions.save_success"));
    },
    onError: () => {
      clearAllAlerts();
      error(t("modules.actions.save_error") + form.errors);
    },
  });
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
        <form class="dropdown-form" action="" method="post">
          <div class="dropdown-form__item">
            <span class="dropdown-form__item__label"
              ><label for="name">Name</label></span
            >
            <div class="dropdown-form__item__field dropdown-form__item--prefix">
              <input type="text" v-model="form.name" maxlength="25" />
              <span class="prefix">_list</span>
            </div>
          </div>
          <div class="dropdown-form__item">
            <span class="dropdown-form__item__label"
              ><label for="name">Module</label></span
            >
            <span class="dropdown-form__item__field">
              <input type="text" v-model="form.module" />
            </span>
          </div>
          <div class="dropdown-form__item">
            <span class="dropdown-form__item__label"
              ><label for="name">Field</label></span
            >
            <span class="dropdown-form__item__field">
              <input type="text" v-model="form.field" />
            </span>
          </div>
        </form>

        <div class="settings__dropdown__edit__header">
          <ul class="settings__dropdown__edit__header__info">
            <li class="settings__dropdown__edit__header__info__indicator">
              <span>{{ $t("settings.dropdown.display_label") }}</span>
              <span>{{ $t("settings.dropdown.value") }}</span>
              <div></div>
            </li>
          </ul>
        </div>
        <ul>
          <li v-for="l in listItems" class="settings__dropdown__edit__value">
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
              <input type="text" v-model="newItem.value" />
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
