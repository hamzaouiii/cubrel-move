<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, useForm } from "@inertiajs/vue3";
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  reactive,
  getCurrentInstance,
} from "vue";
import { formatDateTime } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";
const { success, error, info, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  title: String,
  record: Object,
  recordLayout: Object,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const form = useForm({ ...props.record });
console.log(props.record);

const isEditing = ref(false);
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);

const editableRecord = reactive({ ...props.record });

const toggleActionDropDown = () => {
  showActionDropDown.value = !showActionDropDown.value;
};

const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
};

const isDirty = computed(() => form.isDirty);
const enableEditing = () => {
  isEditing.value = true;
};

const getChangedData = (original, form) => {
  const changed = {};
  const edited = form.data();
  for (const key of Object.keys(edited)) {
    if (original[key] !== edited[key]) {
      changed[key] = edited[key];
    }
  }

  return changed;
};

const saveRecord = () => {
  info(t("modules.actions.updating"));
  const payload = getChangedData(props.record, form);

  if (Object.keys(payload).length === 0) {
    isEditing.value = false;
    return;
  }

  const moduleSlug = props.module.slug ?? props.module;
  const url = `/${moduleSlug}/${props.record.id}`;
  form
    .transform(() => payload)
    .put(url, {
      onSuccess: () => {
        isEditing.value = false;
        clearAllAlerts();
        success(t("modules.actions.update_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.actions.update_error") + form.errors);
      },
    });
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    if (isEditing.value) {
      saveRecord();
    }
  }

  if (e.ctrlKey && e.key === "e") {
    e.preventDefault();
    enableEditing();
  }

  if (e.key === "Escape") {
    cancelEditing();
  }
}

const cancelEditing = () => {
  form.reset();
  isEditing.value = false;
};

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
  window.removeEventListener("keydown", handleKeydown);
});

const appSettings = usePage().props.appSettings;

const displayValueFor = (f) => {
  const val = props.record[f.key];
  if (val == null || val === "") return "";

  if (f.type === "datetime") {
    return formatDateTime(val, appSettings);
  }
  if (f.type === "textarea") {
    if (val.length > 62) {
      return val.substring(0, 64) + "...";
    }
  }
  return val;
};

const getTextareaRows = (f) => {
  if (form[f.key]) {
    const val = form[f.key].split(" ").length;
    return val / 8;
  }
  return 5;
};
</script>

<template>
  <Head>
    <title>{{ record.name }} - {{ title }} - Automatisierung Regensburg</title>
  </Head>

  <div
    class="ar-main-container"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
    <div class="ar-main-container_header">
      <div class="ar-main-container_header_details">
        <h1 class="ar-main-container_header_details_title">
          {{ record.name }}
        </h1>
      </div>
      <div class="ar-main-container_header_actions" ref="actionDropDownref">
        <div class="input-group">
          <button
            v-if="isEditing"
            type="button"
            class="record-main-btn cancel-btn"
            @click="cancelEditing"
          >
            {{ $t("modules.actions.cancel") }}
          </button>

          <button
            v-if="!isEditing"
            type="button"
            class="record-main-btn"
            @click="enableEditing"
          >
            {{ $t("modules.actions.edit") }}
          </button>

          <button
            v-else
            type="button"
            :class="['record-main-btn', { disabled: !isDirty }]"
            :disabled="!isDirty"
            @click="saveRecord"
          >
            {{ $t("modules.actions.save") }}
          </button>

          <button
            @click="toggleActionDropDown"
            type="button"
            class="record-dropdown-btn"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i
              :class="
                showActionDropDown
                  ? 'fa-solid fa-chevron-up'
                  : 'fa-solid fa-chevron-down'
              "
            ></i>
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>

          <transition name="fade">
            <ul
              v-if="showActionDropDown"
              class="dropdown-menu dropdown-menu-end show"
            >
              <li>
                <a class="dropdown-item disabled" href="#">{{
                  $t("modules.actions.share")
                }}</a>
              </li>
              <li>
                <a class="dropdown-item disabled" href="#">{{
                  $t("modules.actions.export")
                }}</a>
              </li>
              <li>
                <a class="dropdown-item" href="#">{{
                  $t("modules.actions.placeholder")
                }}</a>
              </li>
              <li><hr class="dropdown-divider" /></li>
              <li>
                <a class="dropdown-item" href="#">{{
                  $t("modules.actions.bulk_action")
                }}</a>
              </li>
              <li><hr class="dropdown-divider" /></li>
              <li>
                <a class="dropdown-item" href="#" style="color: salmon">{{
                  $t("modules.actions.delete")
                }}</a>
              </li>
            </ul>
          </transition>
        </div>
      </div>
    </div>

    <div class="ar-main-container_content">
      <div
        class="ar-main-container_content_section card-shadow"
        v-for="s in recordLayout.sections"
      >
        <div class="ar-main-container_content_section_title">
          {{ s.name }}
        </div>
        <div class="ar-main-container_content_section_layout">
          <div
            v-for="f in s.layout"
            class="ar-main-container_content_section_layout_field"
          >
            <span class="ar-main-container_content_section_layout_field_label">
              {{ $t(f.label) }}:
            </span>

            <div
              v-if="!isEditing"
              :class="['field', { 'view-uneditable-field': f.readonly }]"
              @click="!f.readonly && enableEditing()"
            >
              <span>
                {{ displayValueFor(f) }}
              </span>
            </div>
            <div
              :class="[
                'field',
                'editing-mode',
                { 'uneditable-field': f.readonly },
              ]"
              v-else
            >
              <template v-if="f.readonly">
                <span>
                  {{ displayValueFor(f) }}
                </span>
              </template>
              <template v-else-if="f.type == 'textarea'">
                <textarea
                  v-model="form[f.key]"
                  :rows="getTextareaRows(f)"
                ></textarea>
              </template>
              <template v-else>
                <input type="text" v-model="form[f.key]" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
