<script setup>
import { getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error } = useAlerts();
const { confirm } = useConfirm();
const page = usePage();
const appSettings = page.props.appSettings;

const props = defineProps({
  transformations: Array,
});
console.log(props.transformations);
const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.transformations") },
];

const handleDelete = async (transformation) => {
  const confirmed = await confirm({
    title: t("globals.transformations.messages.delete_confirm_title"),
    message: t("globals.transformations.messages.delete_confirm", {
      name: transformation.name,
    }),
    highlight: transformation.name,
    danger: true,
  });
  if (!confirmed) return;

  router.delete(`/settings/transformations/${transformation.id}`, {
    preserveScroll: true,
    onError: () => error(t("globals.transformations.messages.delete_error")),
  });
};

const handleToggle = (transformation) => {
  router.patch(
    `/settings/transformations/${transformation.id}/toggle`,
    {},
    {
      preserveScroll: true,
      onError: () => error(t("globals.transformations.messages.save_error")),
    },
  );
};

const getModuleLabelBySlug = (slug) => {
  return page.props.modules.find((e) => e.slug === slug)?.label || null;
};
</script>

<template>
  <Head>
    <title>{{ $t("globals.transformations.labels.page_title") }}</title>
  </Head>

  <div
    class="settings transformations"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--module-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>

    <div class="pdf-templates__header">
      <div class="pdf-templates__header__details">
        <span class="pdf-templates__header__details__title">
          {{ $t("globals.transformations.labels.title") }}
        </span>
      </div>

      <div class="pdf-templates__header__actions">
        <Link href="/settings/transformations/create">
          <i class="fa-solid fa-plus"></i>
        </Link>
      </div>
    </div>

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <thead>
          <tr>
            <th>{{ $t("globals.transformations.labels.name_column") }}</th>
            <th>{{ $t("globals.transformations.labels.modules_column") }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template v-if="transformations.length === 0">
            <tr>
              <td colspan="3" class="pdf-templates__empty">
                {{ $t("globals.transformations.messages.no_transformations") }}
              </td>
            </tr>
          </template>

          <tr
            v-for="tr in transformations"
            :key="tr.id"
            class="transformations-row"
          >
            <td>
              <div class="pdf-templates__cell-name">
                <i
                  class="fa-solid"
                  :class="tr.target_icon || 'fa-arrow-right-arrow-left'"
                ></i>
                <span>{{ tr.name }}</span>
                <span class="pdf-templates__badge" v-if="!tr.enabled">
                  {{ $t("globals.transformations.labels.disabled_badge") }}
                </span>
              </div>
            </td>
            <td>
              <span
                >{{ getModuleLabelBySlug(tr.source_module) }}
                <i class="fa-solid fa-arrow-right-long"></i>
                {{ getModuleLabelBySlug(tr.target_module) }}</span
              >
            </td>
            <td class="row-actions">
              <Link
                class="row-action-btn row-action-btn--resend"
                :href="`/settings/transformations/${tr.id}`"
              >
                <i class="fa-solid fa-pen-to-square"></i>
                {{ $t("globals.transformations.buttons.edit_btn") }}
              </Link>
              <button
                class="row-action-btn row-action-btn--revoke"
                @click="handleToggle(tr)"
              >
                <i class="fa-solid fa-power-off"></i>
                {{
                  tr.enabled
                    ? $t("globals.transformations.buttons.disable_btn")
                    : $t("globals.transformations.buttons.enable_btn")
                }}
              </button>
              <button
                class="row-action-btn row-action-btn--delete"
                @click="handleDelete(tr)"
              >
                <i class="fa-solid fa-trash"></i>
                {{ $t("globals.transformations.buttons.delete_btn") }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
