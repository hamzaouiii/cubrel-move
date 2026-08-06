<script setup>
import { getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import { formatDateTime } from "@/utils/datetime";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error } = useAlerts();
const { confirm } = useConfirm();
const appSettings = usePage().props.appSettings;

const props = defineProps({
  tokens: Array,
});

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.api_tokens") },
];

const abilitiesLabel = (token) =>
  token.abilities.includes("*")
    ? t("globals.api_tokens.labels.full_access")
    : t("globals.api_tokens.labels.selective");

const handleDelete = async (token) => {
  const confirmed = await confirm({
    title: t("globals.api_tokens.messages.delete_confirm_title"),
    message: t("globals.api_tokens.messages.delete_confirm", { name: token.name }),
    highlight: token.name,
    danger: true,
  });
  if (!confirmed) return;

  router.delete(`/settings/api-tokens/${token.id}`, {
    preserveScroll: true,
    onError: () => error(t("globals.api_tokens.messages.delete_error")),
  });
};
</script>

<template>
  <Head>
    <title>{{ $t("globals.api_tokens.labels.page_title") }}</title>
  </Head>

  <div
    class="settings api-tokens"
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
          {{ $t("globals.api_tokens.labels.title") }}
        </span>
      </div>

      <div class="pdf-templates__header__actions">
        <Link href="/settings/api-tokens/create">
          <i class="fa-solid fa-plus"></i>
        </Link>
      </div>
    </div>

    <p class="api-tokens__hint">{{ $t("globals.api_tokens.hints.intro") }}</p>

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <thead>
          <tr>
            <th>{{ $t("globals.api_tokens.labels.name_column") }}</th>
            <th>{{ $t("globals.api_tokens.labels.owner_column") }}</th>
            <th>{{ $t("globals.api_tokens.labels.abilities_column") }}</th>
            <th>{{ $t("globals.api_tokens.labels.last_used_column") }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template v-if="tokens.length === 0">
            <tr>
              <td colspan="5" class="pdf-templates__empty">
                {{ $t("globals.api_tokens.messages.no_tokens") }}
              </td>
            </tr>
          </template>

          <tr v-for="token in tokens" :key="token.id">
            <td>
              <div class="pdf-templates__cell-name">
                <i class="fa-solid fa-key pdf-templates__icon--active"></i>
                <span>{{ token.name }}</span>
              </div>
            </td>
            <td>{{ token.owner_name }} ({{ token.owner_email }})</td>
            <td>{{ abilitiesLabel(token) }}</td>
            <td>{{ token.last_used_at ? formatDateTime(token.last_used_at, appSettings) : "—" }}</td>
            <td class="row-actions">
              <button
                class="row-action-btn row-action-btn--delete"
                @click="handleDelete(token)"
              >
                <i class="fa-solid fa-ban"></i>
                {{ $t("globals.api_tokens.buttons.delete_btn") }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
