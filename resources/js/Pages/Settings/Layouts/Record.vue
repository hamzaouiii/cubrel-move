<script setup>
import { computed, reactive } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import AppTooltip from "@/Pages/Components/Globals/AppTooltip.vue";
import { getCurrentInstance } from "vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
});

const page = usePage();
const currentPath = page.url;
const appSettings = usePage().props.appSettings;

const hasListLayout = computed(() => {
  return (
    props.module.layouts?.some((layout) => layout.type === "list") || false
  );
});

const hasRecordLayout = computed(() => {
  return (
    props.module.layouts?.some((layout) => layout.type === "record") || false
  );
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const tooltip = reactive({
  show: false,
  text: "",
  color: "",
  top: 0,
  left: 0,
});

const hideTooltip = () => {
  tooltip.show = false;
};

const onItemMouseEnter = (event, type) => {
  const rect = event.currentTarget.getBoundingClientRect();

  if (type == "list") {
    tooltip.text = hasListLayout.value
      ? t("layouts.edit_list_tooltip")
      : t("layouts.create_list_tooltip");
  } else if (type == "record") {
    tooltip.text = hasRecordLayout.value
      ? t("layouts.edit_record_tooltip")
      : t("layouts.create_record_tooltip");
  } else if (type == "related") {
    tooltip.text = hasRecordLayout.value
      ? t("layouts.edit_related_tooltip")
      : t("layouts.create_related_tooltip");
  }
  tooltip.text += props.module.label;
  tooltip.color = appSettings.primary_color;
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.right + 10;
  tooltip.show = true;
};

const onItemMouseLeave = () => {
  hideTooltip();
};
</script>

<template>
  <Head>
    <title>{{ module.label }} - {{ $t("settings.label") }}</title>
  </Head>

  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="module"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>

    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="module"
        active-key="layouts"
      ></ModuleSettingTabs>
      <div class="layouts">
        <Link class="layouts__item" :href="currentPath + '/list'">
          <div
            class="layouts__item__content"
            @mouseenter="onItemMouseEnter($event, 'list')"
            @mouseleave="onItemMouseLeave"
          >
            <div class="layouts__item__content__modifier">
              <i v-if="hasListLayout" class="fa-regular fa-pen-to-square"></i>
              <i v-else class="fa-regular fa-square-plus"></i>
            </div>
            <div class="layouts__item__content__icon">
              <i class="fa-solid fa-table-list"></i>
            </div>
            <span class="layouts__item__content__label">
              {{ $t("layouts.list") }}
            </span>
          </div>
        </Link>

        <Link class="layouts__item" :href="currentPath + '/record'">
          <div
            class="layouts__item__content"
            @mouseenter="onItemMouseEnter($event, 'record')"
            @mouseleave="onItemMouseLeave"
          >
            <div class="layouts__item__content__modifier">
              <i v-if="hasListLayout" class="fa-regular fa-pen-to-square"></i>
              <i v-else class="fa-regular fa-square-plus"></i>
            </div>
            <div class="layouts__item__content__icon">
              <i class="fa-regular fa-address-card"></i>
            </div>
            <span class="layouts__item__content__label">
              {{ $t("layouts.record_overview") }}
            </span>
          </div>
        </Link>
        <Link class="layouts__item" :href="currentPath + '/related'">
          <div
            class="layouts__item__content"
            @mouseenter="onItemMouseEnter($event, 'related')"
            @mouseleave="onItemMouseLeave"
          >
            <div class="layouts__item__content__modifier">
              <i v-if="hasListLayout" class="fa-regular fa-pen-to-square"></i>
              <i v-else class="fa-regular fa-square-plus"></i>
            </div>
            <div class="layouts__item__content__icon">
              <i class="fa-solid fa-diagram-predecessor"></i>
            </div>
            <span class="layouts__item__content__label">
              {{ $t("layouts.related") }}
            </span>
          </div>
        </Link>
        <Link class="layouts__item" :href="currentPath + '/linkingPanel'">
          <div
            class="layouts__item__content"
            @mouseenter="onItemMouseEnter($event, 'linkingPanel')"
            @mouseleave="onItemMouseLeave"
          >
            <div class="layouts__item__content__modifier">
              <i v-if="hasListLayout" class="fa-regular fa-pen-to-square"></i>
              <i v-else class="fa-regular fa-square-plus"></i>
            </div>
            <div class="layouts__item__content__icon">
              <i class="fa-solid fa-link"></i>
            </div>
            <span class="layouts__item__content__label">
              {{ $t("layouts.linkingPanel") }}
            </span>
          </div>
        </Link>
      </div>
    </div>
  </div>
  <AppTooltip
    :show="tooltip.show"
    :text="tooltip.text"
    :top="tooltip.top"
    :left="tooltip.left"
    :color="tooltip.color"
  />
</template>
