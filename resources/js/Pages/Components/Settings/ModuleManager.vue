<script setup>
import { computed, inject } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { usePage, Link } from "@inertiajs/vue3";

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: Layout,
});

const props = defineProps({
  modules: Object,
});

const page = usePage();
const currentPath = page.url;
const module = computed(() => page.props.receivedItem || page.props);
</script>

<template>
  <ul class="settings__items__modules">
    <Link
      v-for="m in modules"
      :style="
        appSettings.use_individual_module_colors == '0'
          ? { '--module-color': appSettings.primary_color }
          : { '--module-color': m.color }
      "
      :href="currentPath + '/' + m.id"
      class="settings__items__modules__item"
    >
      <i :class="['fa-solid', m.icon]"></i>
      {{ m.label }}
    </Link>
  </ul>
</template>
