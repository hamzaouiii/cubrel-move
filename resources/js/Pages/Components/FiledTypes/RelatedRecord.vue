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
  related_icon: {
    type: String,
    default: "fa-solid fa-user",
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

const emit = defineEmits(["update:modelValue", "click", "navigate"]);

const showError = ref(false);
const isHovered = ref(false);

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

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!text) return "-";
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text
    .toString()
    .replace(regex, '<span class="search-highlight">$1</span>');
};

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
</script>

<template>
  <!-- Edit Mode -->
  <div v-if="mode === 'edit'">
    <span
      class="related-field related-field--edit"
      :class="{
        'related-field--error': showError,
        'related-field--readonly': readOnly,
        'related-field--hovered': isHovered,
      }"
      @mouseenter="isHovered = true"
      @mouseleave="isHovered = false"
    >
      <div class="related-input-wrapper">
        <i :class="[related_icon, 'related-icon']"></i>

        <div v-if="readOnly" class="related-readonly-value">
          <template v-if="modelValue">
            <component
              :is="openInNewTab ? 'a' : Link"
              :href="getRecordUrl()"
              :target="getLinkTarget()"
              :rel="getLinkRel()"
              class="related-link"
              @click="handleNavigate"
            >
              {{ related_label ?? modelValue }}
            </component>
          </template>
          <span v-else class="field-empty">—</span>
        </div>

        <div v-else class="related-input-container">
          <input
            v-model="localValue"
            @input="clearErrors()"
            :placeholder="`Enter ${related_module} ID or search...`"
            type="text"
            class="related-number-input"
          />
          <button
            v-if="modelValue"
            class="related-clear-btn"
            @click="localValue = ''"
            title="Clear value"
          >
            <i class="fa-solid fa-times"></i>
          </button>
          <button
            class="related-search-btn"
            @click="$emit('search')"
            title="Search records"
          >
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </div>
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
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
      <i :class="[related_icon, 'related-detail-icon']"></i>

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
            <span class="related-module-badge">
              {{ related_module }}
            </span>
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

      <div class="related-actions" v-if="modelValue">
        <button
          class="copy-btn"
          @click.stop.prevent="$emit('copy', modelValue)"
          title="Copy ID"
        >
          <i class="fa-regular fa-copy"></i>
        </button>
        <button
          class="open-btn"
          @click.stop.prevent="handleNavigate"
          title="Open record"
        >
          <i class="fa-solid fa-arrow-up-right-from-marker"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Table Mode -->
  <div v-else-if="mode === 'table'">
    <div class="related-field related-field--table">
      <i :class="[related_icon, 'related-table-icon']"></i>

      <span v-if="searchable && highlight" class="related-table-text">
        <span v-html="highlightMatch(related_label ?? modelValue)"></span>
      </span>

      <component
        v-else-if="modelValue"
        :is="openInNewTab ? 'a' : Link"
        :href="getRecordUrl()"
        :target="getLinkTarget()"
        :rel="getLinkRel()"
        class="related-table-link"
        @click="handleNavigate"
      >
        {{ related_label ?? modelValue }}
      </component>

      <span v-else class="field-empty">—</span>
    </div>
  </div>

  <!-- Related Panel Mode -->
  <div v-else-if="mode === 'related-panel'">
    <div class="related-field related-field--related-panel">
      <div class="related-panel-header" v-if="related_label">
        <i :class="[related_icon, 'related-panel-icon']"></i>
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
              <i :class="[related_icon]"></i>
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
          <i :class="[related_icon, 'linking-icon']"></i>
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
        <i :class="[related_icon, 'related-icon']"></i>
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

<style scoped lang="scss">
.related-field {
  display: flex;
  align-items: center;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 8px;
  position: relative;

  // ===== EDIT MODE =====
  &--edit {
    border: 1.5px solid #e5e7eb;
    background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 0 0.75rem;

    .related-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .related-icon {
        font-size: 1rem;
        transition: color 0.2s ease;
        color: #6b7280;
      }

      .related-readonly-value {
        flex: 1;
        padding: 0.55rem 0;

        .related-link {
          text-decoration: none;
          color: var(--module-color);
          font-weight: 500;
          transition: all 0.2s ease;

          &:hover {
            text-decoration: underline;
          }
        }
      }

      .related-input-container {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.5rem;

        .related-number-input {
          all: unset;
          width: 100%;
          font-size: 1rem;
          letter-spacing: 0.3px;
          padding: 0.55rem 0;
          font-family: "SF Mono", "Monaco", "Cascadia Code", monospace;

          &::placeholder {
            color: #d1d5db;
            font-family: inherit;
            font-style: italic;
          }

          &:focus {
            outline: none;
          }
        }

        .related-clear-btn,
        .related-search-btn {
          background: transparent;
          border: none;
          color: #9ca3af;
          cursor: pointer;
          padding: 0.25rem;
          border-radius: 4px;
          transition: all 0.2s ease;

          &:hover {
            background: #f3f4f6;
            color: #6b7280;
          }
        }
      }
    }

    &:focus-within {
      border-color: var(--module-color);
      background: white;

      .related-icon {
        color: var(--module-color);
      }
    }

    // Error state
    &.related-field--error {
      border-color: var(--danger-color);
      background: #fef2f2;

      &:focus-within {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
      }

      .related-icon {
        color: var(--danger-color);
      }
    }

    // Readonly state
    &.related-field--readonly {
      background: #f9fafb;
      border-color: #e5e7eb;
      cursor: default;

      .related-icon {
        color: #9ca3af;
      }

      input {
        cursor: default;
        color: #6b7280;
      }
    }

    // Hover state
    &.related-field--hovered {
      border-color: #cbd5e1;
      background: #fefefe;
    }
  }

  // ===== DETAIL MODE =====
  &--detail {
    .related-detail-icon {
      color: var(--module-color);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .related-detail-content {
      flex: 1;

      .related-detail-link {
        text-decoration: none;
        display: inline-block;

        .related-record-info {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          flex-wrap: wrap;

          .related-module-badge {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            background: rgba(59, 130, 246, 0.1);
            color: var(--module-color);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            letter-spacing: 0.5px;
          }

          .related-record-label {
            font-family: "SF Mono", "Monaco", "Cascadia Code", monospace;
            font-weight: 500;
            color: #1e293b;
            transition: color 0.2s ease;
          }
        }

        &:hover .related-record-label {
          color: var(--module-color);
          text-decoration: underline;
        }
      }

      .related-record-metadata {
        margin-top: 0.25rem;

        .record-id {
          font-size: 0.75rem;
          color: #94a3b8;
          font-family: monospace;
        }
      }
    }

    .related-actions {
      display: flex;
      gap: 0.5rem;
      flex-shrink: 0;

      .copy-btn,
      .open-btn {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.375rem 0.625rem;
        cursor: pointer;
        color: #64748b;
        font-size: 0.875rem;
        transition: all 0.2s ease;

        &:hover {
          background: var(--module-color);
          border-color: var(--module-color);
          color: white;
          transform: scale(1.05);
        }

        &:active {
          transform: scale(0.98);
        }
      }
    }

    &.related-field--has-value {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }
  }

  // ===== TABLE MODE =====
  &--table {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: transparent;
    border-radius: 6px;
    transition: all 0.15s ease;
    .related-table-icon {
      color: #9ca3af;
      font-size: 0.8rem;
      opacity: 0.7;
    }

    .related-table-link {
      color: #3498db;
      text-decoration: none;
      font-weight: 500;

      &:hover {
        text-decoration: underline;
      }
    }

    &:hover {
      .related-table-icon {
        opacity: 1;
        color: #3498db;
      }
    }
  }

  // ===== RELATED PANEL MODE =====
  &--related-panel {
    .related-panel-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #e2e8f0;

      .related-panel-icon {
        color: var(--module-color);
        font-size: 0.875rem;
      }

      .related-panel-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
      }
    }

    .related-panel-content {
      .related-panel-link {
        text-decoration: none;

        .related-panel-card {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 0.75rem;
          background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
          border: 1px solid #e2e8f0;
          border-radius: 8px;
          transition: all 0.2s ease;
          cursor: pointer;

          .card-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 6px;
            color: var(--module-color);
          }

          .card-info {
            flex: 1;

            .card-module {
              font-size: 0.75rem;
              color: #64748b;
              text-transform: uppercase;
              letter-spacing: 0.5px;
            }

            .card-id {
              font-size: 0.875rem;
              font-weight: 600;
              color: #1e293b;
              font-family: monospace;
            }
          }

          .card-action {
            color: #94a3b8;
            transition: all 0.2s ease;
          }

          &:hover {
            background: #ffffff;
            border-color: var(--module-color);
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

            .card-action {
              color: var(--module-color);
              transform: translateX(2px);
            }
          }
        }
      }
    }

    .related-panel-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      text-align: center;
      color: #94a3b8;

      i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
      }

      span {
        font-size: 0.875rem;
      }
    }
  }

  // ===== LINKING PANEL MODE =====
  &--linking-panel {
    .linking-panel-content {
      .linking-preview {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;

        .linking-icon {
          color: var(--module-color);
          font-size: 1rem;
        }

        .linking-info {
          flex: 1;

          .linking-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
          }

          .linking-module {
            font-size: 0.75rem;
            color: #64748b;
          }
        }

        .linking-remove-btn {
          background: transparent;
          border: none;
          color: #94a3b8;
          cursor: pointer;
          padding: 0.25rem;
          border-radius: 4px;
          transition: all 0.2s ease;

          &:hover {
            background: #fee2e2;
            color: #ef4444;
          }
        }
      }

      .linking-add-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.75rem;
        background: transparent;
        border: 2px dashed #cbd5e1;
        border-radius: 6px;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;

        i {
          font-size: 0.875rem;
        }

        span {
          font-size: 0.875rem;
        }

        &:hover {
          border-color: var(--module-color);
          color: var(--module-color);
          background: rgba(59, 130, 246, 0.05);
        }
      }
    }
  }

  // ===== SETTINGS MODE =====
  &--settings {
    max-width: 450px;
    padding: 0 0.75rem;

    .related-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .related-icon {
        color: #6b7280;
        font-size: 0.9rem;
      }

      input {
        all: unset;
        width: 100%;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        font-family: "SF Mono", "Monaco", monospace;

        &:disabled {
          background: transparent;
          color: #9ca3af;
          cursor: not-allowed;
        }
      }
    }

    .error-icon {
      margin-right: 0.5rem;
    }
  }

  // Error icon container
  .error-icon-container {
    display: flex;
    align-items: center;
    margin-left: 0.5rem;

    .error-icon {
      color: var(--danger-color);
      font-size: 1rem;
      animation: pulse 1s ease-in-out;
    }
  }

  .field-empty {
    color: #94a3b8;
    font-style: italic;
  }
}

// Global display-field styling
.display-field {
  .copy-btn,
  .open-btn {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.375rem 0.625rem;
    cursor: pointer;
    color: #64748b;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    flex-shrink: 0;

    &:hover {
      background: var(--module-color);
      border-color: var(--module-color);
      color: white;
      transform: scale(1.05);
    }

    &:active {
      transform: scale(0.98);
    }
  }
}

@keyframes pulse {
  0% {
    opacity: 0.6;
    transform: scale(0.95);
  }
  50% {
    opacity: 1;
    transform: scale(1.05);
  }
  100% {
    opacity: 0.6;
    transform: scale(0.95);
  }
}
</style>
