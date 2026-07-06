<script setup>
import { ref, computed, watch, getCurrentInstance } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import LayoutPdfEditor from "@/Pages/Components/Settings/Layouts/LayoutPdfEditor.vue";
import PdfPreviewPanel from "@/Pages/Components/Settings/Layouts/PdfPreviewPanel.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import { useAlerts } from "@/Composables/useAlerts";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;
const { error } = useAlerts();
const previewOpen = ref(false);

const props = defineProps({
  modules: Array,
  selectedModule: Object,
  fields: Array,
  relationships: Array,
  lineItemFields: Array,
});

const LOCKED_HEADER = { id: "section-header", type: "header", locked: true };
const LOCKED_FOOTER = { id: "section-footer", type: "footer", locked: true };

const pdfSections = ref([{ ...LOCKED_HEADER }, { ...LOCKED_FOOTER }]);

const form = useForm({
  module_slug: props.selectedModule?.slug ?? "",
  name: "",
  description: "",
  is_default: false,
  definition: { sections: [] },
});

watch(
  () => props.selectedModule,
  (mod) => {
    if (mod) form.module_slug = mod.slug;
  },
  { immediate: true },
);

const moduleDropdownList = computed(() => ({
  values: (props.modules ?? []).map((m) => ({
    value: m.slug,
    label: m.label ?? m.name,
  })),
}));

const moduleField = computed(() => ({
  name: "module_slug",
  type: "select",
  dropdown_list: moduleDropdownList.value,
}));

const handleModuleChange = (slug) => {
  form.module_slug = slug;
  if (!slug) return;
  router.get(
    "/settings/pdf-templates/create",
    { module: slug },
    { preserveState: true, preserveScroll: true },
  );
};

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

const moduleLabel = computed(() =>
  props.selectedModule
    ? (props.selectedModule.label ?? props.selectedModule.name)
    : "",
);

const submit = () => {
  form.definition = { sections: pdfSections.value };
  form.post("/settings/pdf-templates", {
    onError: (errs) => {
      const first = Object.values(errs)[0];
      error(first || t("globals.pdf_templates.save_error"));
    },
  });
};

const showEditor = computed(() => {
  return props.selectedModule && form.module_slug;
});
</script>

<template>
  <Head>
    <title>{{ $t("globals.pdf_templates.new_page_title") }}</title>
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
      <Link href="/settings/pdf-templates">
        <i class="fa-solid fa-arrow-left"></i>
        {{ $t("globals.pdf_templates.back") }}
      </Link>
    </div>
    <div class="settings__module__edit">
      <form class="settings__module__edit__form" @submit.prevent="submit">
        <div class="settings__module__edit__element">
          <label>{{ $t("globals.pdf_templates.module_label") }}</label>
          <FieldRenderer
            :modelValue="form.module_slug"
            :field="moduleField"
            :searchable="true"
            mode="settings"
            :hasError="!!form.errors.module_slug"
            @update:modelValue="handleModuleChange"
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
        <div v-if="showEditor" class="settings__pdf-editor">
          <div class="settings__pdf-editor__body">
            <LayoutPdfEditor
              v-model:sections="pdfSections"
              :available-fields="availablePdfFields"
              :available-relationships="relationships ?? []"
              :line-item-fields="lineItemFields ?? []"
              :module-label="moduleLabel"
              :module="selectedModule"
            />
          </div>
        </div>

        <div v-else class="settings__pdf-editor__placeholder">
          <i class="fa-solid fa-file-pdf"></i>
          <p>{{ $t("globals.pdf_templates.select_module_hint") }}</p>
        </div>

        <p
          v-if="form.errors.definition"
          class="settings__module__edit__element__error"
        >
          {{ form.errors.definition }}
        </p>

        <PdfPreviewPanel
          :visible="previewOpen"
          :sections="pdfSections"
          :module-slug="form.module_slug"
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
            :disabled="!showEditor"
          >
            <i class="fa-solid fa-eye"></i>
            {{ $t("globals.pdf_templates.preview") }}
          </button>
          <button
            type="submit"
            class="settings__actions__save"
            :disabled="form.processing || !form.module_slug || !form.name"
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
