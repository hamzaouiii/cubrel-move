<script setup>
import IconPicker from "@/Pages/Components/Settings/Modules/IconPicker.vue";
import ColorPicker from "@/Pages/Components/FiledTypes/ColorPicker.vue";
import StatusBadgePreview from "@/Pages/Components/Settings/Dropdowns/StatusBadgePreview.vue";

const DEFAULT_COLOR = "#374151";
const DEFAULT_BG_COLOR = "#e5e7eb";

defineProps({
  label: String,
});

const color = defineModel("color", { default: DEFAULT_COLOR });
const bgColor = defineModel("bgColor", { default: DEFAULT_BG_COLOR });
// No default here — a status row is allowed to have no icon at all. A
// defaulted value here would show up in the live preview without ever
// being written back to the row until the user actually opened the picker,
// so the preview lied about what was really going to be saved.
const icon = defineModel("icon", { default: "" });
</script>

<template>
  <div class="status-option-fields">
    <div class="status-option-fields__preview">
      <StatusBadgePreview :label="label" :color="color" :bg-color="bgColor" :icon="icon" />
    </div>

    <div class="status-option-fields__color">
      <span>{{ $t("settings.dropdown.color") }}</span>
      <ColorPicker v-model="color" />
    </div>

    <div class="status-option-fields__color">
      <span>{{ $t("settings.dropdown.background_color") }}</span>
      <ColorPicker v-model="bgColor" />
    </div>

    <div class="status-option-fields__icon">
      <span>{{ $t("settings.dropdown.icon") }}</span>
      <IconPicker v-model="icon" :color="color" />
    </div>
  </div>
</template>
