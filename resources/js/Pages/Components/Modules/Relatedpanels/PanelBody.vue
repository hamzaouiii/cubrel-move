<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import PanelRecord from "./PanelRecord.vue";
import PanelParentRecord from "./PanelParentRecord.vue";
import { computed } from "vue";

const props = defineProps({
  isOpenPanel: Boolean,
  relationship: Object,
  panel: Object,
});

const parentRecord = computed(() => {
  if (props.relationship.role === "child") {
    return props.relationship?.records[0] || [];
  }
  return null;
});
const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;
</script>

<template>
  <Transition name="expand">
    <div v-if="relationship && isOpenPanel" class="relatedpanels__item__body">
      <div class="related-table-wrapper">
        <table class="related-records" v-if="relationship.role === 'parent'">
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
              v-for="record in relationship.records"
              :key="record.id"
              :record="record"
              :header="panel.panelHeader"
              :related_slug="relationship.related_slug"
            ></PanelRecord>
          </tbody>
        </table>
        <div
          class="parent_records__body"
          v-if="
            relationship.role === 'child' || relationship.role === 'sibling'
          "
        >
          <PanelParentRecord
            :record="relationship.records[0]"
            :header="panel.panelHeader"
            :related_slug="relationship.related_slug"
          >
          </PanelParentRecord>
        </div>
      </div>
    </div>
  </Transition>
</template>
