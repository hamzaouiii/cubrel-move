<script setup>
import { ref, computed, watch } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import PanelHeader from "./PanelHeader.vue";
import PanelBody from "./PanelBody.vue";
const props = defineProps({
  relationship: Object,
  panel: Object,
  expandPanel: String,
});
const openPanels = ref(props.relationship?.records?.length || false);
const relatedFields = computed(() => props?.relationship?.fields || null);
watch(
  () => props.expandPanel,
  (newVal) => {
    if (newVal === props.panel.name) {
      openPanels.value = !openPanels.value;
    }
  },
);

const hasRecords = computed(() => {
  return props.relationship.value?.records?.length || false;
});

const togglePanel = (name) => {
  if (!hasRecords.value) {
    return;
  }
  openPanels.value = !openPanels.value;
};

const page = usePage();
const modules = computed(() => page.props.modules);

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedIcon = (slug) => getModule(slug)?.icon;
const getSingleLabel = (slug) => getModule(slug)?.single_label;
const getLabel = (slug) => getModule(slug)?.label;

const emit = defineEmits(["open-overlay", "update-panel-trigger"]);

const openLinkOverlay = () => {
  const parent = props.relationship?.records?.[0] || null;
  emit("open-overlay", props.panel, parent);
};

const handleUpdatePanel = () => {
  emit("update-panel-trigger");
};
</script>

<template>
  <PanelHeader
    v-if="relationship"
    @toggle="togglePanel(panel.name)"
    @open-overlay="openLinkOverlay"
    :icon="getRelatedIcon(relationship.related_slug)"
    :count="relationship.records?.length ?? 0"
    :label="getLabel(relationship.related_slug)"
    :single_label="getSingleLabel(relationship.related_slug)"
    :type="relationship.role"
  ></PanelHeader>
  <PanelBody
    :relationship="relationship"
    :isOpenPanel="openPanels"
    :panel="panel"
    :fields="relatedFields"
    @update-panel="handleUpdatePanel"
  ></PanelBody>
</template>
