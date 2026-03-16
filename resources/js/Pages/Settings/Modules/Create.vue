<script setup>
import { ref, computed } from "vue";
import ModuleBuilderTabs from "@/Pages/Components/Settings/ModuleBuilderTabs.vue";
import EditModule from "@/Pages/Components/Settings/Builder/EditModule.vue";
import FieldSettings from "@/Pages/Components/Settings/Builder/FieldSettings.vue";

import Layout from "@/Layouts/Layout.vue";

import { Head, usePage, useForm } from "@inertiajs/vue3";

defineOptions({
  layout: Layout,
});
const props = defineProps({
  settingModule: Object,
  categoryList: Object,
});
const childFormData = ref({});
const hasMissingFields = ref(false);
const isFormDirty = ref(false);

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

// Default tab
const currentTab = ref("edit");
const tabs = ["edit", "fields"];
const appSettings = usePage().props.appSettings;
const moduleColor = computed(() =>
  appSettings.use_individual_module_colors === 0
    ? props.settingModule.color
    : appSettings.primary_color,
);
const isProcessing = ref(false);

const proceedToNextTab = () => {
  const currentIndex = tabs.indexOf(currentTab.value);
  if (currentIndex < tabs.length - 1) {
    currentTab.value = tabs[currentIndex + 1];
  } else {
    publishModule();
  }
};
const saveModuleAndProceed = () => {
  isProcessing.value = true;
  const url = "/settings/modulebuilder/" + props.settingModule.id;

  form
    .transform(() => childFormData.value)
    .put(url, {
      onSuccess: () => {
        isProcessing.value = false;
        // Reset dirty state locally since it's now saved
        isFormDirty.value = false;
        proceedToNextTab(); // Move to next tab only after successful save
      },
      onError: () => {
        isProcessing.value = false;
      },
    });
};
const nextTab = () => {
  if (hasMissingFields.value) return; // Safety guard

  // If on the edit tab AND they made changes, save it first
  if (currentTab.value === "edit" && isFormDirty.value) {
    saveModuleAndProceed();
  } else {
    // Nothing changed OR we are on a different tab, just skip saving and move instantly
    proceedToNextTab();
  }
};

const isLastTab = computed(() => {
  return tabs.indexOf(currentTab.value) === tabs.length - 1;
});

const publishModule = () => {
  console.log("Publish module");
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

const isCurrentDirty = computed(() => {
  return tabDirty.value[currentTab.value];
});
const isAnythingDirty = computed(() =>
  Object.values(tabDirty.value).some(Boolean),
);

const saveStep = (step) => {
  if (step === "edit") {
    saveModule();
  } else return;
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
          v-if="currentTab === 'edit'"
          :setting-module="settingModule"
          :category-list="categoryList"
          @dirty="updateDirty('edit', $event)"
          @update="handleUpdate"
          @missing-fields="handleMissingFields"
          @is-form-dirty="handleIsFormDirty"
          :color="moduleColor"
        />

        <FieldSettings
          v-if="currentTab === 'fields'"
          :module="settingModule"
          @dirty="updateDirty('fields', $event)"
        />

        <div class="settings__actions">
          <button
            v-if="tabs.indexOf(currentTab) > 0"
            @click="currentTab = tabs[tabs.indexOf(currentTab) - 1]"
          >
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
</template>
