<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed, getCurrentInstance } from "vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  metadata: Object,
  types: Array,
  typeList: Object,
  moduleList: Object,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;
const default_values = {
  name: "",
  label: "",
  right_module: "",
  type: "",
};

const form = useForm({ ...default_values });
const relationshipUrl = () => {
  const url = page.url;
  const segments = url.split("/").filter(Boolean);
  if (segments.at(-1) === "create") {
    segments.pop();
  }
  return "/" + segments.join("/");
};

const moduleColor = () =>
  appSettings.use_individual_module_colors
    ? props.module.color
    : appSettings.primary_color;
const mapper = {
  name: "text",
  label: "text",
  right_module: "select",
  type: "select",
};

const getField = (field) => {
  if (mapper[field] === "select") {
    return {
      name: field,
      type: mapper[field],
      dropdown_list: getList(field),
    };
  }
  return {
    name: field,
    type: mapper[field],
  };
};
const getList = (field) => {
  return field === "type" ? props.typeList : props.moduleList;
};
const getRelationshipName = computed(() => {
  if (form.right_module?.length) {
    return `${props.module.slug}_${form.right_module}`;
  }
  return "";
});
</script>
<template>
  <Head>
    <title>
      {{ $t("relationships.create_new") }} - {{ module.label }} -
      {{ $t("relationships.label") }} -
      {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="[
      { '--module-color': moduleColor() },
      { '--danger-color': appSettings.danger_color },
    ]"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="module"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>
    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="module"
        active-key="relationships"
      ></ModuleSettingTabs>
      <div class="settings__module__header">
        <Link :href="relationshipUrl()">
          <i class="fa-solid fa-arrow-left"></i>
          {{ $t("relationships.back_to_list") }}
        </Link>
      </div>
      <div class="settings__module__edit">
        <form @submit.prevent="saveRelationship">
          <div
            class="settings__module__edit__element"
            v-for="fieldName in metadata"
            :key="fieldName"
          >
            <label class="settings__module__edit__element__label">
              {{ $t("relationships.metadata." + fieldName) }}
            </label>
            <template v-if="fieldName === 'name'">
              <FieldRenderer
                v-model="getRelationshipName"
                :type="mapper[fieldName]"
                :field="getField(fieldName)"
                mode="settings"
                :read-only="true"
              />
            </template>
            <template v-else>
              <FieldRenderer
                v-model="form[fieldName]"
                :type="mapper[fieldName]"
                :field="getField(fieldName)"
                mode="settings"
                :read-only="fieldName === 'name'"
              />
            </template>
          </div>
          <div class="settings__module__edit__actions">
            <button
              class="settings__module__edit__actions__reset btn"
              @click="resetForm"
              :disabled="!form.isDirty"
              type="button"
            >
              {{ $t("settings.cancel") }}
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
  </div>
</template>
