<script setup>
import { ref, computed, watch, getCurrentInstance } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import PanelHeader from "./PanelHeader.vue";
import PanelBody from "./PanelBody.vue";
import axios from "axios";
import { useConfirm } from "@/Composables/useConfirm";
import { useAlerts } from "@/Composables/useAlerts";
const { confirm } = useConfirm();
const { success, error, info, clearAllAlerts } = useAlerts();

const props = defineProps({
  relationship: Object,
  panel: Object,
  expandPanel: String,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;
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

const collapsePanel = () => {
  isOpen.value = false;
};
const parent = computed(() => props.relationship?.records?.[0] || null);

const page = usePage();
const modules = computed(() => page.props.modules);
const module_slug = page.props.module.slug;
const current_record_id = page.props.recordId;

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

// Helper methods (kept as is)
const getRelatedIcon = (slug) => getModule(slug)?.icon;
const getSingleLabel = (slug) => getModule(slug)?.single_label;
const getLabel = (slug) => getModule(slug)?.label;

const openLinkOverlay = () => {
  emit("open-overlay", props.panel, parent.value);
};

const handleUpdatePanel = () => {
  emit("update-panel-trigger", props.panel);
};

const handleUnlinkParent = () => {
  if (!hasRecords.value) return;
  if (
    props.relationship.role === "child" ||
    props.relationship.role === "sibling"
  ) {
    unlinkParent(parent.value);
  }
};

const unlinkParent = async (record) => {
  const ok = await confirm({
    title: t("modules.actions.unlink_confirm_title"),
    message: t("modules.actions.unlink_confirm"),
    confirmText: t("modules.actions.unlink_yes"),
    cancelText: t("modules.actions.unlink_no"),
  });

  if (!ok) return;
  info(t("modules.actions.unlink_process"));

  const url = `/modules/${module_slug}/${current_record_id}/relationships/${props.relationship.name}/${record.id}`;
  try {
    await axios.delete(url);
    router.reload({
      only: ["record"],
      onSuccess: () => {
        collapsePanel();
        clearAllAlerts();
        success(t("modules.actions.unlink_success"));
      },
    });
  } catch (err) {
    clearAllAlerts();
    error(t("modules.actions.unlink_error"));
    console.error("Unlink failed:", err);
  } finally {
  }
};
</script>
<template>
  <PanelHeader
    v-if="relationship"
    @toggle="togglePanel(panel.name)"
    @open-overlay="openLinkOverlay"
    @unlink-parent="handleUnlinkParent"
    :icon="getRelatedIcon(relationship.related_slug)"
    :dislplay-count="relationship.records?.length ?? 0"
    :total-count="relationship.count ?? 0"
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
