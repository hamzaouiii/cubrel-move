<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import LayoutPdfEditor from "@/Pages/Components/Settings/Layouts/LayoutPdfEditor.vue";
import PdfPreviewPanel from "@/Pages/Components/Settings/Layouts/PdfPreviewPanel.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import { useAlerts } from "@/Composables/useAlerts";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;
const { success, error } = useAlerts();
const previewOpen = ref(false);

const props = defineProps({
  template: Object,
  module: Object,
  fields: Array,
  relationships: Array,
  lineItemFields: Array,
});

const LOCKED_HEADER = { id: "section-header", type: "header", locked: true };
const LOCKED_FOOTER = { id: "section-footer", type: "footer", locked: true };

function initSections(definition) {
  let sections = (definition?.sections ?? []).map((s) => ({ ...s }));
  if (!sections.some((s) => s.type === "header"))
    sections.unshift({ ...LOCKED_HEADER });
  if (!sections.some((s) => s.type === "footer"))
    sections.push({ ...LOCKED_FOOTER });
  return sections;
}

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.pdf_templates"), href: "/settings/pdf-templates" },
  { label: props.template?.name },
];

const pdfSections = ref(initSections(props.template.definition));

const form = useForm({
  name: props.template.name,
  description: props.template.description ?? "",
  is_default: props.template.is_default,
  definition: props.template.definition ?? { sections: [] },
});

const availablePdfFields = computed(() => {
  const used = new Set();
  pdfSections.value.forEach((section) => {
    if (section.type === "fields" || section.type === "header") {
      (section.items || []).forEach((item) => {
        if (item.kind === "field") used.add(item.name);
      });
    }
  });
  return (props.fields ?? []).filter((f) => !used.has(f.name));
});

const moduleLabel = computed(
  () => props.module?.label ?? props.module?.name ?? "",
);

const submit = () => {
  form.definition = { sections: pdfSections.value };
  form.put(`/settings/pdf-templates/${props.template.id}`, {
    onSuccess: () => success(t("globals.pdf_templates.updated")),
    onError: (errs) => {
      const first = Object.values(errs)[0];
      error(first || t("globals.pdf_templates.save_error"));
    },
  });
};
</script>

<template>
  <Head>
    <title>{{ $t("globals.pdf_templates.edit_page_title") }}</title>
  </Head>

  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--module-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>

    <div class="settings__module__edit">
      <form class="settings__module__edit__form" @submit.prevent="submit">
        <div class="settings__module__edit__element">
          <label>{{ $t("globals.pdf_templates.module_label") }}</label>
          <FieldRenderer
            :modelValue="module?.label ?? template.module_slug"
            :field="{ name: 'module_slug', type: 'text' }"
            :readOnly="true"
          />
        </div>

        <div class="settings__module__edit__element">
          <label>{{ $t("globals.pdf_templates.name_label") }}</label>
          <FieldRenderer
            v-model="form.name"
            :field="{ name: 'name', type: 'text' }"
            mode="settings"
            :hasError="!!form.errors.name"
          />
        </div>

        <div class="settings__module__edit__element">
          <label>
            {{ $t("globals.pdf_templates.description_label") }}
          </label>
          <FieldRenderer
            v-model="form.description"
            :field="{ name: 'description', type: 'text' }"
            mode="settings"
          />
        </div>

        <div class="settings__module__edit__element">
          <label>{{ $t("globals.pdf_templates.is_default_label") }}</label>
          <FieldRenderer
            v-model="form.is_default"
            :field="{ name: 'is_default', type: 'checkbox' }"
          />
        </div>

        <!-- PDF editor — full width outside element grid -->
        <div class="settings__pdf-editor">
          <div class="settings__pdf-editor__body">
            <LayoutPdfEditor
              v-model:sections="pdfSections"
              :available-fields="availablePdfFields"
              :available-relationships="relationships ?? []"
              :line-item-fields="lineItemFields ?? []"
              :module-label="moduleLabel"
              :module="module"
            />
          </div>
        </div>

        <PdfPreviewPanel
          :visible="previewOpen"
          :sections="pdfSections"
          :module-slug="template.module_slug"
          :module-label="moduleLabel"
          @close="previewOpen = false"
        />

        <div class="settings__actions">
          <button
            type="button"
            class="settings__actions__reset"
            @click="router.visit('/settings/pdf-templates')"
          >
            {{ $t("globals.pdf_templates.cancel") }}
          </button>
          <button
            type="button"
            class="settings__actions__preview"
            @click="previewOpen = true"
          >
            <i class="fa-solid fa-eye"></i>
            {{ $t("globals.pdf_templates.preview") }}
          </button>
          <button
            type="submit"
            class="settings__actions__save"
            :disabled="form.processing"
          >
            {{
              form.processing
                ? $t("globals.pdf_templates.saving")
                : $t("globals.pdf_templates.save_btn")
            }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
