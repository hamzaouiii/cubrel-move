<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, getCurrentInstance } from "vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  title: String,
  recordLayout: Object,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const buildInitialForm = () => {
  const data = {};

  if (props.recordLayout && props.recordLayout.sections) {
    props.recordLayout.sections.forEach((section) => {
      section.layout.forEach((field) => {
        data[field.key] = "";
      });
    });
  }

  return data;
};

const form = useForm(buildInitialForm());
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);

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

const saveRecord = () => {
  const moduleSlug = props.module.slug ?? props.module;
  const url = `/${moduleSlug}`;
  form.post(url, {
    onError: () => {
      console.error("Error creating record:", form.errors);
    },
  });
};

const cancelCreate = () => {
  const moduleSlug = props.module.slug ?? props.module;
  router.visit(`/${moduleSlug}`);
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    saveRecord();
  }

  if (e.key === "Escape") {
    e.preventDefault();
    cancelCreate();
  }
}

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
  window.removeEventListener("keydown", handleKeydown);
});

const formatDate = (value) => {
  if (!value) return "";
  return new Date(value).toISOString().slice(0, 10); // yyyy-mm-dd for <input type="date">
};

const appSettings = usePage().props.appSettings;
</script>

<template>
  <Head>
    <title>{{ module.label }} - Automatisierung Regensburg</title>
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
        <h1 class="ar-main-container_header_details_title"></h1>
      </div>

      <div class="ar-main-container_header_actions" ref="actionDropDownref">
        <div class="input-group">
          <button
            type="button"
            class="record-main-btn cancel-btn"
            @click="cancelCreate"
          >
            {{ $t("modules.actions.cancel") || "Cancel" }}
          </button>

          <button type="button" class="record-main-btn" @click="saveRecord">
            {{ $t("modules.actions.save") || "Save" }}
          </button>

          <button
            @click="toggleActionDropDown"
            type="button"
            class="record-dropdown-btn"
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
            </ul>
          </transition>
        </div>
      </div>
    </div>

    <div class="ar-main-container_content">
      <div
        class="ar-main-container_content_section card-shadow"
        v-for="s in recordLayout.sections"
        :key="s.name"
      >
        <div class="ar-main-container_content_section_title">
          {{ s.name }}
        </div>

        <div class="ar-main-container_content_section_layout">
          <div
            v-for="f in s.layout"
            :key="f.key"
            class="ar-main-container_content_section_layout_field"
          >
            <span class="ar-main-container_content_section_layout_field_label">
              {{ $t(f.label) }}:
            </span>

            <div class="field editing-mode">
              <template v-if="f.format === 'datetime'">
                <input type="date" v-model="form[f.key]" />
              </template>

              <template v-else-if="f.format === 'Textarea'">
                <textarea v-model="form[f.key]"></textarea>
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
