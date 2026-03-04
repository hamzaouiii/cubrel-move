<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link, usePage } from "@inertiajs/vue3";
import PanelRecord from "./PanelRecord.vue";
import PanelParentRecord from "./PanelParentRecord.vue";
import { computed, ref, getCurrentInstance, watch } from "vue";
import axios from "axios";
import { useConfirm } from "@/Composables/useConfirm";

const { confirm } = useConfirm();
const props = defineProps({
  isOpenPanel: Boolean,
  relationship: Object,
  panel: Object,
});

const records = ref([]);

watch(
  () => props.relationship.records,
  (newVal) => {
    records.value = [...newVal];
  },
  { immediate: true },
);
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const module_slug = page.props.module.slug;
const current_record_id = page.props.recordId;

const openMenuId = ref(null);
const toggleMenu = (id) => {
  openMenuId.value = openMenuId.value === id ? null : id;
};
const parentRecord = computed(() => {
  if (
    props.relationship.role === "child" ||
    props.relationship.role === "sibling"
  ) {
    return props.relationship?.records[0] || null;
  }
  return null;
});
const unlinkingId = ref(null);

const emit = defineEmits("update-panel");
const unlink = async (record) => {
  const ok = await confirm({
    title: t("modules.actions.unlink_confirm_title"),
    message: t("modules.actions.unlink_confirm"),
    confirmText: t("modules.actions.unlink_yes"),
    cancelText: t("modules.actions.unlink_no"),
  });

  if (!ok) return;
  unlinkingId.value = record.id;
  const url = `/modules/${module_slug}/${current_record_id}/relationships/${props.relationship.name}/${record.id}`;
  try {
    await axios.delete(url);
    records.value = records.value.filter((r) => r.id !== record.id);
    emit("update-panel");
  } catch (error) {
    console.error("Unlink failed:", error);
  } finally {
    unlinkingId.value = null;
  }
};
</script>

<template>
  <Transition name="expand">
    <div v-if="relationship && isOpenPanel" class="relatedpanels__item__body">
      <div class="related-table-wrapper">
        <table class="related-records" v-if="relationship.role === 'parent'">
          <thead>
            <tr>
              <th
                v-for="field in panel.fields"
                :key="field.name"
                :class="{ 'is-action': field.type === 'action' }"
              >
                {{ $t(field.label) }}
              </th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <PanelRecord
              v-for="record in records"
              :key="record.id"
              :record="record"
              :header="panel.fields"
              :related_slug="relationship.related_slug"
              :openMenuId="openMenuId"
              @toggleMenu="toggleMenu"
              @unlink="unlink"
              :isUnlinking="unlinkingId === record.id"
              :class="{ isUnlinking: unlinkingId === record.id }"
            ></PanelRecord>
          </tbody>
        </table>
        <div
          v-if="
            relationship.role === 'child' || relationship.role === 'sibling'
          "
        >
          <PanelParentRecord
            :record="parentRecord"
            :header="panel.fields"
            :related_slug="relationship.related_slug"
            :key="parentRecord?.id"
          >
          </PanelParentRecord>
        </div>
      </div>
    </div>
  </Transition>
</template>
