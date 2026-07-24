<script setup>
import { computed, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import Checkbox from "../Components/FiledTypes/Checkbox.vue";
import SettingsBreadcrumb from "../Components/Settings/SettingsBreadcrumb.vue";

const { success, error, info, clearAllAlerts } = useAlerts();

defineOptions({
  layout: [AppLayout, SettingsLayout],
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  item: Object,
  values: Array,
});

const crumbs = computed(() => [
  { label: t("settings.label"), href: "/settings" },
  { label: t(props.item.label) },
]);

const normalizedValues = props.values.map((v) => ({
  ...v,
  value: v.value == 1 || v.value === "1",
}));

const form = useForm({
  values: normalizedValues,
});

const notificationTypeKeys = computed(() => {
  const types = [];
  form.values.forEach((v) => {
    const type = v.key.startsWith("notify_email_")
      ? v.key.slice("notify_email_".length)
      : v.key.startsWith("notify_inapp_")
        ? v.key.slice("notify_inapp_".length)
        : null;
    if (type && !types.includes(type)) types.push(type);
  });
  return types;
});

const indexForKey = (key) => form.values.findIndex((v) => v.key === key);

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

const resetForm = () => {
  form.reset();
};
const isDirty = () => form.isDirty;
</script>

<template>
  <Head>
    <title>{{ $t(item.label) }} - {{ $t("settings.label") }} - Cubrel</title>
  </Head>

  <div class="settings">
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>

    <div class="settings__system">
      <form @submit.prevent="saveSetting">
        <div class="settings__notifications">
          <div class="settings__notifications__header">
            <span class="settings__notifications__header__label"></span>
            <span class="settings__notifications__header__col">
              <i class="fa-solid fa-envelope"></i>
              {{ $t("preferences.notifications_email_column") }}
            </span>
            <span class="settings__notifications__header__col">
              <i class="fa-solid fa-bell"></i>
              {{ $t("preferences.notifications_inapp_column") }}
            </span>
          </div>

          <div
            v-for="type in notificationTypeKeys"
            :key="type"
            class="settings__notifications__row"
          >
            <div class="settings__notifications__row__label">
              <label>{{ $t(`preferences.notification_types.${type}`) }}</label>
            </div>

            <div class="settings__notifications__row__col">
              <Checkbox
                v-model="form.values[indexForKey(`notify_email_${type}`)].value"
              />
            </div>

            <div class="settings__notifications__row__col">
              <Checkbox
                v-model="form.values[indexForKey(`notify_inapp_${type}`)].value"
              />
            </div>
          </div>
        </div>

        <div class="settings__actions">
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
