<script setup>
import { computed, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, usePage, Link, useForm, router } from "@inertiajs/vue3";
import DropdownField from "../Components/FiledTypes/SettingDropdownField.vue";
import Switcher from "../Components/FiledTypes/Switcher.vue";
import { useAlerts } from "@/Composables/useAlerts";
import Checkbox from "../Components/FiledTypes/Checkbox.vue";
import ColorPicker from "../Components/FiledTypes/ColorPicker.vue";
import SettingsLink from "../Components/Settings/SettingsLink.vue";
import ExplainTip from "../Components/Globals/ExplainTip.vue";
import ImageField from "../Components/FiledTypes/ImageField.vue";
const { success, error, info, clearAllAlerts } = useAlerts();

defineOptions({
  layout: AppLayout,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  item: Object,
  values: Object,
  dateFormatOptions: { type: Array, default: [] },
  datetimeFormatOptions: { type: Array, default: [] },
  timezoneOptions: { type: Array, default: [] },
  currencyOptions: { type: Array, default: [] },
});

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
  if (type === "date") return "date";
  if (type === "datetime") return "datetime";
  if (type === "timezone") return "timezone";
  if (type === "currency") return "currency";
  if (type === "image") return "image";
  return "text";
};

const saveSetting = () => {
  clearAllAlerts();
  info(t("settings.saving"));
  form.put(`/settings/${props.item.slug}`, {
    preserveScroll: true,
    onSuccess: () => {
      window.location.reload();

      clearAllAlerts();
      success(t("settings.setting_update_success"));
    },
    onError: () => {
      clearAllAlerts();
      error(t("settings.setting_update_error"));
    },
  });
};

const getColorModel = computed(() => {
  const item = form.values.find((e) => e.key === "primary_color");
  return item?.value || appSettings.primary_color;
});

const resetForm = () => {
  form.reset();
};
const isDirty = () => form.isDirty;
</script>

<template>
  <Head>
    <title>{{ $t(item.label) }} - {{ $t("settings.label") }} - Cubrel</title>
  </Head>

  <div class="settings" :style="{ '--primary-color': getColorModel }">
    <SettingsLink :color="getColorModel" />
    <div class="settings__system">
      <form @submit.prevent="saveSetting" class="settings__system__form">
        <div
          v-for="(i, index) in form.values"
          :key="i.id || i.key || index"
          class="settings__system__form__field"
        >
          <label v-if="inputTypeFor(i.type) === 'currency'">
            {{ $t(i.label) }}
            <ExplainTip :text="$t('settings.currency_hint')"></ExplainTip>
          </label>
          <label v-else> {{ $t(i.label) }}</label>
          <div class="settings__system__form__field__content">
            <template v-if="i.type === 'bool'">
              <Checkbox
                v-model="form.values[index].value"
                :module-color="getColorModel"
              ></Checkbox>
            </template>

            <template v-else-if="inputTypeFor(i.type) === 'datetime'">
              <DropdownField
                v-model="form.values[index].value"
                :options="datetimeFormatOptions"
              />
            </template>

            <template v-else-if="inputTypeFor(i.type) === 'date'">
              <DropdownField
                v-model="form.values[index].value"
                :options="dateFormatOptions"
              />
            </template>
            <template v-else-if="inputTypeFor(i.type) === 'timezone'">
              <DropdownField
                v-model="form.values[index].value"
                :options="timezoneOptions"
              />
            </template>
            <template v-else-if="inputTypeFor(i.type) === 'currency'">
              <DropdownField
                v-model="form.values[index].value"
                :options="currencyOptions"
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
            <template v-else-if="inputTypeFor(i.type) === 'color'">
              <ColorPicker v-model="form.values[index].value"></ColorPicker>
            </template>
            <template v-else-if="inputTypeFor(i.type) === 'image'">
              <ImageField v-model="form.values[index].value" mode="edit" size="lg" />
            </template>
            <template v-else>
              <input
                :type="inputTypeFor(i.type)"
                v-model="form.values[index].value"
              />
            </template>
          </div>
        </div>

        <div
          class="settings__actions"
          :style="
            ({ '--primary-color': getColorModel },
            { '--module-color': getColorModel })
          "
        >
          <button
            type="button"
            class="settings__actions__reset"
            @click="resetForm"
            :disabled="!isDirty()"
          >
            {{ $t("settings.reset") }}
          </button>

          <button
            class="settings__actions__save"
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
