<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import PanelHeader from "./PanelHeader.vue";
import PanelBody from "./PanelBody.vue";
const props = defineProps({
  relationships: Object,
  panel: Object,
});

const openPanels = ref(
  Object.values(props.relationships)
    .filter((r) => r.records?.length)
    .map((r) => r.name),
);
const relationshipMap = computed(() => {
  return Object.values(props.relationships).reduce((acc, rel) => {
    acc[rel.name] = rel;
    return acc;
  }, {});
});
const togglePanel = (name) => {
  const index = openPanels.value.indexOf(name);
  index === -1
    ? openPanels.value.push(name)
    : openPanels.value.splice(index, 1);
};

const page = usePage();
const modules = computed(() => page.props.modules);

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedIcon = (slug) => getModule(slug)?.icon;
const getSingleLabel = (slug) => getModule(slug)?.single_label;
const getLabel = (slug) => getModule(slug)?.label;

const emit = defineEmits(["open-overlay"]);

const openLinkOverlay = () => {
  emit("open-overlay", props.panel);
};
</script>

<template>
  <PanelHeader
    v-if="relationshipMap[panel.name]"
    @toggle="togglePanel(panel.name)"
    @open-overlay="openLinkOverlay"
    :icon="getRelatedIcon(relationshipMap[panel.name].related_slug)"
    :count="relationshipMap[panel.name].records?.length ?? 0"
    :label="getLabel(relationshipMap[panel.name].related_slug)"
    :single_label="getSingleLabel(relationshipMap[panel.name].related_slug)"
  ></PanelHeader>
  <PanelBody
    :relationship="relationshipMap[panel.name]"
    :isOpenPanel="openPanels.includes(panel.name)"
    :panel="panel"
  ></PanelBody>
</template>
