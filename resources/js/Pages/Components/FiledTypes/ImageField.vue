<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { useImageUpload } from "@/Composables/useImageUpload";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: {
    type: [String, null],
    default: "",
  },
  mode: {
    type: String,
    default: "edit",
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  related_label: {
    type: String,
    default: null,
  },
  size: {
    type: String,
    default: "md", // "md" | "lg"
  },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const { upload, uploading, error } = useImageUpload();
const fileInput = ref(null);

const localValue = computed(() => props.modelValue || "");

// Falls back to initials from related_label (e.g. a user's name) when there's
// no image yet, instead of a generic placeholder icon.
const initials = computed(() => {
  const label = props.related_label?.trim();
  if (!label) return "";

  const cleaned = label.replace(/\d+/g, "");
  const words = cleaned.split(/\s+/).filter(Boolean);

  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }

  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
});

const triggerFileInput = () => {
  if (props.readOnly || uploading.value) return;
  fileInput.value?.click();
};

const onFileSelected = async (event) => {
  const file = event.target.files?.[0];
  event.target.value = "";
  if (!file) return;

  const url = await upload(file);
  if (url) {
    emit("update:modelValue", url);
  }
};

const removeImage = () => {
  emit("update:modelValue", "");
};
</script>

<template>
  <div v-if="mode === 'edit' || mode === 'settings'">
    <div
      class="image-field image-field--edit"
      :class="{
        'image-field--error': hasError || error,
        'image-field--readonly': readOnly,
        'image-field--lg': size === 'lg',
      }"
    >
      <div class="image-field__preview" @click="triggerFileInput">
        <img v-if="localValue" :src="localValue" alt="" />
        <span v-else-if="initials" class="image-field__initials">{{
          initials
        }}</span>
        <i v-else class="fa-solid fa-image image-field__placeholder-icon"></i>
        <div v-if="uploading" class="image-field__uploading">
          <i class="fa-solid fa-atom fa-spin"></i>
        </div>
      </div>
      <div class="image-field__actions" v-if="!readOnly">
        <button
          type="button"
          class="image-field__upload-btn"
          @click="triggerFileInput"
          :disabled="uploading"
        >
          {{ localValue ? t("fields.image.change") : t("fields.image.upload") }}
        </button>
        <button
          v-if="localValue"
          type="button"
          class="image-field__remove-btn"
          @click="removeImage"
        >
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/gif"
        class="image-field__file-input"
        @change="onFileSelected"
      />
      <span v-if="error" class="image-field__error">{{ t(error) }}</span>
    </div>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'image-field image-field--detail',
        { 'image-field--readonly': readOnly },
      ]"
    >
      <img v-if="localValue" :src="localValue" alt="" />
      <div
        v-else-if="initials"
        class="image-field__placeholder image-field__initials"
      >
        {{ initials }}
      </div>
      <div v-else class="image-field__placeholder">
        <i class="fa-solid fa-image"></i>
      </div>
    </div>
  </div>

  <div
    v-else-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
  >
    <div class="image-field image-field--table">
      <img v-if="localValue" :src="localValue" alt="" />
      <div
        v-else-if="initials"
        class="image-field__placeholder image-field__placeholder--sm image-field__initials"
      >
        {{ initials }}
      </div>
      <div v-else class="image-field__placeholder image-field__placeholder--sm">
        <i class="fa-solid fa-image"></i>
      </div>
    </div>
  </div>
</template>
