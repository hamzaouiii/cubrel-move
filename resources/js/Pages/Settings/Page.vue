<script setup>
import { computed, getCurrentInstance } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
import DropdownField from "../Components/FiledTypes/SettingDropdownField.vue";
import Switcher from "../Components/FiledTypes/Switcher.vue";
import { useAlerts } from "@/Composables/useAlerts";
import Checkbox from "../Components/FiledTypes/Checkbox.vue";
import SettingBreadcrumbs from "../Components/Settings/SettingBreadcrumbs.vue";
const { success, error, info, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  item: Object,
  values: Object,
  datetimeFormatOptions: { type: Array, default: [] },
  timezoneOptions: { type: Array, default: [] },
});
console.log(props.datetimeFormatOptions);
const page = usePage();
const module = computed(() => page.props.item || page.props);
const appSettings = usePage().props.appSettings;

const normalizedValues = props.values.map((v) => ({
  ...v,
  value: v.type === "bool" ? v.value == 1 || v.value === "1" : v.value,
}));

const form = useForm({
  values: normalizedValues,
});

const inputTypeFor = (type) => {
  if (type === "lang_switcher") return "lang_switcher";
  if (type === "theme_switcher") return "theme_switcher";
  if (type === "string") return "text";
  if (type === "bool") return "checkbox";
  if (type === "color") return "color";
  if (type === "json") return "multiselect";
  if (type === "int") return "number";
  if (type === "datetime") return "datetime";
  if (type === "timezone") return "timezone";
  return "text";
};
const saveSetting = () => {
  clearAllAlerts();
  info(t("settings.saving"));
  form.put(`/settings/${props.item.slug}`, {
    onSuccess: () => {
      clearAllAlerts();
      success(t("settings.setting_update_success"));
    },
    onError: () => {
      clearAllAlerts();
      error(t("settings.setting_update_error"));
    },
  });
};

const resetForm = () => {
  form.reset();
};
const isDirty = () => form.isDirty;
</script>

<template>
  <Head>
    <title>{{ $t(item.label) }} - {{ $t("settings.label") }}</title>
  </Head>

  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <SettingBreadcrumbs :setting-item="item"></SettingBreadcrumbs>
      </div>
    </div>

    <div class="settings_system">
      <form @submit.prevent="saveSetting" class="settings_system_form">
        <div
          v-for="(i, index) in form.values"
          :key="i.id || i.key || index"
          class="settings_system_form_field"
        >
          <label>{{ i.label || i.key }}</label>

          <template v-if="i.type === 'bool'">
            <Checkbox v-model="form.values[index].value"></Checkbox>
          </template>

          <template v-else-if="inputTypeFor(i.type) === 'datetime'">
            <DropdownField
              v-model="form.values[index].value"
              :options="datetimeFormatOptions"
            />
          </template>
          <template v-else-if="inputTypeFor(i.type) === 'timezone'">
            <DropdownField
              v-model="form.values[index].value"
              :options="timezoneOptions"
            />
          </template>
          <template v-else-if="inputTypeFor(i.type) === 'lang_switcher'">
            <switcher
              v-model="form.values[index].value"
              :options="[
                { label: 'EN', value: 'en' },
                { label: 'DE', value: 'de' },
              ]"
            />
          </template>
          <template v-else-if="inputTypeFor(i.type) === 'theme_switcher'">
            <switcher
              v-model="form.values[index].value"
              :options="[
                { label: 'Light', value: 'light' },
                { label: 'Dark', value: 'dark' },
              ]"
            />
          </template>

          <template v-else>
            <input
              :type="inputTypeFor(i.type)"
              v-model="form.values[index].value"
            />
          </template>
        </div>

        <div class="settings_system_form_actions">
          <button
            type="button"
            class="settings_system_form_actions__reset btn"
            @click="resetForm"
            :disabled="!isDirty()"
          >
            {{ $t("settings.reset") }}
          </button>

          <button
            class="settings_system_form_actions__save btn"
            type="submit"
            :disabled="!isDirty() || form.processing"
          >
            {{ $t("settings.save") }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
