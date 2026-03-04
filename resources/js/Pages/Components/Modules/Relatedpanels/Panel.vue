<script setup>
import { ref, computed, watch } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import PanelHeader from "./PanelHeader.vue";
import PanelBody from "./PanelBody.vue";
const props = defineProps({
  relationships: Object,
  panel: Object,
  expandPanel: String,
});

const openPanels = ref(
  Object.values(props.relationships)
    .filter((r) => r.records?.length)
    .map((r) => r.name),
);

watch(
  () => props.expandPanel,
  (newVal) => {
    if (newVal === props.panel.name) {
      const index = openPanels.value.indexOf(newVal);
      if (index === -1) {
        openPanels.value.push(newVal);
      }
    }
  },
);
const relationshipMap = computed(() => {
  return Object.values(props.relationships).reduce((acc, rel) => {
    acc[rel.name] = rel;
    return acc;
  }, {});
});

const hasRecords = (panel) => {
  return relationshipMap.value?.[panel]?.records?.length || false;
};

const togglePanel = (name) => {
  if (!hasRecords(name)) {
    return;
  }
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

const emit = defineEmits(["open-overlay", "update-panel-trigger"]);

const openLinkOverlay = () => {
  const parent =
    relationshipMap.value?.[props.panel?.name]?.records?.[0] || null;
  emit("open-overlay", props.panel, parent);
};

const handleUpdatePanel = () => {
  emit("update-panel-trigger");
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
    :type="relationshipMap[panel.name].role"
  ></PanelHeader>
  <PanelBody
    :relationship="relationshipMap[panel.name]"
    :isOpenPanel="openPanels.includes(panel.name)"
    :panel="panel"
    @update-panel="handleUpdatePanel"
  ></PanelBody>
</template>
