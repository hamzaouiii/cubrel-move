<script setup>
import {
  ref,
  computed,
  onMounted,
  watch,
  onBeforeUnmount,
  getCurrentInstance,
} from "vue";
import EditModule from "@/Pages/Components/Settings/Builder/EditModule.vue";
import FieldSettings from "@/Pages/Components/Settings/Builder/FieldSettings.vue";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import { useAlerts } from "@/Composables/useAlerts";
import Layout from "@/Layouts/Layout.vue";
import DeployProgressModal from "@/Pages/Components/Settings/Builder/DeployProgressModal.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";

const { error, success, info, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});
const props = defineProps({
  settingModule: Object,
  categoryList: Object,
  fields: Array,
  field_types: Array,
  metadata: Object,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const childFormData = ref({});
const hasMissingFields = ref(false);
const isFormDirty = ref(false);
const showModuleDeplyProgress = ref(false);
const form = useForm({});

const handleUpdate = (payload) => {
  childFormData.value = payload;
};
const handleMissingFields = (payload) => {
  hasMissingFields.value = payload;
};
const handleIsFormDirty = (payload) => {
  isFormDirty.value = payload;
};

const currentStep = ref("edit");
const tabs = ["edit", "fields"];
const appSettings = usePage().props.appSettings;
const moduleColor = computed(() =>
  appSettings.use_individual_module_colors === "1"
    ? props.settingModule.color
    : appSettings.primary_color,
);

const isProcessing = ref(false);

const proceedToNextTab = () => {
  const currentIndex = tabs.indexOf(currentStep.value);
  if (currentIndex < tabs.length - 1) {
    currentStep.value = tabs[currentIndex + 1];
  } else {
    deployModule();
  }
};

const saveModuleAndProceed = () => {
  isProcessing.value = true;
  const url = "/settings/modulebuilder/" + props.settingModule.id;
  info(t("settings.saving"));
  form
    .transform(() => childFormData.value)
    .put(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("settings.module_save_success"));
        isProcessing.value = false;
        isFormDirty.value = false;
        proceedToNextTab(); // Move to next tab only after successful save
      },
      onError: (r) => {
        clearAllAlerts();
        Object.values(r).forEach((message) => {
          error(message);
        });
        isProcessing.value = false;
      },
    });
};

const nextTab = () => {
  if (hasMissingFields.value) return;

  // If on the edit tab AND they made changes, save it first
  if (currentStep.value === "edit" && isFormDirty.value) {
    saveModuleAndProceed();
  } else {
    // Nothing changed OR we are on a different tab, just skip saving and move instantly
    proceedToNextTab();
  }
};

const back = () => {
  currentStep.value = tabs[tabs.indexOf(currentStep.value) - 1];
};

const isLastTab = computed(() => {
  return tabs.indexOf(currentStep.value) === tabs.length - 1;
});

const deployModule = () => {
  // show modal of progress and deploy
  showModuleDeplyProgress.value = true;
};
const onDeployComplete = () => {
  // In the future, this is where you'd redirect using Inertia
  // e.g., router.visit(`/settings/modules/${props.settingModule.id}`);
  console.log("Mock deployment complete!");
};
const tabDirty = ref({
  edit: false,
  layouts: false,
  fields: false,
  relationships: false,
});

const updateDirty = (tab, value) => {
  tabDirty.value[tab] = value;
};

watch(currentStep, (step) => {
  localStorage.setItem("module-create-step", step);
});

onMounted(() => {
  const savedStep = localStorage.getItem("module-create-step");
  if (savedStep && !hasMissingFields.value) {
    currentStep.value = savedStep;
  }
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});

const handleKeydown = (e) => {
  if (e.ctrlKey && e.key === "Enter") {
    e.preventDefault();
    nextTab();
  }
  if (e.ctrlKey && e.key === "b") {
    e.preventDefault();
    back();
  }
};

const handleUpdateList = () => {
  router.reload({
    only: ["fields"],
    onSuccess: () => {
      clearAllAlerts();
      success(t("settings.add_field_success"));
    },
  });
};
</script>

<template>
  <Head>
    <title>
      {{ $t("settings.create_new_module") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="[
      { '--module-color': moduleColor },
      { '--related-color': moduleColor },
    ]"
  >
    <div class="settings__module">
      <div
        class="settings__module__edit"
        :class="{ 'is-loading': isProcessing }"
      >
        <div v-if="isProcessing" class="settings__module__edit__overlay">
          <div class="saving-loader">
            <div class="lds-ripple">
              <div></div>
              <div></div>
            </div>
          </div>
        </div>
        <EditModule
          v-if="currentStep === 'edit'"
          :setting-module="settingModule"
          :category-list="categoryList"
          @dirty="updateDirty('edit', $event)"
          @update="handleUpdate"
          @missing-fields="handleMissingFields"
          @is-form-dirty="handleIsFormDirty"
          :color="moduleColor"
          :errors="form.errors"
        />

        <FieldSettings
          v-if="currentStep === 'fields'"
          :module="settingModule"
          :fields="fields"
          :field_types="field_types"
          :metadata="metadata"
          :color="moduleColor"
          @dirty="updateDirty('fields', $event)"
          @update-field-list="handleUpdateList"
        />

        <div class="settings__actions">
          <button v-if="tabs.indexOf(currentStep) > 0" @click="back()">
            {{ $t("settings.back") }}
          </button>
          <button
            class="settings__actions__save"
            type="button"
            :disabled="hasMissingFields"
            @click="nextTab"
          >
            <span v-if="!isProcessing">
              {{
                isLastTab ? $t("settings.deploy") : $t("settings.add_fields")
              }}
            </span>
            <span v-else class="button-spinner"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <DeployProgressModal
    v-if="showModuleDeplyProgress"
    @close="showModuleDeplyProgress = false"
    @complete="onDeployComplete"
  />
</template>
