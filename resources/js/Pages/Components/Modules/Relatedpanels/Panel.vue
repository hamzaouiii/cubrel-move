<script setup>
import { ref, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import PanelHeader from "./PanelHeader.vue";
import PanelBody from "./PanelBody.vue";

const props = defineProps({
  relationship: Object,
  panel: Object,
  expandPanel: String,
});

const emit = defineEmits(["open-overlay", "update-panel-trigger"]);

// 1. Initialize based on existence of records
const isOpen = ref(!!props.relationship?.records?.length);

// 2. Corrected computed property (no .value on props)
const hasRecords = computed(() => {
  return !!props.relationship?.records?.length;
});

const relatedFields = computed(() => props.relationship?.fields || null);

// 3. Watcher to handle external expansion triggers
watch(
  () => props.expandPanel,
  (newVal) => {
    if (newVal === props.panel.name) {
      isOpen.value = true; // Usually, you want to force open, not toggle
    }
  },
);

// 4. Simplified toggle logic
const togglePanel = () => {
  if (hasRecords.value) {
    isOpen.value = !isOpen.value;
  }
};

const page = usePage();
const modules = computed(() => page.props.modules);
const getModule = (slug) => modules.value.find((m) => m.slug === slug);

// Helper methods (kept as is)
const getRelatedIcon = (slug) => getModule(slug)?.icon;
const getSingleLabel = (slug) => getModule(slug)?.single_label;
const getLabel = (slug) => getModule(slug)?.label;

const openLinkOverlay = () => {
  const parent = props.relationship?.records?.[0] || null;
  emit("open-overlay", props.panel, parent);
};

const handleUpdatePanel = () => {
  emit("update-panel-trigger", props.panel);
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
    :isOpenPanel="isOpen"
    :panel="panel"
    :fields="relatedFields"
    @update-panel="handleUpdatePanel"
  ></PanelBody>
</template>
