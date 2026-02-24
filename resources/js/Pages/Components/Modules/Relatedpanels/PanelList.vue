<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import Panel from "./Panel.vue";

const props = defineProps({
  relationships: { type: Object, required: true },
  layout: { type: Object, required: true },
});

const relationshipMap = computed(() => {
  return Object.values(props.relationships).reduce((acc, rel) => {
    acc[rel.name] = rel;
    return acc;
  }, {});
});

const columns = computed(() => props.layout?.columns ?? []);

const page = usePage();
const modules = computed(() => page.props.modules);
const appSettings = page.props.appSettings;

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedColor = (slug) => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;
};
const emit = defineEmits(["open-overlay"]);

const forwardOpenOverlay = (panel) => {
  emit("open-overlay", panel);
};
</script>

<template>
  <div class="relatedpanels">
    <ul class="relatedpanels__container">
      <div
        v-for="(col, colIndex) in columns"
        :key="colIndex"
        class="relatedpanels__container__column"
      >
        <li
          v-for="panel in col.layout || []"
          :key="panel.name"
          class="relatedpanels__item"
          :style="{
            '--related-color': getRelatedColor(
              relationshipMap[panel.name].related_slug,
            ),
          }"
        >
          <Panel
            :relationships="relationships"
            :panel="panel"
            @open-overlay="forwardOpenOverlay"
          ></Panel>
        </li>
      </div>
    </ul>
  </div>
</template>
