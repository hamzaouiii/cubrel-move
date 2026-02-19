<script setup>
import { ref, computed } from "vue";
import { formatDateTime, formatDate } from "@/utils/datetime";
import { usePage, Link } from "@inertiajs/vue3";
import PanelRecord from "./PanelRecord.vue";
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
</script>

<template>
  <div
    v-if="relationshipMap[panel.name]"
    @click="togglePanel(panel.name)"
    class="relatedpanels__item__header"
  >
    <div class="relatedpanels__item__header__title">
      <i :class="getRelatedIcon(relationshipMap[panel.name].related_slug)"></i>
      {{ $t(relationshipMap[panel.name].label) }}
    </div>

    <div class="relatedpanels__item__header__count">
      {{ relationshipMap[panel.name].records?.length ?? 0 }}
    </div>
  </div>
  <Transition name="expand">
    <div
      v-if="relationshipMap[panel.name] && openPanels.includes(panel.name)"
      class="relatedpanels__item__body"
    >
      <div class="related-table-wrapper">
        <table class="related-records">
          <thead>
            <tr>
              <th
                v-for="field in panel.panelHeader"
                :key="field.name"
                :class="{ 'is-action': field.type === 'action' }"
              >
                {{ $t(field.label) }}
              </th>
            </tr>
          </thead>

          <tbody>
            <PanelRecord
              v-for="record in relationshipMap[panel.name].records"
              :key="record.id"
              :record="record"
              :header="panel.panelHeader"
              :related_slug="relationshipMap[panel.name].related_slug"
            ></PanelRecord>
          </tbody>
        </table>
      </div>
    </div>
  </Transition>
</template>
