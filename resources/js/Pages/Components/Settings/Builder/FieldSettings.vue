<script setup>
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import CreateFieldModal from "./CreateFieldModal.vue";
import EditFieldModal from "./EditFieldModal.vue";
import { useAlerts } from "@/Composables/useAlerts";
import EditDropdownListModal from "../Dropdowns/EditDropdownListModal.vue";

const { success, info, error, clearAllAlerts } = useAlerts();

const props = defineProps({
  module: Object,
  fields: Array,
  color: String,
  field_types: Array,
  metadata: Object,
  fieldModules: { type: Array, default: () => [] },
});

const page = usePage();
const sortKey = ref(null);
const sortDirection = ref("asc");
const showCreateFieldDialog = ref(false);
const showEditFieldDialog = ref(false);
const confirmFieldKey = ref(null);
const isConfirm = (key) => confirmFieldKey.value === key;

function sortBy(key) {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortKey.value = key;
    sortDirection.value = "asc";
  }
}
const cleanedFields = computed(() => {
  return Object.values(props.fields);
});
const sortedFields = computed(() => {
  if (!sortKey.value) return cleanedFields.value;

  return cleanedFields.value.sort((a, b) => {
    const valA = a[sortKey.value] ?? "";
    const valB = b[sortKey.value] ?? "";

    if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
    if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });
});
const emit = defineEmits(["update-field-list"]);
const deleteDraftField = async (f) => {
  if (confirmFieldKey.value !== f.key) {
    confirmFieldKey.value = f.key;
    return;
  }

  try {
    const res = await axios.delete(
      `/settings/modulebuilder/${props.module.id}/field/${f.id}`,
    );

    clearAllAlerts();
    success(res.data.message);
    emit("update-field-list");

    confirmFieldKey.value = null;
  } catch (err) {
    clearAllAlerts();
    const msg = err.response?.data?.message || "An unexpected error occurred";
    error(msg);
    lug;
    console.error(err.response?.data || err);
  }
};

const openCreateFieldDialog = () => (showCreateFieldDialog.value = true);
const closeCreateFieldDialog = () => (showCreateFieldDialog.value = false);
const openEditFieldDialog = () => (showEditFieldDialog.value = true);
const closeEditFieldDialog = () => (showEditFieldDialog.value = false);

const handleNewFieldSaved = () => {
  closeCreateFieldDialog();
  emit("update-field-list");
};

const handleEditedFieldSaved = () => {
  closeEditFieldDialog();
  emit("update-field-list");
};

const fieldToEdit = ref(null);
const setFieldToEdit = (f) => {
  fieldToEdit.value = f;
  openEditFieldDialog();
};
</script>

<template>
  <div class="settings" :style="{ '--module-color': color }">
    <div class="fields">
      <div class="list-mode">
        <div class="fields__header">
          <span class="fields__header__name">
            {{ module?.label || module?.name }}</span
          >
          <button
            class="fields__header__create"
            @click="openCreateFieldDialog()"
          >
            <i class="fa-solid fa-plus"></i>
          </button>
        </div>
        <table class="fields__table">
          <thead>
            <tr>
              <th @click="sortBy('name')">
                {{ $t("fields.name") }}

                <i
                  v-if="sortKey === 'name'"
                  class="fa-solid sort-icon is-active"
                  :class="
                    sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'
                  "
                ></i>

                <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
              </th>

              <th @click="sortBy('label')">
                {{ $t("fields.field_label") }}

                <i
                  v-if="sortKey === 'label'"
                  class="fa-solid sort-icon is-active"
                  :class="
                    sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'
                  "
                ></i>

                <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
              </th>

              <th @click="sortBy('type')">
                {{ $t("fields.type") }}

                <i
                  v-if="sortKey === 'type'"
                  class="fa-solid sort-icon is-active"
                  :class="
                    sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'
                  "
                ></i>

                <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
              </th>
              <th style="width: 70px"></th>
            </tr>
          </thead>

          <tbody>
            <tr
              class="fields__table__row fields__table__row--builder"
              v-for="f in sortedFields"
              :key="f.key"
            >
              <td>
                {{ f.name }}
              </td>
              <td>{{ $t(f.label) }}</td>
              <td>{{ $t("fields.types." + f.type) }}</td>
              <td>
                <div class="fields__table__row__actions" v-if="f.is_draft">
                  <button
                    class=""
                    @click="deleteDraftField(f)"
                    :class="[
                      'fields__table__row__actions__delete',
                      {
                        'fields__table__row__actions__delete--confirm':
                          isConfirm(f.key),
                      },
                    ]"
                  >
                    <i
                      :class="[
                        'fa-solid',
                        isConfirm(f.key) ? 'fa-check' : 'fa-trash-can',
                      ]"
                    ></i>
                  </button>
                  <button
                    class="fields__table__row__actions__edit"
                    @click="setFieldToEdit(f)"
                  >
                    <i
                      class="fields__table__row__actions__edit__icon fa-regular fa-pen-to-square"
                    ></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="list-mode">
        <CreateFieldModal
          :module="module"
          :field_types="field_types"
          :metadata="metadata"
          :field-modules="fieldModules"
          v-if="showCreateFieldDialog"
          @on-close-modal="closeCreateFieldDialog"
          @saved="handleNewFieldSaved"
        ></CreateFieldModal>
        <EditFieldModal
          :module="module"
          :field_types="field_types"
          :metadata="metadata"
          :field-modules="fieldModules"
          v-if="showEditFieldDialog"
          @on-close-modal="closeEditFieldDialog"
          @saved="handleEditedFieldSaved"
          :field="fieldToEdit"
        ></EditFieldModal>
      </div>
    </div>
  </div>
</template>
