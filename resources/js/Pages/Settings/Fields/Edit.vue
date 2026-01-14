<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, usePage, useForm } from "@inertiajs/vue3";
import { computed, getCurrentInstance } from "vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import Checkbox from "@/Pages/Components/Settings/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/Settings/FiledTypes/DropdownField.vue";
defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  metadata: Object,
  item: Object,
  field_types: Array,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const page = usePage();
const appSettings = page.props.appSettings;

const form = useForm({
  name: props.metadata.name,
  type: props.metadata.type,
  label: t(props.metadata.label),
  readonly: props.metadata.readonly,
  hidden: props.metadata.hidden,
  nullable: props.metadata.nullable,
  required: props.metadata.required,
  searchable: props.metadata.searchable,
  filterable: props.metadata.filterable,
  sortable: props.metadata.sortable,
  default_value: props.metadata.default_value,
  options: props.metadata.options,
  min_length: props.metadata.min_length,
  max_length: props.metadata.max_length,
  regex: props.metadata.regex,
});
const isCheckbox = (field) => {
  const map = [
    "readonly",
    "hidden",
    "nullable",
    "required",
    "searchable",
    "filterable",
    "sortable",
  ];
  return map.includes(field);
};

const isDropDown = (field) => {
  const map = ["type"];
  return map.includes(field);
};

const isReadonly = (field) => {
  const map = ["type", "name"];
  return map.includes(field);
};

const typesList = () => {
  const types = [...props.field_types];

  return types.map((type) => ({
    value: type,
    label: t(`fields.types.${type}`),
  }));
};

const fieldsUrl = () => {
  const key = props.metadata?.name || "";
  const url = page.url;
  const segments = url.split("/").filter(Boolean);
  if (segments.at(-1) === key) {
    segments.pop();
  }
  const u = ("/" + segments.join("/")).toString();
  return u;
};

const saveField = () => {};
</script>

<template>
  <Head>
    <title>
      {{ $t(metadata.label) }} - {{ module.label }} - {{ $t("fields.label") }} -
      {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="module"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>
    <ModuleSettingTabs
      :setting-module="module"
      active-key="fields"
    ></ModuleSettingTabs>
    <div class="settings__module__header">
      <Link :href="fieldsUrl()">
        <i class="fa-solid fa-arrow-left"></i>
        {{ $t("fields.back_to_list") }}</Link
      >
    </div>
    <div class="settings__module__edit">
      <form @submit.prevent="saveSetting">
        <div
          v-for="(i, index) in metadata"
          class="settings__module__edit__element"
        >
          <label>{{ $t("fields.metadata." + index) }} </label>

          <template v-if="isReadonly(index)">
            <input type="text" v-model="form[index]" disabled />
          </template>
          <template v-else-if="isCheckbox(index)">
            <Checkbox v-model="form[index]"></Checkbox>
          </template>
          <template v-else-if="isDropDown(index)">
            <DropdownField
              v-model="form[index]"
              :options="typesList()"
            ></DropdownField>
          </template>
          <template v-else>
            <input type="text" v-model="form[index]" />
          </template>
        </div>
        <div class="settings__module__edit__actions">
          <button
            type="button"
            class="settings__module__edit__actions__reset btn"
            @click="resetForm"
            :disabled="!form.isDirty"
          >
            {{ $t("settings.reset") }}
          </button>

          <button
            type="submit"
            class="settings__module__edit__actions__save btn"
            :disabled="!form.isDirty"
          >
            {{ $t("settings.save") }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
