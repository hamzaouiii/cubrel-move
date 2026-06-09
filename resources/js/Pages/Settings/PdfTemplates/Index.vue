<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import SettingsLink from "@/Pages/Components/Settings/SettingsLink.vue";

defineOptions({ layout: AppLayout });

const { success, error } = useAlerts();
const appSettings = usePage().props.appSettings;

const props = defineProps({
  templates: Array,
});

const grouped = () => {
  const map = {};
  for (const t of props.templates) {
    if (!map[t.module_slug]) map[t.module_slug] = [];
    map[t.module_slug].push(t);
  }
  return map;
};

const setDefault = (id) => {
  router.post(
    `/settings/pdf-templates/${id}/default`,
    {},
    {
      preserveScroll: true,
      onSuccess: () => success("Default template updated."),
      onError: () => error("Could not update template."),
    },
  );
};

const previewUrl = (module, recordId = null) =>
  recordId ? `/${module}/${recordId}/pdf` : null;
</script>

<template>
  <Head>
    <title>PDF Templates - Settings - Cubrel</title>
  </Head>

  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
    }"
  >
    <SettingsLink :color="appSettings.primary_color" />

    <div class="settings__system">
      <div class="settings__system__header" style="margin-bottom: 24px">
        <h3
          style="font-size: 18px; font-weight: 600; color: var(--primary-color)"
        >
          PDF Templates
        </h3>
        <p style="color: #6b7280; margin-top: 4px; font-size: 13px">
          Manage which PDF template is used when generating documents from a
          module's record view.
        </p>
      </div>

      <div
        v-if="templates.length === 0"
        style="padding: 32px 0; text-align: center; color: #9ca3af"
      >
        <i
          class="fa-solid fa-file-pdf"
          style="font-size: 32px; margin-bottom: 12px"
        ></i>
        <p>No PDF templates configured yet.</p>
      </div>

      <div
        v-for="(group, slug) in grouped()"
        :key="slug"
        style="margin-bottom: 32px"
      >
        <div
          style="
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #9ca3af;
            margin-bottom: 12px;
          "
        >
          {{ slug }}
        </div>

        <div
          v-for="t in group"
          :key="t.id"
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #fff;
          "
        >
          <div style="display: flex; align-items: center; gap: 12px">
            <i
              class="fa-solid fa-file-pdf"
              :style="{
                color: t.is_default ? appSettings.primary_color : '#9ca3af',
                fontSize: '20px',
              }"
            ></i>
            <div>
              <div style="font-weight: 600; font-size: 14px">{{ t.name }}</div>
              <div style="font-size: 12px; color: #6b7280; margin-top: 2px">
                View:
                <code
                  style="
                    background: #f3f4f6;
                    padding: 1px 5px;
                    border-radius: 3px;
                  "
                  >{{ t.blade_view }}</code
                >
              </div>
              <div
                v-if="t.description"
                style="font-size: 12px; color: #9ca3af; margin-top: 2px"
              >
                {{ t.description }}
              </div>
            </div>
          </div>

          <div style="display: flex; align-items: center; gap: 10px">
            <span
              v-if="t.is_default"
              style="
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 3px 10px;
                border-radius: 4px;
                background: #d1fae5;
                color: #065f46;
              "
            >
              Default
            </span>
            <button
              v-else
              @click="setDefault(t.id)"
              style="
                font-size: 12px;
                padding: 5px 12px;
                border-radius: 6px;
                cursor: pointer;
                border: 1px solid #e5e7eb;
                background: #f9fafb;
                color: #374151;
              "
            >
              Set as default
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
