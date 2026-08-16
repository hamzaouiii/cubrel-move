<script setup>
import { getCurrentInstance, ref, computed, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error } = useAlerts();
const { confirm } = useConfirm();
const page = usePage();
const appSettings = page.props.appSettings;

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.sidebar") },
];

const groupByCategory = (mods) => {
  const groups = {};
  mods.forEach((mod) => {
    const key = mod.category_id || "uncategorized";
    if (!groups[key]) {
      groups[key] = {
        id: key,
        name: mod.category_label || t("globals.sidebar.uncategorized"),
        modules: [],
      };
    }
    groups[key].modules.push(mod);
  });
  return Object.values(groups);
};

const flattenSections = (secs) => secs.flatMap((section) => section.modules);

const normalizeBool = (val, fallback) => {
  if (val === undefined || val === null || val === "") return fallback;
  return val === true || val === 1 || val === "1";
};

const initialModules = page.props.modules ?? [];
const initialSortByCategory = normalizeBool(
  appSettings.sidebar_sort_by_category,
  true,
);

const sortByCategory = ref(initialSortByCategory);
const sections = ref(
  initialSortByCategory
    ? groupByCategory(initialModules)
    : [{ id: "all", name: null, modules: initialModules }],
);

const snapshotState = () =>
  JSON.stringify({
    sortByCategory: sortByCategory.value,
    sections: sections.value.map((section) => ({
      id: section.id,
      modules: section.modules.map((mod) => mod.slug),
    })),
  });

const originalSnapshot = ref(snapshotState());
const isDirty = computed(() => snapshotState() !== originalSnapshot.value);

watch(sortByCategory, (val) => {
  const allModules = flattenSections(sections.value);
  sections.value = val
    ? groupByCategory(allModules)
    : [{ id: "all", name: null, modules: allModules }];
});

const resetToSaved = () => {
  sortByCategory.value = initialSortByCategory;
  sections.value = initialSortByCategory
    ? groupByCategory(initialModules)
    : [{ id: "all", name: null, modules: initialModules }];
};

const saving = ref(false);
const saveOrder = () => {
  saving.value = true;
  const payload = {
    sort_by_category: sortByCategory.value,
    sections: sections.value.map((section) => ({
      category_id:
        sortByCategory.value && section.id !== "uncategorized"
          ? section.id
          : null,
      modules: section.modules.map((mod) => mod.slug),
    })),
  };

  router.put("/settings/sidebar", payload, {
    preserveScroll: true,
    onSuccess: () => {
      originalSnapshot.value = snapshotState();
    },
    onError: (errors) => {
      error(Object.values(errors)[0] || t("settings.setting_update_error"));
    },
    onFinish: () => {
      saving.value = false;
    },
  });
};

useUnsavedChangesGuard({ getIsDirty: () => isDirty.value });

const dragging = ref(null);
const dragOverKey = ref(null);

const endDrag = () => {
  dragging.value = null;
  dragOverKey.value = null;
};

const startSectionDrag = (index, event) => {
  dragging.value = { type: "section", index };
  event.dataTransfer.effectAllowed = "move";
};

const onSectionOrderDragOver = (pos, event) => {
  if (dragging.value?.type !== "section") return;
  event.preventDefault();
  dragOverKey.value = `section-order-${pos}`;
};

const moveSectionOrder = (fromIndex, toIndex) => {
  if (toIndex === fromIndex || toIndex === fromIndex + 1) return;
  const secs = [...sections.value];
  const [item] = secs.splice(fromIndex, 1);
  const insertAt = toIndex > fromIndex ? toIndex - 1 : toIndex;
  secs.splice(insertAt, 0, item);
  sections.value = secs;
};

const onSectionOrderDrop = (pos, event) => {
  event.preventDefault();
  if (dragging.value?.type !== "section") return;
  moveSectionOrder(dragging.value.index, pos);
  endDrag();
};

const startModuleDrag = (sectionIndex, index, event) => {
  dragging.value = { type: "module", sectionIndex, index };
  event.dataTransfer.effectAllowed = "move";
};

const onModuleOrderDragOver = (sectionIndex, pos, event) => {
  if (dragging.value?.type !== "module") return;
  // Modules can be reordered within a category but never dragged out of it.
  if (dragging.value.sectionIndex !== sectionIndex) return;
  event.preventDefault();
  dragOverKey.value = `module-order-${sectionIndex}-${pos}`;
};

const moveModuleOrder = (sectionIndex, fromIndex, toIndex) => {
  if (toIndex === fromIndex || toIndex === fromIndex + 1) return;
  const secs = [...sections.value];
  const mods = [...secs[sectionIndex].modules];
  const [item] = mods.splice(fromIndex, 1);
  const insertAt = toIndex > fromIndex ? toIndex - 1 : toIndex;
  mods.splice(insertAt, 0, item);
  secs[sectionIndex] = { ...secs[sectionIndex], modules: mods };
  sections.value = secs;
};

const onModuleOrderDrop = (sectionIndex, pos, event) => {
  event.preventDefault();
  if (dragging.value?.type !== "module") return;
  if (dragging.value.sectionIndex !== sectionIndex) {
    endDrag();
    return;
  }
  moveModuleOrder(sectionIndex, dragging.value.index, pos);
  endDrag();
};
</script>

<template>
  <Head>
    <title>{{ $t("settings.items.sidebar") }}</title>
  </Head>

  <div
    class="settings sidebar_manager"
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

    <div class="sidebar-manager__toggle">
      <Checkbox v-model="sortByCategory" />
      <div class="sidebar-manager__toggle__text">
        <span class="sidebar-manager__toggle__text__label">
          {{ $t("settings.sidebar.sort_by_category") }}
        </span>
        <span class="sidebar-manager__toggle__text__hint">
          {{ $t("settings.sidebar.sort_by_category_hint") }}
        </span>
      </div>
    </div>

    <div class="editor sidebar-manager__editor">
      <div class="editor__sections">
        <div
          class="editor__sections__drop-zone"
          :class="{
            'editor__sections__drop-zone--active':
              dragOverKey === 'section-order-0',
          }"
          @dragover="onSectionOrderDragOver(0, $event)"
          @drop="onSectionOrderDrop(0, $event)"
        />
        <template v-for="(section, sectionIndex) in sections" :key="section.id">
          <div
            class="editor__sections__item"
            :class="{
              'editor__sections__item--dragging':
                dragging?.type === 'section' &&
                dragging?.index === sectionIndex,
            }"
          >
            <div v-if="sortByCategory" class="editor__sections__item__header">
              <div
                class="editor__sections__item__header__drag"
                draggable="true"
                @dragstart="startSectionDrag(sectionIndex, $event)"
                @dragend="endDrag"
              >
                <i class="fa-solid fa-grip-vertical"></i>
              </div>
              <div class="editor__sections__item__header__title">
                <span>{{ section.name }}</span>
              </div>
            </div>

            <div class="editor__sections__item__content">
              <div
                v-if="section.modules.length === 0"
                class="editor__sections__item__content__empty"
                :class="{
                  'editor__sections__item__content__empty--active':
                    dragOverKey === `module-order-${sectionIndex}-0`,
                }"
                @dragover="onModuleOrderDragOver(sectionIndex, 0, $event)"
                @drop="onModuleOrderDrop(sectionIndex, 0, $event)"
              >
                {{ $t("settings.sidebar.no_modules") }}
              </div>

              <div v-else class="sidebar-manager__modules">
                <div
                  class="editor__columns__drop-zone editor__columns__drop-zone--horizontal"
                  :class="{
                    'editor__columns__drop-zone--active':
                      dragOverKey === `module-order-${sectionIndex}-0`,
                  }"
                  @dragover="onModuleOrderDragOver(sectionIndex, 0, $event)"
                  @drop="onModuleOrderDrop(sectionIndex, 0, $event)"
                />

                <template
                  v-for="(mod, moduleIndex) in section.modules"
                  :key="mod.slug"
                >
                  <div
                    class="sidebar-manager__modules__item"
                    :class="{
                      'sidebar-manager__modules__item--dragging':
                        dragging?.type === 'module' &&
                        dragging?.sectionIndex === sectionIndex &&
                        dragging?.index === moduleIndex,
                    }"
                    :style="{ '--module-color': mod.color }"
                    draggable="true"
                    @dragstart="
                      startModuleDrag(sectionIndex, moduleIndex, $event)
                    "
                    @dragend="endDrag"
                  >
                    <span class="sidebar-manager__modules__item__handle">
                      <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                    <span class="sidebar-manager__modules__item__icon">
                      <i :class="['fa-solid', mod.icon]"></i>
                    </span>
                    <span class="sidebar-manager__modules__item__label">
                      {{ $t(mod.label) }}
                    </span>
                  </div>

                  <div
                    class="editor__columns__drop-zone editor__columns__drop-zone--horizontal"
                    :class="{
                      'editor__columns__drop-zone--active':
                        dragOverKey ===
                        `module-order-${sectionIndex}-${moduleIndex + 1}`,
                    }"
                    @dragover="
                      onModuleOrderDragOver(
                        sectionIndex,
                        moduleIndex + 1,
                        $event,
                      )
                    "
                    @drop="
                      onModuleOrderDrop(sectionIndex, moduleIndex + 1, $event)
                    "
                  />
                </template>
              </div>
            </div>
          </div>
          <div
            class="editor__sections__drop-zone"
            :class="{
              'editor__sections__drop-zone--active':
                dragOverKey === `section-order-${sectionIndex + 1}`,
            }"
            @dragover="onSectionOrderDragOver(sectionIndex + 1, $event)"
            @drop="onSectionOrderDrop(sectionIndex + 1, $event)"
          />
        </template>
      </div>

      <div class="settings__actions">
        <button
          @click="resetToSaved"
          type="button"
          class="settings__actions__reset"
          :disabled="!isDirty || saving"
        >
          {{ $t("settings.sidebar.reset") }}
        </button>
        <button
          @click="saveOrder"
          type="button"
          class="settings__actions__save"
          :disabled="!isDirty || saving"
        >
          {{
            saving ? $t("settings.sidebar.saving") : $t("settings.sidebar.save")
          }}
        </button>
      </div>
    </div>
  </div>
</template>
