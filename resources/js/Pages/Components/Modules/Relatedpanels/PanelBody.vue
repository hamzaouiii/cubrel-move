<script setup>
import { usePage } from "@inertiajs/vue3";
import PanelRecord from "./PanelRecord.vue";
import PanelParentRecord from "./PanelParentRecord.vue";
import { computed, ref, getCurrentInstance, watch } from "vue";
import axios from "axios";
import { useConfirm } from "@/Composables/useConfirm";
import PanelFooter from "./PanelFooter.vue";
const { confirm } = useConfirm();
const props = defineProps({
  isOpenPanel: Boolean,
  relationship: Object,
  panel: Object,
  fields: Object,
  pagination: Object,
  color: String,
});

const records = ref([]);
const isLoading = ref(false);

watch(
  () => props.relationship?.records,
  (newVal) => {
    records.value = newVal != undefined ?? [...newVal];
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

const handleLoading = (state) => {
  isLoading.value = state;
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
const panel_limit = computed(() =>
  Number(page.props.appSettings?.related_panel_limit),
);
const visibleFields = computed(() =>
  Object.values(props.panel.fields || {}).filter((field) =>
    props.fields?.some((f) => f.name === field.name),
  ),
);
</script>

<template>
  <Transition name="expand">
    <div v-if="relationship && isOpenPanel" class="relatedpanels__item__body">
      <div class="related-table-wrapper">
        <table class="related-records" v-if="relationship.role === 'parent'">
          <thead>
            <tr>
              <th
                v-for="field in visibleFields"
                :key="field.name"
                :class="{ 'is-action': field.type === 'action' }"
              >
                {{ $t(field.label) }}
              </th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <template v-if="isLoading">
              <tr
                v-for="n in panel_limit"
                :key="'skeleton-' + n"
                class="skeleton-row"
              >
                <td v-for="field in visibleFields" :key="field.name">
                  <div class="skeleton-bar"></div>
                </td>
                <td></td>
              </tr>
            </template>

            <template v-else>
              <PanelRecord
                v-for="record in records"
                :key="record.id"
                :record="record"
                :header="visibleFields"
                :related_slug="relationship.related_slug"
                :openMenuId="openMenuId"
                @toggleMenu="toggleMenu"
                @unlink="unlink"
                :isUnlinking="unlinkingId === record.id"
                :class="{ isUnlinking: unlinkingId === record.id }"
                :fields="fields"
                :color="color"
              ></PanelRecord>
            </template>
          </tbody>
        </table>
        <div
          v-if="
            relationship.role === 'child' || relationship.role === 'sibling'
          "
        >
          <PanelParentRecord
            :record="parentRecord"
            :header="visibleFields"
            :related_slug="relationship.related_slug"
            :key="parentRecord?.id"
            :fields="fields"
            :color="color"
          >
          </PanelParentRecord>
        </div>
      </div>
      <PanelFooter
        v-if="relationship.role === 'parent'"
        :pagination="pagination"
        :related-slug="relationship.related_slug"
        :relationship-name="relationship.name"
        @loading="handleLoading"
      />
    </div>
  </Transition>
</template>
