<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, getCurrentInstance } from "vue";
import { useAlerts } from "@/Composables/useAlerts";
const { success, error, info, warning, clearAllAlerts } = useAlerts();

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

const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
};

const saveRecord = () => {
  if (!form.isDirty) {
    warning(t("modules.actions.no_data_entered"));
  } else {
    info(t("modules.actions.saving"));

    const moduleSlug = props.module.slug ?? props.module;
    const url = `/${moduleSlug}`;
    form.post(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("modules.actions.save_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.actions.save_error") + form.errors);
      },
    });
  }
};

const cancelCreate = () => {
  const moduleSlug = props.module.slug ?? props.module;
  router.visit(`/${moduleSlug}`);
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    console.log("Here");
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

const appSettings = usePage().props.appSettings;
</script>

<template>
  <Head>
    <title>{{ module.label }} - Automatisierung Regensburg</title>
  </Head>

  <div
    class="module-layout"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
    <div class="module-layout__header">
      <div class="module-layout__header__details">
        <h1 class="module-layout__header__details__title"></h1>
      </div>

      <div class="module-layout__header__actions" ref="actionDropDownref">
        <div class="module-layout__header__actions__create">
          <button
            class="module-layout__header__actions__create__cancel-btn"
            @click="cancelCreate"
          >
            {{ $t("modules.actions.cancel") || "Cancel" }}
          </button>

          <button
            class="module-layout__header__actions__create_save-btn"
            :disabled="!form.isDirty"
            @click="saveRecord"
          >
            {{ $t("modules.actions.save") || "Save" }}
          </button>
        </div>
      </div>
    </div>

    <div class="module-layout__content">
      <div
        class="module-layout__content__section"
        v-for="s in recordLayout.sections"
        :key="s.name"
      >
        <div class="module-layout__content__section__title">
          {{ s.name }}
        </div>

        <div class="module-layout__content__section__layout">
          <div
            v-for="f in s.layout.filter((f) => !f.readonly)"
            :key="f.key"
            class="module-layout__content__section__layout__field"
          >
            <span class="module-layout__content__section__layout__field__label">
              {{ $t(f.label) }}:
            </span>

            <div
              class="module-layout__content__section__layout__field__content editing-mode"
            >
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
