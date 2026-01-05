<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  metadata: Object,
  item: Object,
});
const page = usePage();
const appSettings = page.props.appSettings;

const isDirty = () => {
  return false; //for now
}
const fieldsUrl = () => {
  const key = props.metadata?.key || '';
  const url = page.url;
  const segments = url.split("/").filter(Boolean);
  if(segments.at(-1) === key){
    segments.pop();
  }
  const u = ("/" + segments.join("/")).toString();
return u;
}


</script>

<template>
  <Head>
    <title>
      {{ metadata.label }} - {{ module.label }} - {{ $t("fields.label") }} -
      {{ $t("settings.label") }}
    </title>
  </Head>
  <div class="settings"
  :style="  { '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs :setting-module="module"></ModuleSettingBreadcrumbs>
      </div>
    </div>
      <ModuleSettingTabs :setting-module="module" active-key="fields"></ModuleSettingTabs>
      <div class="settings__module__header">
        <Link :href="fieldsUrl()"> <i class="fa-solid fa-arrow-left"></i> {{ $t('fields.back_to_list') }}</Link>
      </div>
      <div class="settings__module__edit">

        <form @submit.prevent="saveSetting" >
          <div
            v-for="(i, index) in metadata"
            :key="i.id || i.key || index"
            class="settings__module__edit__element"
          >
            <label>{{ index }}</label>
            <input type="text" v-model="metadata[index]"></input>
          </div>
          <div class="settings__module__edit__actions">
            <button
              type="button"
                class="settings__module__edit__actions__reset btn"

              @click="resetForm"
              :disabled="!isDirty()"
            >
              {{ $t("settings.reset") }}
            </button>

            <button type="submit" 
                class="settings__module__edit__actions__save btn"
            :disabled="!isDirty() ">
              {{ $t("settings.save") }}
            </button>
          </div>
        </form>
      </div>
  </div>
</template>
