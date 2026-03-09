<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, Object, null],
    required: true,
  },
  value: {
    type: [String, Number, Boolean, Object],
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  color: {
    type: String,
    required: false,
  },
  name: {
    // Added for accessibility & native grouping
    type: String,
    default: "custom-radio-group",
  },
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed(() => props.modelValue === props.value);

const select = () => {
  if (props.disabled) return;
  emit("update:modelValue", props.value);
};

const boxColor = props.color ?? "#2563eb"; // Using your "Create" button blue
</script>

<template>
  <label
    :style="{ '--radio-color': boxColor }"
    class="custom-radio"
    :class="{
      'is-checked': isChecked,
      'is-disabled': disabled,
    }"
  >
    <input
      type="radio"
      class="custom-radio__native"
      :name="name"
      :checked="isChecked"
      :disabled="disabled"
      @change="select"
    />

    <div class="custom-radio__visual">
      <div class="custom-radio__dot"></div>
    </div>

    <span class="custom-radio__label">
      <slot />
    </span>
  </label>
</template>

<style scoped lang="scss">
.custom-radio {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  position: relative;
  font-size: 0.95rem;
  color: #334155; // Slate-700
  transition: color 0.2s ease;

  &__native {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;

    // Focus state for accessibility
    &:focus-visible + .custom-radio__visual {
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
      border-color: var(--radio-color);
    }
  }

  &__visual {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1; // Slate-300
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }

  &__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: white;
    transform: scale(0);
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  // Hover States
  &:hover:not(.is-disabled) {
    color: #000;

    .custom-radio__visual {
      border-color: var(--radio-color);
      background-color: #f8fafc;
    }
  }

  // Checked State
  &.is-checked {
    .custom-radio__visual {
      border-color: var(--radio-color);
      background: var(--radio-color);

      // Subtle outer glow
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .custom-radio__dot {
      transform: scale(1);
    }
  }

  // Disabled State
  &.is-disabled {
    opacity: 0.5;
    cursor: not-allowed;

    .custom-radio__visual {
      background: #f1f5f9;
      border-color: #e2e8f0;
    }
  }
}
</style>
