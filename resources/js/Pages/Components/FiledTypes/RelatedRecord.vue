<script setup>
import { Link } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
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
  highlight: String,
  searchable: Boolean,
  related_module: {
    type: String,
    required: true,
  },
  related_label: {
    type: String,
    default: null,
  },
  icon: {
    type: String,
  },
  openInNewTab: {
    type: Boolean,
    default: false,
  },
  showRecordInfo: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "update:modelValue",
  "click",
  "navigate",
  "open-overlay",
]);

const showError = ref(false);

watch(
  () => props.hasError,
  (val) => {
    showError.value = val;
  },
  { immediate: true },
);

const clearErrors = () => {
  showError.value = false;
};

const localValue = computed({
  get: () => props.modelValue ?? "",
  set: (val) => emit("update:modelValue", val),
});

const getRecordUrl = () => {
  if (!props.modelValue) return "#";
  return `/${props.related_module}/${props.modelValue}`;
};

const handleNavigate = (event) => {
  emit("navigate", {
    module: props.related_module,
    id: props.modelValue,
    label: props.related_label,
  });
};

const handleClick = (event) => {
  emit("click", event);
};

const getLinkTarget = () => {
  return props.openInNewTab ? "_blank" : "_self";
};

const getLinkRel = () => {
  return props.openInNewTab ? "noopener noreferrer" : "";
};

const emitOpenOverlay = () => {
  clearErrors();
  emit("open-overlay");
};
</script>

<template>
  <!-- Edit Mode -->
  <div v-if="mode === 'edit'">
    <div
      class="related-field related-field--edit edit-field"
      :class="{ 'related-field--error': showError }"
    >
      <div class="related-field--edit__content">
        <i
          :class="[icon ? icon : 'fa-solid fa-user', 'related-detail-icon']"
        ></i>
        <div class="related-detail-content" v-if="modelValue">
          <div class="related-record-info">
            <span class="related-record-label">
              {{ related_label ?? modelValue }}
            </span>
          </div>
        </div>
        <div class="related-detail-content" v-else>
          <div class="related-record-info">
            <span class="related-record-label"> — </span>
          </div>
        </div>
      </div>

      <div class="related-field--edit__actions">
        <i v-if="showError" class="fa-solid fa-circle-exclamation"></i>

        <div class="related-field--edit__actions__clear">
          <span v-if="modelValue" @click="localValue = ''">
            <i class="fa-solid fa-xmark"></i>
          </span>
        </div>
        <button v-if="modelValue" @click="emitOpenOverlay()">
          <i class="fa-solid fa-pen"></i>
        </button>
        <button v-else @click="emitOpenOverlay()">
          <i class="fa-solid fa-link"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Detail Mode -->
  <div v-else-if="mode === 'detail'">
    <div
      class="related-field related-field--detail display-field"
      :class="{
        'related-field--readonly': readOnly,
        'related-field--has-value': modelValue,
      }"
      @click="handleClick"
    >
      <i
        v-if="modelValue"
        :class="[icon ? icon : 'fa-solid fa-user', 'related-detail-icon']"
      ></i>
      <div class="related-detail-content" v-if="modelValue">
        <component
          :is="openInNewTab ? 'a' : Link"
          :href="getRecordUrl()"
          :target="getLinkTarget()"
          :rel="getLinkRel()"
          class="related-detail-link"
          @click="handleNavigate"
        >
          <div class="related-record-info">
            <span class="related-record-label">
              {{ related_label ?? modelValue }}
            </span>
          </div>
        </component>

        <div v-if="showRecordInfo" class="related-record-metadata">
          <span class="record-id">ID: {{ modelValue }}</span>
        </div>
      </div>
      <div v-else class="field-empty">—</div>
    </div>
  </div>
  <div v-else-if="mode === 'table'">
    <div
      class="related-field related-field--detail"
      :class="{
        'related-field--readonly': readOnly,
        'related-field--has-value': modelValue,
      }"
      @click="handleClick"
    >
      <i
        v-if="modelValue"
        :class="[icon ? icon : 'fa-solid fa-user', 'related-detail-icon']"
      ></i>
      <div class="related-detail-content" v-if="modelValue">
        <component
          :is="openInNewTab ? 'a' : Link"
          :href="getRecordUrl()"
          :target="getLinkTarget()"
          :rel="getLinkRel()"
          class="related-detail-link"
          @click="handleNavigate"
        >
          <div class="related-record-info">
            <span class="related-record-label">
              {{ related_label ?? modelValue }}
            </span>
          </div>
        </component>
      </div>
      <div v-else class="field-empty">—</div>
    </div>
  </div>

  <!-- Related Panel Mode -->
  <div v-else-if="mode === 'related-panel'">
    <div class="related-field related-field--related-panel">
      <div class="related-panel-header" v-if="related_label">
        <i :class="[icon, 'related-panel-icon']"></i>
        <span class="related-panel-title">{{ related_label }}</span>
      </div>

      <div class="related-panel-content" v-if="modelValue">
        <component
          :is="openInNewTab ? 'a' : Link"
          :href="getRecordUrl()"
          :target="getLinkTarget()"
          :rel="getLinkRel()"
          class="related-panel-link"
          @click="handleNavigate"
        >
          <div class="related-panel-card">
            <div class="card-icon">
              <i :class="[icon]"></i>
            </div>
            <div class="card-info">
              <div class="card-module">{{ related_module }}</div>
              <div class="card-id">ID: {{ modelValue }}</div>
            </div>
            <div class="card-action">
              <i class="fa-solid fa-chevron-right"></i>
            </div>
          </div>
        </component>
      </div>
      <div v-else class="related-panel-empty">
        <i class="fa-regular fa-circle-question"></i>
        <span>No related record</span>
      </div>
    </div>
  </div>

  <!-- Linking Panel Mode -->
  <div v-else-if="mode === 'linkingPanel'">
    <div class="related-field related-field--linking-panel">
      <div class="linking-panel-content">
        <div class="linking-preview" v-if="modelValue">
          <i :class="[icon, 'linking-icon']"></i>
          <div class="linking-info">
            <div class="linking-label">{{ related_label ?? modelValue }}</div>
            <div class="linking-module">{{ related_module }}</div>
          </div>
          <button
            class="linking-remove-btn"
            @click="localValue = ''"
            title="Remove link"
          >
            <i class="fa-solid fa-times"></i>
          </button>
        </div>

        <button v-else class="linking-add-btn" @click="$emit('search')">
          <i class="fa-solid fa-plus"></i>
          <span>Link {{ related_module }} record</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Settings Mode -->
  <div v-else-if="mode === 'settings'">
    <span
      class="related-field related-field--edit related-field--settings"
      :class="{
        'related-field--error': showError,
        'related-field--readonly': readOnly,
      }"
    >
      <div class="related-input-wrapper">
        <i :class="[icon, 'related-icon']"></i>
        <input
          v-model="localValue"
          type="text"
          @input="clearErrors()"
          :disabled="readOnly"
          :placeholder="`${related_module} ID`"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>
