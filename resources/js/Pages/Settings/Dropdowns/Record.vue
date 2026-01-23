<script setup>
import { Head, usePage, Link } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  dropdown: Object,
  item: Object,
});
console.log(props.dropdown);
const appSettings = usePage().props.appSettings;
</script>

<template>
  <Head>
    <title>
      {{ $t("settings.items.dropdowns") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="item"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>

    <div class="settings__dropdown">
      <div class="settings__dropdown__edit">
        <div class="settings__dropdown__edit__header">
          <ul class="settings__dropdown__edit__header__info">
            <li class="settings__dropdown__edit__header__info__data">
              <span class="settings__dropdown__edit__header__info__data__label"
                >{{ $t("settings.dropdown.list_name") }}:</span
              >
              <span
                class="settings__dropdown__edit__header__info__data__value"
                >{{ dropdown.key }}</span
              >
            </li>
            <li
              class="settings__dropdown__edit__header__info__data"
              v-if="dropdown.field_key"
            >
              <span class="settings__dropdown__edit__header__info__data__label"
                >{{ $t("settings.dropdown.related_field") }}:
              </span>
              <span
                class="settings__dropdown__edit__header__info__data__value"
                >{{ dropdown.field_key }}</span
              >
            </li>
            <li class="settings__dropdown__edit__header__info__indicator">
              <span>{{ $t("settings.dropdown.display_label") }}</span>
              <span>{{ $t("settings.dropdown.value") }}</span>
              <div></div>
            </li>
          </ul>
        </div>
        <ul>
          <li
            v-for="l in dropdown.values"
            class="settings__dropdown__edit__value"
          >
            <div class="settings__dropdown__edit__value__item">
              <span>{{ $t(l.label) }}</span>
            </div>

            <div class="settings__dropdown__edit__value__item">
              <span>{{ $t(l.value) }}</span>
            </div>
            <div class="settings__dropdown__edit__value__actions">
              <span class="settings__dropdown__edit__value__actions__edit">
                <i class="fa-solid fa-pen"></i>
              </span>
              <span class="settings__dropdown__edit__value__actions__delete">
                <i class="fa-solid fa-trash-can"></i>
              </span>
            </div>
          </li>
          <li class="settings__dropdown__edit__value">
            <div class="settings__dropdown__edit__value__item">
              <input type="text" name="" id="" />
            </div>
            <div class="settings__dropdown__edit__value__item">
              <input type="text" name="" id="" />
            </div>
            <div class="settings__dropdown__edit__value__actions">
              <span class="settings__dropdown__edit__value__actions__add">
                <i class="fa-solid fa-plus"></i>
              </span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
