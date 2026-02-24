<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import PanelRecord from "./PanelRecord.vue";

const props = defineProps({
  isOpenPanel: Boolean,
  relationship: Object,
  panel: Object,
});
</script>

<template>
  <Transition name="expand">
    <div v-if="relationship && isOpenPanel" class="relatedpanels__item__body">
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
              v-for="record in relationship.records"
              :key="record.id"
              :record="record"
              :header="panel.panelHeader"
              :related_slug="relationship.related_slug"
            ></PanelRecord>
          </tbody>
        </table>
      </div>
    </div>
  </Transition>
</template>
