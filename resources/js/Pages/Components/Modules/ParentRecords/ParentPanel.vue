<script setup>
import { computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import ParentPanelHeader from "./ParentPanelHeader.vue";
import ParentPanelBody from "./ParentPanelBody.vue";

const props = defineProps({
  record: { type: Object, required: true },
});
const parentRelaionship = computed(() => {
  return Object.values(props.record.related).filter(
    (record) => record.role === "child",
  );
});

const parentRecord = computed(() => {
  return parentRelaionship?.value[0]?.records[0] || [];
});

const page = usePage();
const modules = page.props.modules;

const relatedModule = (slug) => {
  return modules.find((e) => e.slug === slug);
};

const getRelatedIcon = (slug) => relatedModule(slug)?.icon;
const getRelatedColor = (slug) => relatedModule(slug)?.color;
const getSingleLabel = (slug) => relatedModule(slug)?.single_label;
const getLabel = (slug) => relatedModule(slug)?.label;

const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;
const hasParent = computed(() => {
  const obj = parentRecord.value;
  return (
    obj &&
    typeof obj === "object" &&
    !Array.isArray(obj) &&
    Object.keys(obj).length > 0
  );
});
</script>

<template>
  <div
    v-for="rel in parentRelaionship"
    :style="{
      '--related-color': getRelatedColor(rel.related_slug),
    }"
    class="parent_records"
  >
    <ParentPanelHeader
      :icon="getRelatedIcon(rel.related_slug)"
      :label="getSingleLabel(rel.related_slug)"
      :has-parent="hasParent"
    />
    <ParentPanelBody
      :record="parentRecord"
      :url="getRelatedRecordurl(rel.related_slug, parentRecord.id)"
      :has-parent="hasParent"
    />
  </div>
</template>
