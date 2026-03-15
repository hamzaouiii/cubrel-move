<script setup>
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import IconPicker from "@/Pages/Components/Settings/Modules/IconPicker.vue";
import ColorPicker from "@/Pages/Components/FiledTypes/ColorPicker.vue";

const props = defineProps({
  editableFields: Array,
  editableModule: Object,
  inputTypeFor: Function,
  submitHandler: Function,
  disableThis: {
    type: Function,
    default: () => false,
  },
  displayLabelSource: {
    type: Object,
    default: null,
  },
});
</script>

<template>
  <form @submit.prevent="submitHandler">
    <div
      v-for="[key, value] in editableFields"
      :key="key"
      class="settings__module__edit__element"
    >
      <label class="settings__module__edit__element__label">
        {{ $t("settings.modules." + key) }}
      </label>

      <div class="settings__module__edit__element__content">
        <Checkbox
          v-if="inputTypeFor(key, value) === 'checkbox'"
          v-model="editableModule[key]"
          :module-color="editableModule.color"
        />

        <IconPicker
          v-else-if="inputTypeFor(key, value) === 'icon'"
          v-model="editableModule[key]"
          :color="editableModule.color"
        />

        <input
          v-else-if="inputTypeFor(key, value) === 'display_label'"
          type="text"
          :disabled="disableThis(key)"
          v-model="displayLabelSource.label"
        />

        <textarea
          v-else-if="inputTypeFor(key, value) === 'textarea'"
          v-model="editableModule[key]"
        ></textarea>

        <ColorPicker
          v-else-if="inputTypeFor(key, value) === 'color'"
          v-model="editableModule[key]"
        />

        <input
          v-else
          :type="inputTypeFor(key, value)"
          v-model="editableModule[key]"
        />
      </div>
    </div>
  </form>
</template>
