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
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed(() => {
  return props.modelValue === props.value;
});

const select = () => {
  if (props.disabled) return;

  // 🔥 true radio behavior: always set value
  if (!isChecked.value) {
    emit("update:modelValue", props.value);
  }
};

const boxColor = props.color ?? "#3b82f6";
</script>

<template>
  <label
    :style="{ '--radio-color': boxColor }"
    class="custom-radio"
    :class="{
      'custom-radio--checked': isChecked,
      'custom-radio--disabled': disabled,
    }"
  >
    <input
      type="radio"
      class="custom-radio__native"
      :checked="isChecked"
      :disabled="disabled"
      @change="select"
    />

    <span class="custom-radio__circle">
      <i class="fa-solid fa-circle-dot"></i>
    </span>

    <slot />
  </label>
</template>

<style scoped>
.custom-radio {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
  position: relative;
}

.custom-radio__native {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

/* OUTER BOX */
.custom-radio__circle {
  width: 20px;
  height: 20px;
  border: 2px solid #cbd5e1;
  border-radius: 50%; /* square with slight rounding */
  box-sizing: border-box;
  background: white;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;
  transition: all 0.15s ease;
}

/* Checked State */
.custom-radio--checked .custom-radio__circle {
  background: var(--radio-color);
  border-color: var(--radio-color);
}

/* Optional check icon */
.custom-radio__circle i {
  font-size: 12px;
  color: white;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.custom-radio--checked .custom-radio__circle i {
  opacity: 1;
}

.custom-radio:not(.custom-radio--disabled):hover .custom-radio__circle {
  border-color: var(--radio-color);
}

.custom-radio--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
