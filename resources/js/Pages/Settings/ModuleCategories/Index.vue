<script setup>
import { getCurrentInstance, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const props = defineProps({
  categories: { type: Array, default: () => [] },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.module_categories") },
];

const categories = ref(
  props.categories.map((c) => ({ ...c, _originalLabel: c.label })),
);

const newLabel = ref("");
const addCategory = () => {
  const label = newLabel.value.trim();
  if (!label) return;

  router.post(
    "/settings/module-categories",
    { label },
    {
      preserveScroll: true,
      onSuccess: () => {
        newLabel.value = "";
      },
    },
  );
};

const renameCategory = (category) => {
  const label = category.label.trim();
  if (!label || label === category._originalLabel) return;

  router.put(
    `/settings/module-categories/${category.id}`,
    { label },
    { preserveScroll: true, preserveState: true },
  );
  category._originalLabel = label;
};

const confirmDeleteId = ref(null);
const deleteCategory = (category) => {
  if (confirmDeleteId.value !== category.id) {
    confirmDeleteId.value = category.id;
    return;
  }
  confirmDeleteId.value = null;
  router.delete(`/settings/module-categories/${category.id}`, {
    preserveScroll: true,
  });
};

const dragging = ref(null);
const dragOverId = ref(null);

const startDrag = (id, event) => {
  dragging.value = id;
  event.dataTransfer.effectAllowed = "move";
};

const onDragOver = (id, event) => {
  if (!dragging.value || dragging.value === id) return;
  event.preventDefault();
  dragOverId.value = id;
};

const endDrag = () => {
  dragging.value = null;
  dragOverId.value = null;
};

const onDrop = (targetId, event) => {
  event.preventDefault();
  if (!dragging.value || dragging.value === targetId) {
    endDrag();
    return;
  }

  const list = [...categories.value];
  const fromIndex = list.findIndex((c) => c.id === dragging.value);
  const toIndex = list.findIndex((c) => c.id === targetId);
  const [item] = list.splice(fromIndex, 1);
  list.splice(toIndex, 0, item);
  categories.value = list;
  endDrag();

  router.post(
    "/settings/module-categories/reorder",
    { ids: list.map((c) => c.id) },
    { preserveScroll: true, preserveState: true },
  );
};
</script>

<template>
  <Head>
    <title>{{ $t("settings.items.module_categories") }}</title>
  </Head>

  <div
    class="settings"
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

    <div class="fields">
      <div class="fields__header">
        <span class="fields__header__name">
          {{ $t("settings.module_categories.title") }}
        </span>
      </div>
      <p class="module-categories__description">
        {{ $t("settings.module_categories.description") }}
      </p>

      <table class="fields__table">
        <thead>
          <tr>
            <th style="width: 40px"></th>
            <th>{{ $t("settings.module_categories.label") }}</th>
            <th style="width: 120px">
              {{ $t("settings.module_categories.modules_count") }}
            </th>
            <th style="width: 70px"></th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="category in categories"
            :key="category.id"
            class="fields__table__row module-categories__row"
            :class="{
              'module-categories__row--dragging': dragging === category.id,
              'module-categories__row--drop-target':
                dragOverId === category.id,
            }"
            draggable="true"
            @dragstart="startDrag(category.id, $event)"
            @dragover="onDragOver(category.id, $event)"
            @drop="onDrop(category.id, $event)"
            @dragend="endDrag"
          >
            <td class="module-categories__row__handle">
              <i class="fa-solid fa-grip-vertical"></i>
            </td>
            <td>
              <input
                v-model="category.label"
                type="text"
                class="module-categories__row__input"
                :placeholder="$t('settings.module_categories.label_placeholder')"
                @blur="renameCategory(category)"
                @keydown.enter="$event.target.blur()"
              />
            </td>
            <td>{{ category.modules_count }}</td>
            <td class="fields__table__row__actions">
              <button
                :class="[
                  'fields__table__row__actions__delete',
                  { 'fields__table__row__actions__delete--confirm':
                      confirmDeleteId === category.id },
                ]"
                type="button"
                :title="$t('settings.module_categories.confirm_delete')"
                @click="deleteCategory(category)"
              >
                <i
                  class="fa-solid"
                  :class="
                    confirmDeleteId === category.id ? 'fa-check' : 'fa-trash'
                  "
                ></i>
              </button>
            </td>
          </tr>

          <tr v-if="categories.length === 0">
            <td colspan="4" class="module-categories__empty">
              {{ $t("settings.module_categories.no_categories") }}
            </td>
          </tr>

          <tr class="module-categories__add-row">
            <td></td>
            <td colspan="2">
              <input
                v-model="newLabel"
                type="text"
                class="module-categories__row__input"
                :placeholder="$t('settings.module_categories.label_placeholder')"
                @keydown.enter="addCategory"
              />
            </td>
            <td class="fields__table__row__actions">
              <button
                type="button"
                class="module-categories__add-btn"
                :title="$t('settings.module_categories.add')"
                @click="addCategory"
              >
                <i class="fa-solid fa-plus"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>