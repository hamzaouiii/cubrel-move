<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: {
    type: [Array, Boolean],
    required: true,
  },
  value: {
    type: [String, Number, Boolean, Object],
    default: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  color: {
    type: String,
    required: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(props.value);
  }
  return props.modelValue;
});

const toggle = () => {
  if (props.disabled) return;

  if (Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue];
    const index = newValue.indexOf(props.value);

    if (index > -1) {
      newValue.splice(index, 1);
    } else {
      newValue.push(props.value);
    }

    emit("update:modelValue", newValue);
  } else {
    emit("update:modelValue", !props.modelValue);
  }
};

const boxColor = props.color ?? "#3b82f6";
</script>

<template>
  <label
    :style="{ '--box-color': boxColor }"
    class="custom-checkbox"
    :class="{
      'custom-checkbox--checked': isChecked,
      'custom-checkbox--disabled': disabled,
    }"
  >
    <input
      type="checkbox"
      class="custom-checkbox__native"
      :checked="isChecked"
      :disabled="disabled"
      @change="toggle"
    />

    <span class="custom-checkbox__box">
      <i
        class="custom-checkbox__check fa-solid fa-check"
        :class="{ 'is-visible': isChecked }"
      ></i>
    </span>

    <slot />
  </label>
</template>

<style scoped>
.custom-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
  position: relative;
}

.custom-checkbox__native {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

/* 🔥 LOCKED BOX */
.custom-checkbox__box {
  width: 20px;
  height: 20px;
  border: 2px solid #cbd5e1;
  border-radius: 6px;
  background: white;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;
  position: relative;
}

/* 🔥 ICON NO LONGER AFFECTS LAYOUT */
.custom-checkbox__check {
  font-size: 12px;
  line-height: 1;
  color: white;

  opacity: 0;
  transition: opacity 0.15s ease;

  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.custom-checkbox__check.is-visible {
  opacity: 1;
}

.custom-checkbox--checked .custom-checkbox__box {
  background: var(--box-color);
  border-color: var(--box-color);
}

.custom-checkbox:not(.custom-checkbox--disabled):hover .custom-checkbox__box {
  border-color: var(--box-color);
}

.custom-checkbox--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
