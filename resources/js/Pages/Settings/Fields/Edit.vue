<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  metadata: Array,
  item: Object,
});
const isDirty = () => {
  return true; //for now
}
</script>

<template>
  <Head>
    <title>
      {{ metadata.label }} - {{ module.label }} - {{ $t("fields.label") }} -
      {{ $t("settings.label") }}
    </title>
  </Head>
  <div class="settings">
    <div class="settings_header">
      <div class="settings_header_title">
        <h5>
          <Link href="/settings">{{ $t("settings.label") }}</Link>
        </h5>
        <span>></span>
        <h5>
          <Link href="/settings/fields/">{{ $t("fields.label") }}</Link>
        </h5>
        <span>></span>
        <h5>
          <Link :href="`/settings/fields/${module.id}`">{{
            module.label
          }}</Link>
        </h5>
        <span>></span>
        <h6>{{ metadata.label }}</h6>
      </div>
    </div>
    <div class="settings_system">
      <form @submit.prevent="saveSetting" class="settings_system_form">
        <div
          v-for="(i, index) in metadata"
          :key="i.id || i.key || index"
          class="settings_system_form_field"
        >
          <label>{{ index }}</label>
          <input type="text" v-model="metadata[index]"></input>
        </div>
        <div class="settings_system_form_actions">
          <button
            type="button"
            class="reset-btn"
            @click="resetForm"
            :disabled="!isDirty()"
          >
            {{ $t("settings.reset") }}
          </button>

          <button type="submit" :disabled="!isDirty() ">
            {{ $t("settings.save") }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
