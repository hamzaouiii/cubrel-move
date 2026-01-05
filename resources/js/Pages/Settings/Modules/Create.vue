<script setup>
import { computed, getCurrentInstance } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import IconPicker from "@/Pages/Components/Settings/IconPicker.vue";
import Checkbox from "@/Pages/Components/Settings/FiledTypes/Checkbox.vue";
import { useAlerts } from "@/Composables/useAlerts";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";

const appSettings = usePage().props.appSettings;

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const { success, error, info, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});

const defaultValues = {
  display_label: "",
  label: "",
  icon: "",
  color: "#0d6efd",
  show_in_sidebar: true,
  description: "",
  slug: "",
};

const form = useForm({ ...defaultValues });

const isDirty = computed(() => {
  return form.display_label.length >= 4;
});

const slug = computed(() => {
  return form.display_label
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
});

const resetModule = () => {
  Object.keys(defaultValues).forEach((key) => {
    form[key] = defaultValues[key];
  });
  form.clearErrors();
};

const saveModule = () => {
  info(t("settings.saving"));

  form
    .transform((data) => ({ ...data, slug: slug.value }))
    .post("/settings/modules", {
      preserveScroll: true,
      onSuccess: () => {
        form.clearErrors();
        clearAllAlerts();
        const flash = usePage().props.flash;
        if (flash?.success) {
          success(flash.success);
        } else {
          success(t("settings.module_save_success"));
        }
      },
      onError: (errors) => {
        const serverError = Object.values(errors)[0];
        clearAllAlerts();
        error(t("settings.module_save_error") + ": " + serverError);
      },
    });
};
</script>

<template>
  <Head>
    <title>
      {{ $t("settings.create_new_module") }} - {{ $t("settings.label") }}
    </title>
  </Head>

  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs></ModuleSettingBreadcrumbs>
      </div>
    </div>

    <form class="settings__create" @submit.prevent="saveModule">
      <div>
        <div class="settings__create__element">
          <label>
            {{ $t("settings.modules.display_label") }}
          </label>
          <input
            class=""
            type="text"
            name="display_label"
            v-model="form.display_label"
            :placeholder="$t('settings.modules.name_placeholder')"
          />
        </div>
        <div class="settings__create__element">
          <label>
            {{ $t("settings.modules.slug") }}
          </label>
          <input class="slug" type="text" name="slug" :value="slug" disabled />
        </div>
        <div class="settings__create__element">
          <label>
            {{ $t("settings.modules.icon") }}
          </label>
          <IconPicker v-model="form.icon" :color="form.color" />
        </div>

        <div class="settings__create__element">
          <label>
            {{ $t("settings.modules.color") }}
          </label>
          <input class="" type="color" name="color" v-model="form.color" />
        </div>

        <div class="settings__create__element">
          <label>
            {{ $t("settings.modules.show_in_sidebar") }}
          </label>
          <Checkbox
            v-model="form.show_in_sidebar"
            :module-color="form.color"
          ></Checkbox>
        </div>

        <div class="settings__create__element">
          <label>
            {{ $t("settings.modules.description") }}
          </label>
          <textarea class="" v-model="form.description"></textarea>
        </div>
      </div>

      <div
        class="settings__create__actions"
        :style="{ '--module-color': form.color }"
      >
        <button
          class="settings__create__actions__reset btn"
          type="button"
          @click="resetModule"
          v-if="isDirty"
        >
          {{ $t("settings.cancel") }}
        </button>

        <button
          class="settings__create__actions__save btn"
          type="submit"
          :disabled="!isDirty"
        >
          {{ $t("settings.save") }}
        </button>
      </div>
    </form>
  </div>
</template>
